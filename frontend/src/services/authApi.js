import { apiClient, refreshAccessToken } from '@/api/axios'

/** Isole les endpoints d'authentification du reste de la logique de session Pinia. */
export const authApi = {
  // Renouvelle le JWT d'accès à partir du cookie de renouvellement. Dépendances utilisées : refreshAccessToken.
  /** Renouvelle le JWT d'accès à partir du cookie HTTP-only de session. */
  refreshSession() {
    return refreshAccessToken()
  },
  // Authentifie l'utilisateur et initialise sa session. Dépendances utilisées : apiClient.
  /** Authentifie des identifiants et retourne le JWT d'accès émis par l'API. */
  async login(credentials) {
    const { data } = await apiClient.post('/auth/login', credentials)
    return data
  },
  // Invalide le refresh token et termine la session de l'utilisateur. Dépendances utilisées : apiClient.
  /** Termine la session serveur et invalide le refresh token HTTP-only. */
  async logout() {
    const { data } = await apiClient.post('/auth/logout')
    return data
  },
}
