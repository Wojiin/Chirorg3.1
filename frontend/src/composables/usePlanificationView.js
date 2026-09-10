import { computed, onMounted, reactive, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useProgrammeStore } from '@/stores/programme'
import { useReferenceStore } from '@/stores/references'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { getTomorrowDateValue } from '@/utils/date'

const rooms = ['Salle A', 'Salle B', 'Salle C']

/** Orchestre le formulaire de planification et ses référentiels dépendants. */
export function usePlanificationView() {
  const router = useRouter()
  const programmeStore = useProgrammeStore()
  const referenceStore = useReferenceStore()
  const { planning, error: programmeError } = storeToRefs(programmeStore)
  const { loading: referencesLoading, error: referenceError } =
    storeToRefs(referenceStore)
  const minimumDate = getTomorrowDateValue()
  const formError = ref('')
  const form = reactive({
    specialiteId: '',
    chirurgienId: '',
    dateProgrammee: minimumDate,
    salle: 'Salle A',
    chirurgieModeleIds: [''],
  })

  const specialties = computed(() =>
    referenceStore
      .getCollection('specialites')
      .map((item) => ({ value: item.id, label: item.intitule }))
      .sort((left, right) => left.label.localeCompare(right.label, 'fr')),
  )
  const surgeons = computed(() =>
    referenceStore
      .getCollection('chirurgiens')
      .filter(
        (item) => String(item.specialite?.id) === String(form.specialiteId),
      )
      .map((item) => ({
        value: item.id,
        label: `Dr ${item.prenom} ${item.nom}`,
      }))
      .sort((left, right) => left.label.localeCompare(right.label, 'fr')),
  )
  const surgeries = computed(() =>
    referenceStore
      .getCollection('chirurgie-modeles')
      .filter(
        (item) => String(item.specialite?.id) === String(form.specialiteId),
      )
      .map((item) => ({ value: item.id, label: item.intitule }))
      .sort((left, right) => left.label.localeCompare(right.label, 'fr')),
  )
  const displayedError = computed(
    () => formError.value || programmeError.value || referenceError.value,
  )

  function addSurgery() {
    form.chirurgieModeleIds.push('')
  }

  function removeSurgery(index) {
    if (form.chirurgieModeleIds.length > 1)
      form.chirurgieModeleIds.splice(index, 1)
  }

  function cancel() {
    router.back()
  }

  async function submit() {
    formError.value = ''
    const surgeryModelIds = form.chirurgieModeleIds.map(Number)

    if (
      !form.specialiteId ||
      !form.chirurgienId ||
      !form.dateProgrammee ||
      !form.salle ||
      surgeryModelIds.some((id) => !Number.isInteger(id) || id <= 0)
    ) {
      formError.value = ERROR_MESSAGES.planningRequired
      return
    }
    if (
      !surgeons.value.some(
        (item) => Number(item.value) === Number(form.chirurgienId),
      ) ||
      surgeryModelIds.some(
        (id) => !surgeries.value.some((item) => Number(item.value) === id),
      )
    ) {
      formError.value = ERROR_MESSAGES.planningSpecialiteMismatch
      return
    }
    if (form.dateProgrammee < minimumDate) {
      formError.value = ERROR_MESSAGES.planningDate
      return
    }

    const createdProgramme = await programmeStore.planProgramme({
      chirurgienId: Number(form.chirurgienId),
      chirurgieModeleIds: surgeryModelIds,
      dateProgrammee: form.dateProgrammee,
      salle: form.salle,
    })

    if (createdProgramme) await router.push({ name: 'programme' })
  }

  watch(
    () => form.specialiteId,
    () => {
      form.chirurgienId = ''
      form.chirurgieModeleIds = ['']
    },
  )

  onMounted(() => {
    referenceStore
      .load(['specialites', 'chirurgiens', 'chirurgie-modeles'])
      .catch(() => {})
  })

  return {
    addSurgery,
    cancel,
    displayedError,
    form,
    minimumDate,
    planning,
    referencesLoading,
    removeSurgery,
    rooms,
    specialties,
    submit,
    surgeons,
    surgeries,
  }
}
