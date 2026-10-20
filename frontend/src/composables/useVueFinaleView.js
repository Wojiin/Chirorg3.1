import { watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { usePreparationStore } from '@/stores/preparation'
import { getProgrammeDetailRoute } from '@/config/navigation'

// Regroupe l'état réactif et les actions de la page consacrée à la chirurgie planifiée. Dépendances utilisées : getProgrammeDetailRoute, preparationStore, router, storeToRefs, usePreparationStore, useRouter, watch.
/** Orchestre le chargement de la synthèse validée en lecture seule. */
export function useVueFinaleView(props) {
  const router = useRouter()
  const preparationStore = usePreparationStore()
  const { finalView: view, loading, error } = storeToRefs(preparationStore)

  watch(
    () => props.id,
    (id) => preparationStore.loadFinalView(id),
    { immediate: true },
  )

  // Revient à la page précédente depuis la vue consacrée à la chirurgie planifiée. Dépendances utilisées : getProgrammeDetailRoute, router.
  function goBack() {
    if (view.value?.chirurgie)
      return router.push(getProgrammeDetailRoute(view.value.chirurgie))
    return router.push({ name: 'programme' })
  }

  return { error, goBack, loading, view }
}
