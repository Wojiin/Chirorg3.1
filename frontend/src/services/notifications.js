import { toast } from 'vue-sonner'

const defaultOptions = { duration: 4500 }

/** Affiche un retour positif homogène sans coupler les composables au composant visuel. */
export function notifySuccess(message, description) {
  return toast.success(message, { ...defaultOptions, description })
}

/** Rend une erreur d'action visible tout en conservant son message contextuel dans la vue. */
export function notifyError(message, description) {
  return toast.error(message, { ...defaultOptions, description })
}
