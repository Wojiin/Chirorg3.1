import { reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { accountApi } from '@/services/accountApi'
import { getApiErrorMessage } from '@/api/response'
import { useAuthStore } from '@/stores/auth'
import { notifyError, notifySuccess } from '@/services/notifications'
import {
  PASSWORD_PATTERN_SOURCE,
  PASSWORD_REQUIREMENTS,
  validatePasswordChange,
} from '@/utils/password'

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

  function replaceErrors(errors) {
    for (const key of Object.keys(fieldErrors)) delete fieldErrors[key]
    Object.assign(fieldErrors, errors)
  }

  function submitPassword() {
    apiError.value = ''
    success.value = ''
    replaceErrors(validatePasswordChange(form))
    if (Object.keys(fieldErrors).length > 0) return
    confirmationOpen.value = true
  }

  function cancelConfirmation() {
    confirmationOpen.value = false
  }

  async function confirmPasswordChange() {
    confirmationOpen.value = false

    saving.value = true
    try {
      await accountApi.changePassword({ ...form })
      form.currentPassword = ''
      form.newPassword = ''
      form.newPasswordConfirmation = ''
      success.value = 'Votre mot de passe a été modifié.'
      notifySuccess('Mot de passe modifié', success.value)
    } catch (error) {
      apiError.value = getApiErrorMessage(
        error,
        'Le mot de passe n’a pas pu être modifié.',
      )
      notifyError('Modification impossible', apiError.value)
    } finally {
      saving.value = false
    }
  }

  return {
    PASSWORD_PATTERN_SOURCE,
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
