<script setup>
/** Vue de formulaire administratif : son script ne relie que l'affichage au composable dédié. */
import { useAdminFormView } from '@/composables/useAdminFormView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseTextarea from '@/components/ui/BaseTextarea.vue'
import MaterialPicker from '@/components/MaterialPicker.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

const props = defineProps({
  resourceSlug: { type: String, required: true },
  id: { type: Number, default: null },
})

const {
  currentImageUrl,
  confirmationOpen,
  cancelConfirmation,
  confirmSubmit,
  displayedError,
  fields,
  fileInputKey,
  form,
  isEditing,
  loading,
  goBack,
  removeImage,
  resource,
  saving,
  selectImage,
  submit,
  uploading,
} = useAdminFormView(props)
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Administration"
      :title="`${isEditing ? 'Modifier' : 'Ajouter'} ${resource?.label ?? 'Référentiel'}`"
      description="Renseignez les informations du référentiel."
    />

    <form v-if="resource" class="form-panel" @submit.prevent="submit">
      <ErrorMessage v-if="displayedError" :message="displayedError" />
      <fieldset class="form-grid" :disabled="loading">
        <legend class="sr-only">Informations de la ressource</legend>
        <template v-for="field in fields" :key="field.key">
          <div v-if="field.type === 'file'" class="md:col-span-2">
            <label :for="field.key" class="field-label">{{
              field.label
            }}</label>
            <input
              :id="field.key"
              :key="fileInputKey"
              type="file"
              :accept="field.accept"
              class="field-control cursor-pointer file:mr-4 file:rounded-lg file:border-0 file:bg-chirorg-100 file:px-3 file:py-2 file:font-semibold file:text-chirorg-800"
              @change="selectImage"
            />
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
              JPEG, PNG ou WebP. 5 Mo maximum. Le texte reste facultatif si une
              image est fournie.
            </p>
            <p v-if="form.imageFile" class="mt-3 text-sm font-medium">
              Nouvelle image : {{ form.imageFile.name }}
            </p>
            <div
              v-if="form.lienImage"
              class="mt-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700"
            >
              <p
                class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500"
              >
                Image actuelle
              </p>
              <img
                :src="currentImageUrl"
                alt="Illustration actuelle de la consigne technique"
                class="max-h-64 w-full rounded-lg bg-white object-contain dark:bg-gray-900"
              />
            </div>
            <BaseButton
              v-if="form.imageFile || form.lienImage"
              type="button"
              variant="secondary"
              size="sm"
              class="mt-3"
              @click="removeImage"
            >
              Retirer l’image
            </BaseButton>
          </div>
          <BaseSelect
            v-else-if="field.type === 'select'"
            v-model="form[field.key]"
            :label="field.label"
            :options="field.options"
            :required="field.required"
          />
          <MaterialPicker
            v-else-if="field.type === 'material-picker'"
            v-model="form[field.key]"
            :label="field.label"
            :options="field.options"
            :required="field.required"
            :empty-message="
              form.chirurgien
                ? 'Aucun matériel de cette spécialité ne correspond à la recherche.'
                : 'Sélectionnez d’abord un chirurgien pour afficher les matériels de sa spécialité.'
            "
          />
          <BaseTextarea
            v-else-if="field.type === 'textarea'"
            v-model="form[field.key]"
            :label="field.label"
            :required="field.required"
            class="md:col-span-2"
          />
          <BaseInput
            v-else
            v-model="form[field.key]"
            :label="field.label"
            :type="field.type ?? 'text'"
            :required="field.required"
          />
        </template>
      </fieldset>
      <div class="form-actions">
        <BaseButton type="button" variant="secondary" @click="goBack">
          Retour
        </BaseButton>
        <BaseButton type="submit" :loading="saving || uploading"
          >Enregistrer</BaseButton
        >
      </div>
    </form>
    <ErrorMessage v-else :message="displayedError" />

    <ConfirmationModal
      :open="confirmationOpen"
      variant="success"
      :title="
        isEditing ? 'Confirmer les modifications' : 'Confirmer la création'
      "
      :message="`Voulez-vous ${isEditing ? 'enregistrer les modifications de' : 'créer'} ${resource?.label ?? 'cet élément'} ?`"
      :confirm-label="isEditing ? 'Enregistrer' : 'Créer'"
      :loading="saving || uploading"
      @cancel="cancelConfirmation"
      @confirm="confirmSubmit"
    />
  </PageContainer>
</template>
