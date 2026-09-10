import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { getAdminResource } from '@/config/adminResources'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import {
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

/** Orchestre le chargement, les filtres serveur et les suppressions d'une liste administrative. */
export function useAdminListView(props) {
  const adminStore = useAdminStore()
  const referenceStore = useReferenceStore()
  const { items, totalItems, page, itemsPerPage, loading, deletingId, error } =
    storeToRefs(adminStore)
  const {
    collections: referenceCollections,
    loading: referencesLoading,
    error: referencesError,
  } = storeToRefs(referenceStore)
  const search = ref('')
  const specialityFilter = ref('')
  const surgeonFilter = ref('')
  const pendingRemoval = ref(null)

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
  const filteredItems = computed(() => items.value)
  const specialityOptions = computed(() =>
    getSpecialityFilterOptions(referenceCollections.value.specialites ?? []),
  )
  const surgeonOptions = computed(() =>
    getSurgeonFilterOptions(referenceCollections.value.chirurgiens ?? []),
  )
  const technicalSheetGroups = computed(() => groupTechnicalSheets(items.value))
  const paginatedItems = computed(() => items.value)
  const paginatedTechnicalSheetGroups = computed(
    () => technicalSheetGroups.value,
  )
  const paginationTotal = computed(() => totalItems.value)
  const hasDisplayedItems = computed(() => items.value.length > 0)

  function loadPage() {
    if (!resource.value) return Promise.resolve([])

    return adminStore.loadItems(props.resourceSlug, {
      page: page.value,
      itemsPerPage: itemsPerPage.value,
      q: search.value.trim() || undefined,
      ...getAdminListFilterParams(props.resourceSlug, {
        specialityId: specialityFilter.value,
        surgeonId: surgeonFilter.value,
      }),
    })
  }

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
      if (!items.value.length && page.value > 1) page.value -= 1
      else await loadPage()
    }
  }

  watch(
    () => props.resourceSlug,
    (resourceSlug) => {
      const loadWithoutPageChange = page.value === 1
      search.value = ''
      specialityFilter.value = ''
      surgeonFilter.value = ''
      page.value = 1
      if (!resource.value) return

      const filterReferences = getAdminListFilterReferences(resourceSlug)
      if (filterReferences.length) {
        referenceStore.load(filterReferences, { force: true }).catch(() => {})
      }
      if (loadWithoutPageChange) loadPage()
    },
    { immediate: true },
  )

  watch([search, specialityFilter, surgeonFilter], () => {
    if (page.value !== 1) page.value = 1
    else loadPage()
  })

  watch(page, loadPage)

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
