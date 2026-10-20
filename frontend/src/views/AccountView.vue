<script setup>
/** Vue du compte : son script ne relie que l'affichage au composable dédié. */
import { useAccountView } from '@/composables/useAccountView'
import PageContainer from '@/components/ui/PageContainer.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

const {
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
} = useAccountView()
</script>

<template>
  <PageContainer>
    <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
    <div class="account-layout">
      <!-- Délimite la section consacrée à la page consacrée à la session de l'utilisateur. -->
      <section class="account-panel">
        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div class="account-heading">
          <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
          <div aria-hidden="true" class="account-avatar">
            {{ displayName.charAt(0).toUpperCase() }}
          </div>
          <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
          <div>
            <h2 class="section-title">Informations du compte</h2>
            <p class="text-muted">{{ user?.email }}</p>
          </div>
        </div>
        <dl class="account-metadata">
          <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
          <div>
            <dt>Rôle</dt>
            <dd><StatusBadge :status="isAdmin ? 'Admin' : 'Utilisateur'" /></dd>
          </div>
        </dl>
      </section>

      <!-- Regroupe les champs et la soumission de la page consacrée à la session de l'utilisateur. -->
      <form
        class="form-panel account-password-panel"
        novalidate
        @submit.prevent="submitPassword"
      >
        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div>
          <h2 class="section-title">Changer le mot de passe</h2>
          <p class="page-description">{{ PASSWORD_REQUIREMENTS }}</p>
        </div>

        <ErrorMessage v-if="apiError" :message="apiError" />
        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div v-if="success" role="status" class="feedback-success">
          {{ success }}
        </div>

        <!-- Regroupe les champs métier de la page consacrée à la session de l'utilisateur. -->
        <fieldset class="space-y-5" :disabled="saving">
          <legend class="sr-only">Nouveau mot de passe</legend>
          <BaseInput
            v-model="form.currentPassword"
            label="Mot de passe actuel"
            type="password"
            autocomplete="current-password"
            :error="fieldErrors.currentPassword"
            required
          />
          <BaseInput
            v-model="form.newPassword"
            label="Nouveau mot de passe"
            type="password"
            autocomplete="new-password"
            :maxlength="128"
            :error="fieldErrors.newPassword"
            required
          />
          <BaseInput
            v-model="form.newPasswordConfirmation"
            label="Confirmer le nouveau mot de passe"
            type="password"
            autocomplete="new-password"
            :maxlength="128"
            :error="fieldErrors.newPasswordConfirmation"
            required
          />
        </fieldset>

        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div class="form-actions">
          <BaseButton type="submit" :loading="saving"
            >Modifier le mot de passe</BaseButton
          >
        </div>
      </form>
    </div>

    <ConfirmationModal
      :open="confirmationOpen"
      variant="warning"
      title="Confirmer le changement de mot de passe"
      message="Cette action modifiera immédiatement vos identifiants de connexion. Voulez-vous continuer ?"
      confirm-label="Modifier le mot de passe"
      :loading="saving"
      @cancel="cancelConfirmation"
      @confirm="confirmPasswordChange"
    />
  </PageContainer>
</template>
