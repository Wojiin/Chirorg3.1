import { apiClient } from '@/api/axios'
import { unwrapPaginatedCollection } from '@/api/response'

/** Porte les appels CRUD génériques des référentiels administratifs, sans état d'interface. */
export const adminApi = {
  // Retourne une page du référentiel administré. Dépendances utilisées : apiClient, unwrapPaginatedCollection.
  /** Liste les salles, spécialités, chirurgiens, matériels, chirurgies modèles, listes de matériel ou utilisateurs et normalise la collection API Platform. */
  async list(resource, params = {}) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.get(`/${apiResource}`, {
      params,
      headers: { Accept: 'application/ld+json' },
    })
    return unwrapPaginatedCollection(data)
  },
  // Charge toutes les pages du référentiel administré. Dépendances utilisées : aucune dépendance importée directe.
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
  // Retourne les référentiels administrés identifié par son identifiant. Dépendances utilisées : apiClient.
  /** Charge un référentiel administratif par son identifiant. */
  async get(resource, id) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.get(`/${apiResource}/${id}`)
    return data
  },
  // Crée les référentiels administrés à partir des valeurs du formulaire. Dépendances utilisées : apiClient.
  /** Crée un référentiel administratif à partir des données déjà normalisées par le formulaire. */
  async create(resource, payload) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.post(`/${apiResource}`, payload, {
      headers: { 'Content-Type': 'application/ld+json' },
    })
    return data
  },
  // Met à jour les référentiels administrés à partir des valeurs du formulaire. Dépendances utilisées : apiClient.
  /** Met à jour partiellement un référentiel administratif avec le type de contenu attendu par API Platform. */
  async update(resource, id, payload) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    const { data } = await apiClient.patch(`/${apiResource}/${id}`, payload, {
      headers: { 'Content-Type': 'application/merge-patch+json' },
    })
    return data
  },
  // Supprime les référentiels administrés après validation des règles métier. Dépendances utilisées : apiClient.
  /** Supprime un référentiel administratif après application des règles liées aux chirurgies, programmes et matériels par l'API. */
  async remove(resource, id) {
    const apiResource = resource === 'users' ? 'utilisateurs' : resource
    await apiClient.delete(`/${apiResource}/${id}`)
  },
}
