/**
 * Décode uniquement les données publiques du JWT déjà reçu du serveur.
 * La sécurité reste assurée par le backend, qui vérifie la signature à chaque appel.
 */
export function getUserFromAccessToken(token) {
  if (typeof token !== 'string') return null

  try {
    const encodedPayload = token.split('.')[1]
    if (!encodedPayload) return null

    const normalized = encodedPayload.replace(/-/g, '+').replace(/_/g, '/')
    const padded = normalized.padEnd(Math.ceil(normalized.length / 4) * 4, '=')
    const payload = JSON.parse(atob(padded))
    const email = payload.username ?? payload.email

    if (typeof email !== 'string' || !Array.isArray(payload.roles)) return null

    return { email, roles: payload.roles }
  } catch {
    return null
  }
}
