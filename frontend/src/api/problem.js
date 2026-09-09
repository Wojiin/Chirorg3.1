export function apiErrorMessage(
  error,
  fallback = 'Une erreur inattendue est survenue.',
) {
  const data = error?.response?.data

  if (Array.isArray(data?.violations) && data.violations.length > 0) {
    return data.violations
      .map(({ propertyPath, message }) =>
        propertyPath ? `${propertyPath} : ${message}` : message,
      )
      .join(' · ')
  }

  return data?.detail || data?.description || error?.message || fallback
}
