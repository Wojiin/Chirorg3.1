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
    totalItems,
    page,
    itemsPerPage,
    loading,
    error,
  } = storeToRefs(programmeStore)
  const filters = reactive({ ...storedFilters.value })

  function loadProgrammes() {
    return programmeStore.fetchProgrammes({ ...filters }, page.value)
  }

  function clearFilters() {
    filters.date = ''
    filters.room = ''
  }

  onMounted(loadProgrammes)
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
