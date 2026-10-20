import { apiClient } from '@/api/axios'

/** Regroupe les appels relatifs à la checklist matériel et à la clôture d'une chirurgie. */
export const preparationApi = {
  // Retourne préparation de la préparation du matériel. Dépendances utilisées : apiClient.
  /** Charge la checklist enrichie d'une chirurgie planifiée. */
  async getPreparation(id) {
    const { data } = await apiClient.get(
      `/chirurgies-planifiees/${id}/preparation`,
    )
    return data
  },
  // Inverse l'état coché ou absent du matériel préparé. Dépendances utilisées : apiClient.
  /** Demande la transition cochée / décochée d'un matériel. */
  async toggle(id, { coche, absent }) {
    const { data } = await apiClient.patch(
      `/preparations-materiel/${id}/cocher`,
      { coche, absent },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
    return data
  },
  // Valide la préparation du matériel selon les règles métier. Dépendances utilisées : apiClient.
  /** Demande la validation métier d'une chirurgie dont la checklist est complète. */
  async validate(id) {
    const { data } = await apiClient.post(
      `/chirurgies-planifiees/${id}/validation`,
    )
    return data
  },
  // Retourne finale vue de la préparation du matériel. Dépendances utilisées : apiClient.
  /** Charge la synthèse finale disponible après validation. */
  async getFinalView(id) {
    const { data } = await apiClient.get(
      `/chirurgies-planifiees/${id}/vue-finale`,
    )
    return data
  },
}
