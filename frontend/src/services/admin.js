import { apiClient } from '../api/http.js'

export function unwrapCollection(data) {
  if (Array.isArray(data)) return data
  return data?.member ?? data?.['hydra:member'] ?? []
}

export const adminApi = {
  async list(resource, params = {}) {
    const { data } = await apiClient.get(`/${resource}`, {
      params,
      headers: { Accept: 'application/ld+json' },
    })
    return unwrapCollection(data)
  },
  async get(resource, id) {
    const { data } = await apiClient.get(`/${resource}/${id}`)
    return data
  },
  async create(resource, payload) {
    const { data } = await apiClient.post(`/${resource}`, payload, {
      headers: { 'Content-Type': 'application/ld+json' },
    })
    return data
  },
  async update(resource, id, payload) {
    const { data } = await apiClient.patch(`/${resource}/${id}`, payload, {
      headers: { 'Content-Type': 'application/merge-patch+json' },
    })
    return data
  },
  async remove(resource, id) {
    await apiClient.delete(`/${resource}/${id}`)
  },
}
