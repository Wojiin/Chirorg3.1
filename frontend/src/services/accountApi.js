import { apiClient } from '@/api/axios'

/** Regroupe les opérations du compte courant, indépendamment du cycle de session. */
export const accountApi = {
  /** Retourne le profil associé au JWT courant. */
  async getCurrent() {
    const { data } = await apiClient.get('/me')
    return data
  },

  /** Vérifie le secret actuel puis remplace le mot de passe du compte connecté. */
  async changePassword(passwords) {
    await apiClient.patch(
      '/me/mot-de-passe',
      {
        motDePasseActuel: passwords.currentPassword,
        nouveauMotDePasse: passwords.newPassword,
      },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
  },
}
