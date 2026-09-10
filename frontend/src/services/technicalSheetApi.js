import { apiClient } from '@/api/axios'
import { unwrapCollection } from '@/api/response'

/** Centralise les lectures et téléversements propres aux fiches techniques. */
export const technicalSheetApi = {
  /** Retourne les consignes ordonnées du modèle de chirurgie demandé. */
  async listForSurgeryModel(surgeryModelId) {
    const { data } = await apiClient.get(
      `/chirurgie-modeles/${surgeryModelId}/fiches-techniques`,
    )
    return unwrapCollection(data)
  },

  /** Téléverse une illustration et retourne son chemin public contrôlé par l'API. */
  async uploadImage(image) {
    const body = new FormData()
    body.append('image', image)
    const { data } = await apiClient.post('/fiche-technique-images', body)
    return data.url
  },
}
