import axios from 'axios'
import { apiBaseUrl } from '@/api/config'
import { ERROR_MESSAGES } from '@/config/errorMessages'

/**
 * Infrastructure HTTP commune : configure Axios, propage le JWT en mémoire et
 * coordonne un unique renouvellement de session après une réponse 401.
 */
export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true,
  headers: { Accept: 'application/ld+json, application/json' },
})

const refreshClient = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true,
  headers: { Accept: 'application/json' },
})

const authSession = {
  getAccessToken: () => null,
  setAccessToken: () => {},
  onSessionExpired: () => {},
}

/** Branche le client HTTP sur le store d'authentification sans créer de dépendance circulaire. */
export function configureApiAuth({
  getAccessToken,
  setAccessToken,
  onSessionExpired,
}) {
  authSession.getAccessToken =
    typeof getAccessToken === 'function'
      ? getAccessToken
      : authSession.getAccessToken
  authSession.setAccessToken =
    typeof setAccessToken === 'function'
      ? setAccessToken
      : authSession.setAccessToken
  authSession.onSessionExpired =
    typeof onSessionExpired === 'function'
      ? onSessionExpired
      : authSession.onSessionExpired
}

/** Ajoute le Bearer token en supportant les deux formes d'en-têtes Axios. */
function setAuthorizationHeader(config, token) {
  if (!token) return

  if (typeof config.headers?.set === 'function') {
    config.headers.set('Authorization', `Bearer ${token}`)
    return
  }

  config.headers = {
    ...config.headers,
    Authorization: `Bearer ${token}`,
  }
}

apiClient.interceptors.request.use((config) => {
  if (!isAuthenticationRequest(config.url)) {
    setAuthorizationHeader(config, authSession.getAccessToken())
  }
  return config
})

let refreshPromise = null

/** Partage une même requête de refresh entre toutes les requêtes simultanément expirées. */
export function refreshAccessToken() {
  refreshPromise ??= refreshClient
    .post('/auth/refresh')
    .then(({ data }) => {
      if (!data?.token) {
        throw new Error(ERROR_MESSAGES.refreshTokenMissing)
      }

      authSession.setAccessToken(data.token)
      return data.token
    })
    .finally(() => {
      refreshPromise = null
    })

  return refreshPromise
}

/** Identifie les routes qui ne doivent jamais déclencher une boucle de renouvellement. */
function isAuthenticationRequest(url = '') {
  return ['/auth/login', '/auth/refresh', '/auth/logout'].some(
    (path) => url === path || url.endsWith(path),
  )
}

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const request = error.config

    if (
      error.response?.status !== 401 ||
      !request ||
      request._retry ||
      isAuthenticationRequest(request.url)
    ) {
      return Promise.reject(error)
    }

    request._retry = true

    try {
      const token = await refreshAccessToken()
      setAuthorizationHeader(request, token)
      return apiClient(request)
    } catch (refreshError) {
      authSession.onSessionExpired()
      return Promise.reject(refreshError)
    }
  },
)
