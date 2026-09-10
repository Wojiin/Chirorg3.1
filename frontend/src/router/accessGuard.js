import { useAuthStore } from '@/stores/auth'

/**
 * Installe uniquement les décisions d'accès liées à la navigation.
 * La restauration de session est terminée par Pinia avant l'installation du routeur.
 */
export function installAccessGuard(router) {
  router.beforeEach((to) => {
    const authStore = useAuthStore()

    if (to.meta.guestOnly && authStore.isAuthenticated) {
      return { name: 'programme' }
    }
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } }
    }
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
      return { name: 'programme' }
    }

    return true
  })
}
