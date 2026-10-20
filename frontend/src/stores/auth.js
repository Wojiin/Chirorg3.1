import { defineStore } from 'pinia'
import { authApi } from '@/services/authApi'
import { accountApi } from '@/services/accountApi'
import { getApiErrorMessage } from '@/api/response'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { getUserFromAccessToken } from '@/utils/jwt'

// Empêche plusieurs gardes de route de lancer simultanément le même refresh silencieux.
let initializationPromise = null

/** Centralise la session utilisateur en mémoire et ne persiste jamais le JWT dans le navigateur. */
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    loading: false,
    initializing: false,
    initialized: false,
    error: '',
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
    isAdmin: (state) => state.user?.roles?.includes('ROLE_ADMIN') ?? false,
    displayName: (state) =>
      state.user?.email?.split('@')[0] || 'Compte ChirOrg',
  },

  actions: {
    // Conserve ou efface le JWT d'accès dans le store Pinia. Dépendances utilisées : aucune dépendance importée directe.
    /** Mémorise ou efface le JWT d'accès reçu de l'API. */
    setAccessToken(token) {
      this.token = token || null
    },

    // Efface le JWT d'accès et le profil utilisateur du store Pinia. Dépendances utilisées : aucune dépendance importée directe.
    /** Efface toute identité locale quand le refresh échoue ou après déconnexion. */
    clearSession() {
      this.user = null
      this.token = null
    },

    // Initialise la page de connexion sans appeler le renouvellement. Dépendances utilisées : aucune dépendance importée directe.
    /** Initialise directement l'écran public de connexion sans provoquer un refresh anonyme en 401. */
    initializeGuestSession() {
      this.clearSession()
      this.initializing = false
      this.initialized = true
    },

    // Charge le profil de l'utilisateur authentifié depuis l'API. Dépendances utilisées : accountApi.
    /** Récupère le profil associé au JWT courant pour compléter la session en mémoire. */
    async fetchMe() {
      const user = await accountApi.getCurrent()
      this.user = user
      return user
    },

    // Reconstruit le profil utilisateur depuis les claims du JWT ou l'API. Dépendances utilisées : getUserFromAccessToken.
    /** Utilise les claims signés du JWT et ne sollicite `/me` qu'en solution de repli. */
    async hydrateUser(token) {
      const tokenUser = getUserFromAccessToken(token)
      if (tokenUser) {
        this.user = tokenUser
        return tokenUser
      }

      return this.fetchMe()
    },

    // Authentifie l'utilisateur et initialise sa session. Dépendances utilisées : ERROR_MESSAGES, authApi, getApiErrorMessage.
    /** Authentifie l'utilisateur, conserve le token en mémoire puis hydrate son profil. */
    async login(credentials) {
      this.loading = true
      this.error = ''

      try {
        const data = await authApi.login(credentials)
        this.setAccessToken(data.token)
        await this.hydrateUser(data.token)
        this.initialized = true
        return true
      } catch (error) {
        this.clearSession()
        this.error = getApiErrorMessage(
          error,
          ERROR_MESSAGES.invalidCredentials,
        )
        return false
      } finally {
        this.loading = false
      }
    },

    // Remplace la session après la modification du mot de passe. Dépendances utilisées : authApi.
    /** Remplace silencieusement la session révoquée après un changement de mot de passe. */
    async renewSession(credentials) {
      try {
        const data = await authApi.login(credentials)
        this.setAccessToken(data.token)
        await this.hydrateUser(data.token)
        this.initialized = true
        return true
      } catch (error) {
        this.clearSession()
        throw error
      }
    },

    // Restaure la session avec le cookie de renouvellement au démarrage de la SPA. Dépendances utilisées : authApi.
    /** Tente une restauration silencieuse de session une seule fois au démarrage de la SPA. */
    initialize() {
      if (this.initialized) return Promise.resolve(this.isAuthenticated)
      if (initializationPromise) return initializationPromise

      this.initializing = true
      initializationPromise = (async () => {
        try {
          if (!this.token) {
            this.setAccessToken(await authApi.refreshSession())
          }
          await this.hydrateUser(this.token)
          return true
        } catch {
          this.clearSession()
          return false
        } finally {
          this.initialized = true
          this.initializing = false
          initializationPromise = null
        }
      })()

      return initializationPromise
    },

    // Invalide le refresh token et termine la session de l'utilisateur. Dépendances utilisées : authApi.
    /** Invalide côté serveur puis nettoie la session locale même en cas d'indisponibilité réseau. */
    async logout() {
      this.loading = true
      this.error = ''

      try {
        await authApi.logout()
      } catch {
        // Le client est déconnecté même si l'API est momentanément indisponible.
      } finally {
        this.clearSession()
        this.initialized = true
        this.loading = false
      }
    },
  },
})
