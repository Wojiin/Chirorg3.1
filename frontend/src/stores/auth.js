import { defineStore } from 'pinia'

import { setAccessToken } from '../api/http.js'
import { apiErrorMessage } from '../api/problem.js'
import { authService } from '../services/auth.js'

const PROFILE_STORAGE_KEY = 'chirorg.profile'
let refreshPromise = null

function readStoredProfile() {
  try {
    const value = window.localStorage.getItem(PROFILE_STORAGE_KEY)
    return value ? JSON.parse(value) : null
  } catch {
    window.localStorage.removeItem(PROFILE_STORAGE_KEY)
    return null
  }
}

function persistProfile(profile) {
  if (profile) {
    window.localStorage.setItem(PROFILE_STORAGE_KEY, JSON.stringify(profile))
  } else {
    window.localStorage.removeItem(PROFILE_STORAGE_KEY)
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    profile: readStoredProfile(),
    authenticated: false,
    initialized: false,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => state.authenticated,
    roles: (state) => state.profile?.roles || [],
    isAdmin() {
      return this.roles.includes('ROLE_ADMIN')
    },
  },

  actions: {
    setProfile(profile) {
      this.profile = profile
      persistProfile(profile)
    },

    clearSession() {
      setAccessToken(null)
      this.authenticated = false
      this.setProfile(null)
    },

    async login(credentials) {
      this.loading = true
      this.error = null

      try {
        const session = await authService.login(credentials)
        if (!session?.token) {
          throw new Error('Le serveur n’a pas retourné de jeton d’accès.')
        }
        setAccessToken(session.token)
        this.authenticated = true
        this.setProfile(await authService.me())
        this.initialized = true
      } catch (error) {
        this.clearSession()
        this.error = apiErrorMessage(error, 'Connexion impossible.')
        throw error
      } finally {
        this.loading = false
      }
    },

    refreshAccessToken() {
      if (!refreshPromise) {
        refreshPromise = authService
          .refresh()
          .then(({ token }) => {
            if (!token) {
              throw new Error('Le renouvellement de session a échoué.')
            }
            setAccessToken(token)
            this.authenticated = true
            return token
          })
          .catch((error) => {
            this.clearSession()
            throw error
          })
          .finally(() => {
            refreshPromise = null
          })
      }

      return refreshPromise
    },

    async initialize() {
      if (this.initialized) return

      this.loading = true
      try {
        await this.refreshAccessToken()
        this.setProfile(await authService.me())
      } catch {
        this.clearSession()
      } finally {
        this.initialized = true
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await authService.logout()
      } finally {
        this.clearSession()
        this.initialized = true
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },
  },
})
