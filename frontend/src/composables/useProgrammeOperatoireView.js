import { onMounted, reactive, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useProgrammeStore } from '@/stores/programme'

/** Orchestre les filtres et le chargement de la liste des programmes. */
export function useProgrammeOperatoireView() {
  const programmeStore = useProgrammeStore()
  const {
    filters: storedFilters,
    rooms,
    filteredProgrammes,
    loading,
    error,
  } = storeToRefs(programmeStore)
  const filters = reactive({ ...storedFilters.value })

  function loadProgrammes() {
    return programmeStore.fetchProgrammes({ ...filters })
  }

  function clearFilters() {
    filters.date = ''
    filters.room = ''
  }

  onMounted(loadProgrammes)
  watch(filters, loadProgrammes)

  return {
    clearFilters,
    error,
    filteredProgrammes,
    filters,
    loading,
    loadProgrammes,
    rooms,
  }
}
