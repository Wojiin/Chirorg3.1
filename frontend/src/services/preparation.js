import { apiClient } from '../api/http.js'

export const preparationApi = {
  async get(id) {
    const { data } = await apiClient.get(
      `/chirurgies-planifiees/${id}/preparation`,
    )
    return data
  },
  async setState(id, { coche, absent }) {
    const { data } = await apiClient.patch(
      `/preparations-materiel/${id}/cocher`,
      { coche, absent },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
    return data
  },
  async validate(id) {
    const { data } = await apiClient.post(
      `/chirurgies-planifiees/${id}/validation`,
    )
    return data
  },
  async getFinalView(id) {
    const { data } = await apiClient.get(
      `/chirurgies-planifiees/${id}/vue-finale`,
    )
    return data
  },
}
