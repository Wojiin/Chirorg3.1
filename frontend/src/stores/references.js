import { defineStore } from 'pinia'
import { adminApi } from '@/services/adminApi'
import { getApiErrorMessage } from '@/api/response'
import { ERROR_MESSAGES } from '@/config/errorMessages'

/** Met en cache les petits référentiels partagés par les formulaires et la planification. */
export const useReferenceStore = defineStore('references', {
  state: () => ({
    collections: {},
    pendingLoads: 0,
    error: '',
  }),

  getters: {
    loading: (state) => state.pendingLoads > 0,
    getCollection: (state) => (resource) => state.collections[resource] ?? [],
  },

  actions: {
    /** Charge uniquement les collections absentes, sauf si un rechargement forcé est demandé. */
    async load(resources, { force = false } = {}) {
      const requested = [...new Set(resources)]
      const missing = force
        ? requested
        : requested.filter(
            (resource) => !Object.hasOwn(this.collections, resource),
          )

      if (!missing.length) return this.collections

      this.pendingLoads += 1
      this.error = ''

      try {
        const results = await Promise.all(
          missing.map((resource) => adminApi.list(resource)),
        )
        this.collections = {
          ...this.collections,
          ...Object.fromEntries(
            missing.map((resource, index) => [resource, results[index]]),
          ),
        }
        return this.collections
      } catch (error) {
        this.error = getApiErrorMessage(error, ERROR_MESSAGES.referencesLoad)
        throw error
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Invalide une collection après une mutation administrative afin d'éviter les valeurs périmées. */
    invalidate(resource) {
      const collections = { ...this.collections }
      delete collections[resource]
      this.collections = collections
    },
  },
})
