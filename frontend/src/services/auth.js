import { apiClient, authClient } from '../api/http.js'

export const authService = {
  async login(credentials) {
    const { data } = await authClient.post('/auth/login', credentials)
    return data
  },

  async refresh() {
    const { data } = await authClient.post('/auth/refresh')
    return data
  },

  async logout() {
    await authClient.post('/auth/logout')
  },

  async me() {
    const { data } = await apiClient.get('/me')
    return data
  },
}
