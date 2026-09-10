import { ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useProgrammeStore } from '@/stores/programme'

/** Orchestre le chargement et le réordonnancement du programme affiché. */
export function useProgrammeDetailView(props) {
  const router = useRouter()
  const programmeStore = useProgrammeStore()
  const pendingSurgeryRemoval = ref(null)
  const {
    selectedProgramme: programme,
    loading,
    error,
    deletingSurgeryId,
    savingProgrammeId,
  } = storeToRefs(programmeStore)

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
