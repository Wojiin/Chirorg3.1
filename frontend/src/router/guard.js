export function createNavigationGuard(authStore) {
  return async (to) => {
    await authStore.initialize()

    if (to.meta.guestOnly && authStore.isAuthenticated) {
      return { name: 'accueil' }
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: 'connexion', query: { redirect: to.fullPath } }
    }

    const requiredRoles = to.meta.roles || []
    if (
      requiredRoles.length > 0 &&
      !requiredRoles.some((role) => authStore.roles.includes(role))
    ) {
      return { name: 'acces-refuse' }
    }

    return true
  }
}
