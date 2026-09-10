import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { getAdminResource } from '@/config/adminResources'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import {
  filterAdminItems,
  getAdminListFilterConfig,
  getAdminListFilterParams,
  getAdminListFilterReferences,
  getSpecialityFilterOptions,
  getSurgeonFilterOptions,
} from '@/domain/adminFilters'
import { getAdminItemDetails, getAdminItemTitle } from '@/presenters/admin'
import { groupTechnicalSheets } from '@/utils/technicalSheets'
import { useAdminStore } from '@/stores/admin'
import { useReferenceStore } from '@/stores/references'
import { notifySuccess } from '@/services/notifications'

/** Orchestre le chargement, les filtres et les suppressions d'une liste administrative. */
export function useAdminListView(props) {
  const adminStore = useAdminStore()
  const referenceStore = useReferenceStore()
  const { items, loading, deletingId, error } = storeToRefs(adminStore)
  const {
    collections: referenceCollections,
    loading: referencesLoading,
    error: referencesError,
  } = storeToRefs(referenceStore)
  const search = ref('')
  const specialityFilter = ref('')
  const surgeonFilter = ref('')
  const pendingRemoval = ref(null)
  const page = ref(1)
  const itemsPerPage = 10

  const resource = computed(() => getAdminResource(props.resourceSlug))
  const isTechnicalSheetList = computed(
    () => props.resourceSlug === 'fiches-techniques',
  )
  const filterConfig = computed(() =>
    getAdminListFilterConfig(props.resourceSlug),
  )
  const hasSpecialityFilter = computed(() =>
    Boolean(filterConfig.value.speciality),
  )
  const hasSurgeonFilter = computed(() => Boolean(filterConfig.value.surgeon))
  const hasAdminFilters = computed(
    () => hasSpecialityFilter.value || hasSurgeonFilter.value,
  )
  const displayedError = computed(() =>
    resource.value
      ? error.value || (hasAdminFilters.value ? referencesError.value : '')
      : ERROR_MESSAGES.unknownAdminResource,
  )
  const pageLoading = computed(
    () => loading.value || (hasAdminFilters.value && referencesLoading.value),
  )
  const filteredItems = computed(() => {
    const needle = search.value.trim().toLocaleLowerCase('fr')
    const searchedItems = !needle
      ? items.value
      : items.value.filter((item) =>
          JSON.stringify(item).toLocaleLowerCase('fr').includes(needle),
        )
    return filterAdminItems(searchedItems, {
      specialityId: specialityFilter.value,
      surgeonId: surgeonFilter.value,
    })
  })
  const specialityOptions = computed(() =>
    getSpecialityFilterOptions(referenceCollections.value.specialites ?? []),
  )
  const surgeonOptions = computed(() =>
    getSurgeonFilterOptions(referenceCollections.value.chirurgiens ?? []),
  )
  const technicalSheetGroups = computed(() =>
    groupTechnicalSheets(filteredItems.value),
  )
  const paginatedItems = computed(() => {
    const start = (page.value - 1) * itemsPerPage
    return filteredItems.value.slice(start, start + itemsPerPage)
  })
  const paginatedTechnicalSheetGroups = computed(() => {
    const start = (page.value - 1) * itemsPerPage
    return technicalSheetGroups.value.slice(start, start + itemsPerPage)
  })
  const paginationTotal = computed(() =>
    isTechnicalSheetList.value
      ? technicalSheetGroups.value.length
      : filteredItems.value.length,
  )
  const hasDisplayedItems = computed(() =>
    isTechnicalSheetList.value
      ? technicalSheetGroups.value.length > 0
      : filteredItems.value.length > 0,
  )

  function requestRemoval(item) {
    pendingRemoval.value = item
  }

  function cancelRemoval() {
    pendingRemoval.value = null
  }

  async function confirmRemoval() {
    if (!pendingRemoval.value) return
    const removed = await adminStore.removeItem(
      props.resourceSlug,
      pendingRemoval.value.id,
    )
    if (removed !== false) {
      notifySuccess('Élément supprimé', getAdminItemTitle(pendingRemoval.value))
      cancelRemoval()
    }
  }

  watch(
    () => props.resourceSlug,
    (resourceSlug) => {
      search.value = ''
      specialityFilter.value = ''
      surgeonFilter.value = ''
      page.value = 1
      if (!resource.value) return

      adminStore.loadItems(resourceSlug)
      const filterReferences = getAdminListFilterReferences(resourceSlug)
      if (filterReferences.length) {
        referenceStore.load(filterReferences, { force: true }).catch(() => {})
      }
    },
    { immediate: true },
  )

  watch([specialityFilter, surgeonFilter], ([specialityId, surgeonId]) => {
    page.value = 1
    if (!filterConfig.value.serverSide) return
    adminStore.loadItems(
      props.resourceSlug,
      getAdminListFilterParams(props.resourceSlug, {
        specialityId,
        surgeonId,
      }),
    )
  })

  watch(search, () => {
    page.value = 1
  })

  watch(paginationTotal, (total) => {
    const lastPage = Math.max(1, Math.ceil(total / itemsPerPage))
    if (page.value > lastPage) page.value = lastPage
  })

  return {
    deletingId,
    displayedError,
    filteredItems,
    getAdminItemDetails,
    getAdminItemTitle,
    hasDisplayedItems,
    hasSpecialityFilter,
    hasSurgeonFilter,
    isTechnicalSheetList,
    pageLoading,
    page,
    itemsPerPage,
    paginatedItems,
    paginatedTechnicalSheetGroups,
    paginationTotal,
    pendingRemoval,
    requestRemoval,
    cancelRemoval,
    confirmRemoval,
    resource,
    search,
    specialityFilter,
    specialityOptions,
    surgeonFilter,
    surgeonOptions,
    technicalSheetGroups,
  }
}
