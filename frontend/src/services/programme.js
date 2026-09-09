import { apiClient } from '../api/http.js'
import { unwrapCollection } from './admin.js'

export const programmeApi = {
  async list(params = {}) {
    const { data } = await apiClient.get('/programmes-operatoires', { params })
    return unwrapCollection(data)
  },
  async get({ date, salle, chirurgien }) {
    const { data } = await apiClient.get(
      `/programmes-operatoires/${date}/${encodeURIComponent(salle)}/${chirurgien}`,
    )
    return data
  },
  async create(payload) {
    const { data } = await apiClient.post('/programmes-operatoires', payload, {
      headers: { 'Content-Type': 'application/ld+json' },
    })
    return data
  },
  async reorder({ date, salle, chirurgien, chirurgieIds }) {
    const { data } = await apiClient.patch(
      `/programmes-operatoires/${date}/${encodeURIComponent(salle)}/${chirurgien}/ordre`,
      { chirurgieIds },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
    return data
  },
  async removeSurgery(id) {
    await apiClient.delete(`/chirurgies-planifiees/${id}`)
  },
}
