import { defineStore } from 'pinia'
import { authApi } from '@/services/authApi'
import { accountApi } from '@/services/accountApi'
import { getApiErrorMessage } from '@/api/response'
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
    /** Mémorise ou efface le JWT d'accès reçu de l'API. */
    setAccessToken(token) {
      this.token = token || null
    },

    /** Efface toute identité locale quand le refresh échoue ou après déconnexion. */
    clearSession() {
      this.user = null
      this.token = null
    },

    /** Initialise directement l'écran public de connexion sans provoquer un refresh anonyme en 401. */
    initializeGuestSession() {
      this.clearSession()
      this.initializing = false
      this.initialized = true
    },

    /** Récupère le profil associé au JWT courant pour compléter la session en mémoire. */
    async fetchMe() {
      const user = await accountApi.getCurrent()
      this.user = user
      return user
    },

    /** Utilise les claims signés du JWT et ne sollicite `/me` qu'en solution de repli. */
    async hydrateUser(token) {
      const tokenUser = getUserFromAccessToken(token)
      if (tokenUser) {
        this.user = tokenUser
        return tokenUser
      }

      return this.fetchMe()
    },

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
          'Email ou mot de passe incorrect.',
        )
        return false
      } finally {
        this.loading = false
      }
    },

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
