import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useProgrammeStore } from '@/stores/programme'
import { useReferenceStore } from '@/stores/references'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { notifySuccess } from '@/services/notifications'

/** Orchestre le chargement et le réordonnancement du programme affiché. */
export function useProgrammeDetailView(props) {
  const router = useRouter()
  const programmeStore = useProgrammeStore()
  const referenceStore = useReferenceStore()
  const pendingSurgeryRemoval = ref(null)
  const addSurgeryFormOpen = ref(false)
  const chirurgieModeleId = ref('')
  const addSurgeryError = ref('')
  const {
    selectedProgramme: programme,
    loading,
    error,
    deletingSurgeryId,
    savingProgrammeId,
    planning: addingSurgery,
  } = storeToRefs(programmeStore)
  const { loading: referencesLoading, error: referencesError } =
    storeToRefs(referenceStore)

  const surgeon = computed(() =>
    referenceStore
      .getCollection('chirurgiens')
      .find((item) => Number(item.id) === Number(props.chirurgienId)),
  )
  const speciality = computed(() => surgeon.value?.specialite ?? null)
  const surgeryModels = computed(() =>
    referenceStore
      .getCollection('chirurgie-modeles')
      .filter(
        (item) =>
          speciality.value?.id != null &&
          Number(item.specialite?.id) === Number(speciality.value.id),
      )
      .map((item) => ({ value: item.id, label: item.intitule }))
      .sort((left, right) => left.label.localeCompare(right.label, 'fr')),
  )
  const displayedAddSurgeryError = computed(
    () => addSurgeryError.value || referencesError.value,
  )

  watch(
    () => [props.date, props.salle, props.chirurgienId],
    ([date, salle, chirurgien]) =>
      programmeStore.loadProgramme({ date, salle, chirurgien }),
    { immediate: true },
  )

  function reorder(chirurgieIds) {
    if (!programme.value) return false
    return programmeStore.reorderProgramme(programme.value, chirurgieIds)
  }

  async function openAddSurgeryForm() {
    addSurgeryFormOpen.value = true
    addSurgeryError.value = ''
    try {
      await referenceStore.load(['chirurgiens', 'chirurgie-modeles'])
    } catch {
      // Le store de référentiels expose déjà le message destiné à l'interface.
    }
  }

  function cancelAddSurgery() {
    addSurgeryFormOpen.value = false
    chirurgieModeleId.value = ''
    addSurgeryError.value = ''
  }

  async function submitSurgery() {
    addSurgeryError.value = ''
    const modelId = Number(chirurgieModeleId.value)
    if (!Number.isInteger(modelId) || modelId <= 0) {
      addSurgeryError.value = ERROR_MESSAGES.surgeryModelRequired
      return false
    }
    if (!surgeryModels.value.some((item) => Number(item.value) === modelId)) {
      addSurgeryError.value = ERROR_MESSAGES.planningSpecialiteMismatch
      return false
    }
    if (!programme.value) return false

    const updated = await programmeStore.addSurgeryToProgramme(
      programme.value,
      modelId,
    )
    if (!updated) return false

    const selectedModel = surgeryModels.value.find(
      (item) => Number(item.value) === modelId,
    )
    notifySuccess(
      'Chirurgie ajoutée',
      selectedModel?.label ?? 'Le programme a été actualisé.',
    )
    cancelAddSurgery()
    return true
  }

  function requestSurgeryRemoval(chirurgie) {
    if (!chirurgie.valide) pendingSurgeryRemoval.value = chirurgie
  }

  function cancelSurgeryRemoval() {
    pendingSurgeryRemoval.value = null
  }

  async function confirmSurgeryRemoval() {
    const chirurgie = pendingSurgeryRemoval.value
    if (!programme.value || !chirurgie) return false

    const removed = await programmeStore.deleteSurgery(
      programme.value,
      chirurgie.id,
    )
    if (removed) cancelSurgeryRemoval()
    if (removed && programme.value.chirurgies.length === 0) {
      await router.push({ name: 'programme' })
    }
    return removed
  }

  return {
    deletingSurgeryId,
    addSurgeryFormOpen,
    addingSurgery,
    chirurgieModeleId,
    displayedAddSurgeryError,
    referencesLoading,
    speciality,
    surgeryModels,
    openAddSurgeryForm,
    cancelAddSurgery,
    submitSurgery,
    pendingSurgeryRemoval,
    requestSurgeryRemoval,
    cancelSurgeryRemoval,
    confirmSurgeryRemoval,
    error,
    loading,
    programme,
    reorder,
    savingProgrammeId,
  }
}
