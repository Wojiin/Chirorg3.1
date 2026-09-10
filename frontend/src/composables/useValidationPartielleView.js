import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { usePreparationStore } from '@/stores/preparation'
import { technicalSheetApi } from '@/services/technicalSheetApi'
import { normalizeTechnicalSheets } from '@/mappers/preparation'
import { getApiErrorMessage } from '@/api/response'
import { getProgrammeDetailRoute } from '@/config/navigation'

/** Orchestre la régularisation des matériels absents et la clôture automatique. */
export function useValidationPartielleView(props) {
  let viewLoadId = 0
  const router = useRouter()
  const preparationStore = usePreparationStore()
  const { preparation, loading, savingId, error, isComplete } =
    storeToRefs(preparationStore)
  const surgery = computed(() => preparation.value?.chirurgie)
  const absentMaterials = computed(
    () => preparation.value?.preparations.filter((item) => item.absent) ?? [],
  )
  const technicalSheets = ref([])
  const technicalSheetsLoading = ref(false)
  const technicalSheetsError = ref('')

  watch(
    () => props.id,
    async (id) => {
      const requestId = ++viewLoadId
      technicalSheets.value = []
      technicalSheetsError.value = ''
      technicalSheetsLoading.value = false
      const loaded = await preparationStore.loadPreparation(id)
      if (!loaded || requestId !== viewLoadId) return
      if (loaded.chirurgie.valide) {
        await router.replace({ name: 'vue-finale', params: { id } })
      } else if (loaded.chirurgie.etatValidation !== 'VALIDATION_PARTIELLE') {
        await router.replace({ name: 'preparation', params: { id } })
      } else {
        technicalSheetsLoading.value = true
        try {
          const sheets = await technicalSheetApi.listForSurgeryModel(
            loaded.chirurgie.chirurgieModele.id,
          )
          if (requestId === viewLoadId) {
            technicalSheets.value = normalizeTechnicalSheets(sheets)
          }
        } catch (error) {
          if (requestId === viewLoadId) {
            technicalSheetsError.value = getApiErrorMessage(
              error,
              'Impossible de charger les fiches techniques.',
            )
          }
        } finally {
          if (requestId === viewLoadId) technicalSheetsLoading.value = false
        }
      }
    },
    { immediate: true },
  )

  async function markReady(item) {
    if (!(await preparationStore.setMaterialState(item, 'ready'))) return
    if (!isComplete.value) return

    const result = await preparationStore.validateSurgery()
    if (result === 'final') {
      await router.push({ name: 'vue-finale', params: { id: props.id } })
    }
  }

  function goBack() {
    if (surgery.value)
      return router.push(getProgrammeDetailRoute(surgery.value))
    return router.push({ name: 'programme' })
  }

  return {
    absentMaterials,
    error,
    goBack,
    loading,
    markReady,
    preparation,
    savingId,
    surgery,
    technicalSheets,
    technicalSheetsError,
    technicalSheetsLoading,
  }
}
