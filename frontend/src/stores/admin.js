import { defineStore } from 'pinia'

import { apiErrorMessage } from '../api/problem.js'
import { adminApi } from '../services/admin.js'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    items: [],
    current: null,
    loading: false,
    saving: false,
    deletingId: null,
    error: null,
  }),
  actions: {
    async loadItems(resource, params = {}) {
      this.loading = true
      this.error = null
      try {
        this.items = await adminApi.list(resource, params)
        return this.items
      } catch (error) {
        this.items = []
        this.error = apiErrorMessage(
          error,
          'Impossible de charger ce référentiel.',
        )
        return []
      } finally {
        this.loading = false
      }
    },
    async loadItem(resource, id) {
      this.loading = true
      this.error = null
      try {
        this.current = await adminApi.get(resource, id)
        return this.current
      } catch (error) {
        this.current = null
        this.error = apiErrorMessage(
          error,
          'Impossible de charger cette ressource.',
        )
        return null
      } finally {
        this.loading = false
      }
    },
    async saveItem(resource, id, payload) {
      this.saving = true
      this.error = null
      try {
        this.current = id
          ? await adminApi.update(resource, id, payload)
          : await adminApi.create(resource, payload)
        return this.current
      } catch (error) {
        this.error = apiErrorMessage(error, 'L’enregistrement a échoué.')
        return null
      } finally {
        this.saving = false
      }
    },
    async removeItem(resource, id) {
      this.deletingId = id
      this.error = null
      try {
        await adminApi.remove(resource, id)
        this.items = this.items.filter((item) => item.id !== id)
        return true
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Cette ressource ne peut pas être supprimée.',
        )
        return false
      } finally {
        this.deletingId = null
      }
    },
  },
})
