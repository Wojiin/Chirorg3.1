export const PASSWORD_PATTERN_SOURCE = String.raw`(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])\S{12,128}`
const PASSWORD_PATTERN = new RegExp(`^${PASSWORD_PATTERN_SOURCE}$`)
export const PASSWORD_REQUIREMENTS =
  '12 caractères minimum, avec au moins une minuscule, une majuscule, un chiffre et un caractère spécial, sans espace.'

/** Retourne les erreurs par champ avant tout appel à l'API. */
export function validatePasswordChange(form) {
  const errors = {}

  if (!form.currentPassword) {
    errors.currentPassword = ERROR_MESSAGES.currentPasswordRequired
  }
  if (!PASSWORD_PATTERN.test(form.newPassword)) {
    errors.newPassword = PASSWORD_REQUIREMENTS
  }
  if (!form.newPasswordConfirmation) {
    errors.newPasswordConfirmation = ERROR_MESSAGES.passwordConfirmationRequired
  } else if (form.newPasswordConfirmation !== form.newPassword) {
    errors.newPasswordConfirmation = ERROR_MESSAGES.passwordConfirmationMismatch
  }

  return errors
}
import { ERROR_MESSAGES } from '@/config/errorMessages'
