import { computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { usePreparationStore } from '@/stores/preparation'

// Regroupe l'état réactif et les actions de la page consacrée à la préparation du matériel. Dépendances utilisées : computed, preparationStore, router, storeToRefs, usePreparationStore, useRouter, watch.
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

  // Valide la préparation du matériel selon les règles métier. Dépendances utilisées : preparationStore, router.
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

  // Enregistre l'état coché ou absent du matériel préparé. Dépendances utilisées : preparationStore.
  function setMaterialState(item, state) {
    return preparationStore.setMaterialState(item, state)
  }

  // Revient à la page précédente depuis la vue consacrée à la préparation du matériel. Dépendances utilisées : router.
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
