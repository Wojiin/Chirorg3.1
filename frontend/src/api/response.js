/** Extrait le message métier le plus utile des différents formats d'erreur API Platform. */
export function getApiErrorMessage(
  error,
  fallback = 'Une erreur est survenue.',
) {
  return (
    error?.response?.data?.detail ||
    error?.response?.data?.message ||
    error?.response?.data?.['hydra:description'] ||
    fallback
  )
}

/** Normalise les collections JSON-LD et les tableaux JSON simples. */
export function unwrapCollection(data) {
  return data?.member ?? data?.['hydra:member'] ?? data ?? []
}
