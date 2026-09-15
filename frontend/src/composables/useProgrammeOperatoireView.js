import { computed, onMounted, reactive, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useProgrammeStore } from '@/stores/programme'
import { useReferenceStore } from '@/stores/references'

/** Orchestre les filtres et le chargement de la liste des programmes. */
export function useProgrammeOperatoireView() {
  const programmeStore = useProgrammeStore()
  const referenceStore = useReferenceStore()
  const {
    filters: storedFilters,
    filteredProgrammes,
    totalItems,
    page,
    itemsPerPage,
    loading,
    error: programmeError,
  } = storeToRefs(programmeStore)
  const { error: referencesError } = storeToRefs(referenceStore)
  const filters = reactive({ ...storedFilters.value })
  const rooms = computed(() =>
    referenceStore
      .getCollection('salles')
      .map((salle) => salle.intitule)
      .sort((left, right) =>
        left.localeCompare(right, 'fr', { sensitivity: 'base' }),
      ),
  )
  const error = computed(() => programmeError.value || referencesError.value)

  function loadProgrammes() {
    return programmeStore.fetchProgrammes({ ...filters }, page.value)
  }

  function clearFilters() {
    filters.date = ''
    filters.room = ''
  }

  onMounted(() => {
    loadProgrammes()
    referenceStore.load(['salles']).catch(() => {})
  })
  watch(filters, () => {
    if (page.value !== 1) page.value = 1
    else loadProgrammes()
  })
  watch(page, loadProgrammes)

  return {
    clearFilters,
    error,
    filteredProgrammes,
    filters,
    loading,
    loadProgrammes,
    page,
    itemsPerPage,
    totalItems,
    rooms,
  }
}
