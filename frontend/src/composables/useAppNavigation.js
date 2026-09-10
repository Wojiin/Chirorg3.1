import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { navigationItems } from '@/config/navigation'

/** Filtre la navigation déclarative selon les droits de la session courante. */
export function useAppNavigation() {
  const authStore = useAuthStore()
  const links = computed(() =>
    navigationItems.filter((item) => !item.requiresAdmin || authStore.isAdmin),
  )

  return { links }
}
