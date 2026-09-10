import { defineStore } from 'pinia'
import { adminApi } from '@/services/adminApi'
import { getApiErrorMessage } from '@/api/response'
import { ERROR_MESSAGES } from '@/config/errorMessages'

/** Porte l'état et les actions CRUD de l'écran d'administration courant. */
export const useAdminStore = defineStore('admin', {
  state: () => ({
    resource: '',
    items: [],
    totalItems: 0,
    page: 1,
    itemsPerPage: 10,
    current: null,
    pendingLoads: 0,
    listRequestId: 0,
    itemRequestId: 0,
    saving: false,
    deletingId: null,
    error: '',
  }),

  getters: {
    loading: (state) => state.pendingLoads > 0,
  },

  actions: {
    /** Charge la collection du référentiel demandé et remplace les résultats précédents. */
    async loadItems(resource, params = {}) {
      this.resource = resource
      const requestId = ++this.listRequestId
      this.pendingLoads += 1
      this.error = ''

      try {
        const result = await adminApi.list(resource, {
          page: params.page ?? this.page,
          itemsPerPage: params.itemsPerPage ?? this.itemsPerPage,
          ...params,
        })
        const collection = Array.isArray(result)
          ? { items: result, totalItems: result.length }
          : result
        if (requestId === this.listRequestId) {
          this.items = collection.items
          this.totalItems = collection.totalItems
          this.page = Number(params.page ?? this.page)
        }
        return collection.items
      } catch (error) {
        if (requestId === this.listRequestId) {
          this.items = []
          this.totalItems = 0
          this.error = getApiErrorMessage(error, ERROR_MESSAGES.adminListLoad)
        }
        return []
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Charge un élément à modifier tout en réinitialisant l'élément courant. */
    async loadItem(resource, id) {
      this.resource = resource
      this.current = null
      const requestId = ++this.itemRequestId
      this.pendingLoads += 1
      this.error = ''

      try {
        const item = await adminApi.get(resource, id)
        if (requestId === this.itemRequestId) this.current = item
        return item
      } catch (error) {
        if (requestId === this.itemRequestId) {
          this.error = getApiErrorMessage(error, ERROR_MESSAGES.adminItemLoad)
        }
        return null
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Crée ou met à jour un élément selon la présence de son identifiant. */
    async saveItem(resource, id, payload) {
      this.saving = true
      this.error = ''

      try {
        const item = id
          ? await adminApi.update(resource, id, payload)
          : await adminApi.create(resource, payload)
        this.current = item
        return item
      } catch (error) {
        this.error = getApiErrorMessage(error, ERROR_MESSAGES.adminSave)
        return null
      } finally {
        this.saving = false
      }
    },

    /** Supprime un élément puis retire immédiatement sa représentation de la liste locale. */
    async removeItem(resource, id) {
      this.deletingId = id
      this.error = ''

      try {
        await adminApi.remove(resource, id)
        this.items = this.items.filter((item) => item.id !== id)
        this.totalItems = Math.max(0, this.totalItems - 1)
        return true
      } catch (error) {
        this.error = getApiErrorMessage(error, ERROR_MESSAGES.adminDelete)
        return false
      } finally {
        this.deletingId = null
      }
    },
  },
})
