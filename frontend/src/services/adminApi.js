import { apiClient } from '@/api/axios'
import { unwrapPaginatedCollection } from '@/api/response'

/** Porte les appels CRUD génériques des référentiels administratifs, sans état d'interface. */
export const adminApi = {
  /** Liste une ressource API et normalise les formats de collection API Platform. */
  async list(resource, params = {}) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.get(`/${apiResource}`, {
      params,
      headers: { Accept: 'application/ld+json' },
    })
    return unwrapPaginatedCollection(data)
  },
  /** Charge toutes les pages d'un référentiel destiné à alimenter un sélecteur. */
  async listAll(resource, params = {}) {
    const itemsPerPage = 100
    const firstPage = await this.list(resource, {
      ...params,
      page: 1,
      itemsPerPage,
    })
    const normalizedFirstPage = Array.isArray(firstPage)
      ? { items: firstPage, totalItems: firstPage.length }
      : firstPage
    const items = [...normalizedFirstPage.items]
    const pageCount = Math.ceil(normalizedFirstPage.totalItems / itemsPerPage)

    for (let page = 2; page <= pageCount; page += 1) {
      const result = await this.list(resource, {
        ...params,
        page,
        itemsPerPage,
      })
      items.push(...(Array.isArray(result) ? result : result.items))
    }

    return items
  },
  /** Charge une ressource administrative par son identifiant. */
  async get(resource, id) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.get(`/${apiResource}/${id}`)
    return data
  },
  /** Crée une ressource administrative à partir d'un payload déjà normalisé. */
  async create(resource, payload) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.post(`/${apiResource}`, payload, {
      headers: { 'Content-Type': 'application/ld+json' },
    })
    return data
  },
  /** Met à jour partiellement une ressource avec le média type API Platform adapté. */
  async update(resource, id, payload) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.patch(`/${apiResource}/${id}`, payload, {
      headers: { 'Content-Type': 'application/merge-patch+json' },
    })
    return data
  },
  /** Supprime une ressource ; les règles de dépendance restent appliquées par l'API. */
  async remove(resource, id) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    await apiClient.delete(`/${apiResource}/${id}`)
  },
}
