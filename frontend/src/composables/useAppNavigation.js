import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { navigationItems } from '@/config/navigation'

// Regroupe l'état réactif et les actions de la page consacrée à la navigation de l'application. Dépendances utilisées : authStore, computed, navigationItems, useAuthStore.
/** Filtre la navigation déclarative selon les droits de la session courante. */
export function useAppNavigation() {
  const authStore = useAuthStore()
  const links = computed(() =>
    navigationItems.filter((item) => !item.requiresAdmin || authStore.isAdmin),
  )

  return { links }
}
