import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL || '/api'

export const apiClient = axios.create({
  baseURL,
  withCredentials: true,
  headers: { Accept: 'application/ld+json' },
})

export const authClient = axios.create({
  baseURL,
  withCredentials: true,
  headers: { Accept: 'application/ld+json' },
})

let accessToken = null
let unauthorizedHandler = null

export function setAccessToken(token) {
  accessToken = typeof token === 'string' && token.length > 0 ? token : null
}

export function getAccessToken() {
  return accessToken
}

export function configureUnauthorizedHandler(handler) {
  unauthorizedHandler = handler
}

apiClient.interceptors.request.use((config) => {
  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`
  }

  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const request = error.config
    const canRefresh =
      error.response?.status === 401 &&
      request &&
      !request._refreshAttempted &&
      !request.url?.startsWith('/auth/') &&
      unauthorizedHandler

    if (!canRefresh) {
      return Promise.reject(error)
    }

    request._refreshAttempted = true

    try {
      await unauthorizedHandler()
      return apiClient.request(request)
    } catch {
      return Promise.reject(error)
    }
  },
)
