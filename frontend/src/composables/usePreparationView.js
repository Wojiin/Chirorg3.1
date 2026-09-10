import { computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { usePreparationStore } from '@/stores/preparation'

/** Orchestre la checklist, ses transitions et la navigation après validation. */
export function usePreparationView(props) {
  const router = useRouter()
  const preparationStore = usePreparationStore()
  const { preparation, loading, savingId, error, isResolved, isPartial } =
    storeToRefs(preparationStore)
  const surgery = computed(() => preparation.value?.chirurgie)
  const progress = computed(
    () => preparation.value?.progressionPreparation ?? { total: 0, coches: 0 },
  )

  watch(
    () => props.id,
    async (id) => {
      const loaded = await preparationStore.loadPreparation(id)
      if (loaded?.chirurgie.valide) {
        await router.replace({ name: 'vue-finale', params: { id } })
      } else if (loaded?.chirurgie.etatValidation === 'VALIDATION_PARTIELLE') {
        await router.replace({ name: 'validation-partielle', params: { id } })
      }
    },
    { immediate: true },
  )

  async function validate() {
    const result = await preparationStore.validateSurgery()
    if (result === 'final') {
      await router.push({
        name: 'vue-finale',
        params: { id: surgery.value.id },
      })
    } else if (result === 'partial') {
      await router.push({
        name: 'validation-partielle',
        params: { id: surgery.value.id },
      })
    }
  }

  function setMaterialState(item, state) {
    return preparationStore.setMaterialState(item, state)
  }

  function goBack() {
    router.back()
  }

  return {
    error,
    goBack,
    isPartial,
    isResolved,
    loading,
    preparation,
    progress,
    savingId,
    setMaterialState,
    surgery,
    validate,
  }
}
