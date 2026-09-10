/** Configuration commune des URLs de l'API, indépendante du client HTTP utilisé. */
export const apiBaseUrl = (
  import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api'
).replace(/\/+$/, '')

/** Convertit un chemin de média renvoyé par l'API en URL affichable par la SPA. */
export function resolveApiAssetUrl(path) {
  if (!path || typeof path !== 'string') return ''
  if (/^https?:\/\//i.test(path)) return path

  const browserOrigin =
    typeof window === 'undefined' ? 'http://localhost' : window.location.origin
  const apiOrigin = new URL(apiBaseUrl, browserOrigin).origin
  return new URL(path, apiOrigin).href
}
