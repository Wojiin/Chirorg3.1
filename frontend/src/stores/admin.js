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
    // Charge entrées des référentiels administrés. Dépendances utilisées : ERROR_MESSAGES, adminApi, getApiErrorMessage.
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

    // Charge entrée des référentiels administrés. Dépendances utilisées : ERROR_MESSAGES, adminApi, getApiErrorMessage.
    /** Charge le référentiel administratif à modifier et réinitialise la sélection précédente. */
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

    // Enregistre entrée des référentiels administrés. Dépendances utilisées : ERROR_MESSAGES, adminApi, getApiErrorMessage.
    /** Crée ou met à jour une salle, une spécialité, un chirurgien, un matériel, une chirurgie modèle, une liste de matériel ou un utilisateur. */
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

    // Retire entrée des référentiels administrés. Dépendances utilisées : ERROR_MESSAGES, adminApi, getApiErrorMessage.
    /** Supprime le référentiel administratif puis le retire immédiatement de la liste affichée. */
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
