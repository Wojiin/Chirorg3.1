import {
  ERROR_MESSAGES,
  TECHNICAL_API_MESSAGES,
  TECHNICAL_API_MESSAGE_PREFIXES,
} from '@/config/errorMessages'

const statusMessages = {
  400: ERROR_MESSAGES.invalidRequest,
  403: ERROR_MESSAGES.accessDenied,
  404: ERROR_MESSAGES.resourceNotFound,
  405: ERROR_MESSAGES.methodNotAllowed,
  500: ERROR_MESSAGES.serviceUnavailable,
  502: ERROR_MESSAGES.serviceUnavailable,
  503: ERROR_MESSAGES.serviceUnavailable,
  504: ERROR_MESSAGES.serviceUnavailable,
}

function isTechnicalApiMessage(message) {
  if (typeof message !== 'string') return false

  return (
    TECHNICAL_API_MESSAGES.includes(message) ||
    TECHNICAL_API_MESSAGE_PREFIXES.some((prefix) => message.startsWith(prefix))
  )
}

function getValidationMessage(data) {
  if (!Array.isArray(data?.violations)) return ''

  const messages = data.violations
    .map((violation) => violation?.message?.trim())
    .filter(Boolean)

  return [...new Set(messages)].join(' ')
}

/** Extrait le message métier le plus utile des différents formats d'erreur API Platform. */
export function getApiErrorMessage(error, fallback = ERROR_MESSAGES.generic) {
  const validationMessage = getValidationMessage(error?.response?.data)
  if (validationMessage) return validationMessage

  const message =
    error?.response?.data?.detail ||
    error?.response?.data?.message ||
    error?.response?.data?.['hydra:description']

  if (typeof message === 'string' && message && !isTechnicalApiMessage(message))
    return message
  if (error?.response?.status === 401) {
    return fallback === ERROR_MESSAGES.generic
      ? ERROR_MESSAGES.sessionExpired
      : fallback
  }

  return statusMessages[error?.response?.status] ?? fallback
}

/** Normalise les collections JSON-LD et les tableaux JSON simples. */
export function unwrapCollection(data) {
  return data?.member ?? data?.['hydra:member'] ?? data ?? []
}

/** Conserve les métadonnées de pagination API Platform avec les éléments normalisés. */
export function unwrapPaginatedCollection(data) {
  const items = unwrapCollection(data)

  return {
    items,
    totalItems: Number(
      data?.totalItems ?? data?.['hydra:totalItems'] ?? items.length,
    ),
  }
}
