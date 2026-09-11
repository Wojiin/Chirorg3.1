import { apiClient } from '@/api/axios'
import { unwrapPaginatedCollection } from '@/api/response'

/** Encapsule les contrats API de consultation, création et réordonnancement des programmes. */
export const programmeApi = {
  /** Liste les résumés de programmes avec les filtres optionnels fournis. */
  async list(params = {}) {
    const { data } = await apiClient.get('/programmes-operatoires', { params })
    return unwrapPaginatedCollection(data)
  },
  /** Charge le détail d'un programme identifié par date, salle et chirurgien. */
  async getProgramme({ date, salle, chirurgien }) {
    const { data } = await apiClient.get(
      `/programmes-operatoires/${date}/${encodeURIComponent(salle)}/${chirurgien}`,
    )
    return data
  },
  /** Crée plusieurs chirurgies dans un même programme opératoire. */
  async planProgram(payload) {
    const { data } = await apiClient.post('/programmes-operatoires', payload, {
      headers: { 'Content-Type': 'application/ld+json' },
    })
    return data
  },
  /** Envoie la permutation complète des chirurgies afin que l'API recalcule les ordres. */
  async reorder({ date, salle, chirurgien, chirurgieIds }) {
    const { data } = await apiClient.patch(
      `/programmes-operatoires/${date}/${encodeURIComponent(salle)}/${chirurgien}/ordre`,
      { chirurgieIds },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
    return data
  },
  /** Supprime une chirurgie planifiée lorsque son état métier l'autorise. */
  async deleteSurgery(id) {
    await apiClient.delete(`/chirurgies-planifiees/${id}`)
  },
}
