/**
 * Crée le traitement de fin de session partagé par les erreurs 401. La promesse
 * mémorisée évite plusieurs redirections lorsque plusieurs requêtes échouent ensemble.
 */
export function createSessionExpiredHandler({ router, clearSession }) {
  let redirectPromise = null

  return function onSessionExpired() {
    clearSession()

    const currentRoute = router.currentRoute.value
    if (currentRoute.name === 'login' || redirectPromise) {
      return redirectPromise
    }

    const query = currentRoute.fullPath
      ? { redirect: currentRoute.fullPath }
      : undefined

    redirectPromise = Promise.resolve(router.replace({ name: 'login', query }))
      .catch(() => undefined)
      .finally(() => {
        redirectPromise = null
      })

    return redirectPromise
  }
}
