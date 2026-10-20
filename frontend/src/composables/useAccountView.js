import { reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { accountApi } from '@/services/accountApi'
import { getApiErrorMessage } from '@/api/response'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { useAuthStore } from '@/stores/auth'
import { notifyError, notifySuccess } from '@/services/notifications'
import { PASSWORD_REQUIREMENTS, validatePasswordChange } from '@/utils/password'

// Regroupe l'état réactif et les actions de la page consacrée à la session de l'utilisateur. Dépendances utilisées : ERROR_MESSAGES, PASSWORD_REQUIREMENTS, accountApi, authStore, getApiErrorMessage, notifyError, notifySuccess, reactive, ref, storeToRefs, useAuthStore, validatePasswordChange.
/** Orchestre l'affichage du compte et le changement de mot de passe. */
export function useAccountView() {
  const authStore = useAuthStore()
  const { user, displayName, isAdmin } = storeToRefs(authStore)
  const form = reactive({
    currentPassword: '',
    newPassword: '',
    newPasswordConfirmation: '',
  })
  const fieldErrors = reactive({})
  const apiError = ref('')
  const success = ref('')
  const saving = ref(false)
  const confirmationOpen = ref(false)

  // Remplace les erreurs affichées dans le formulaire de mot de passe. Dépendances utilisées : aucune dépendance importée directe.
  function replaceErrors(errors) {
    for (const key of Object.keys(fieldErrors)) delete fieldErrors[key]
    Object.assign(fieldErrors, errors)
  }

  // Valide le formulaire de changement de mot de passe. Dépendances utilisées : validatePasswordChange.
  function submitPassword() {
    apiError.value = ''
    success.value = ''
    replaceErrors(validatePasswordChange(form))
    if (Object.keys(fieldErrors).length > 0) return
    confirmationOpen.value = true
  }

  // Ferme la confirmation sans modifier le mot de passe. Dépendances utilisées : aucune dépendance importée directe.
  function cancelConfirmation() {
    confirmationOpen.value = false
  }

  // Enregistre le nouveau mot de passe puis renouvelle la session. Dépendances utilisées : ERROR_MESSAGES, accountApi, authStore, getApiErrorMessage, notifyError, notifySuccess.
  async function confirmPasswordChange() {
    confirmationOpen.value = false

    saving.value = true
    try {
      const email = user.value?.email
      const newPassword = form.newPassword
      await accountApi.changePassword({ ...form })
      await authStore.renewSession({ email, password: newPassword })
      form.currentPassword = ''
      form.newPassword = ''
      form.newPasswordConfirmation = ''
      success.value = 'Votre mot de passe a été modifié.'
      notifySuccess('Mot de passe modifié', success.value)
    } catch (error) {
      apiError.value = getApiErrorMessage(error, ERROR_MESSAGES.passwordChange)
      notifyError(ERROR_MESSAGES.passwordChangeNotification, apiError.value)
    } finally {
      saving.value = false
    }
  }

  return {
    PASSWORD_REQUIREMENTS,
    apiError,
    confirmationOpen,
    cancelConfirmation,
    confirmPasswordChange,
    displayName,
    fieldErrors,
    form,
    isAdmin,
    saving,
    submitPassword,
    success,
    user,
  }
}
