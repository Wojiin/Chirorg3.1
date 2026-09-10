<script setup>
/** Vue de planification : son script ne relie que l'affichage au composable dédié. */
import { usePlanificationView } from '@/composables/usePlanificationView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'

const {
  addSurgery,
  cancel,
  displayedError,
  form,
  minimumDate,
  planning,
  referencesLoading,
  removeSurgery,
  rooms,
  specialties,
  submit,
  surgeons,
  surgeries,
} = usePlanificationView()
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Programme opératoire"
      title="Planifier un programme"
      description="Choisissez une spécialité pour filtrer le chirurgien et les interventions, puis définissez leur ordre initial."
    />

    <form class="form-panel" @submit.prevent="submit">
      <ErrorMessage v-if="displayedError" :message="displayedError" />

      <fieldset class="programme-form-section">
        <legend class="section-title">Informations du programme</legend>
        <div class="form-grid mt-5">
          <BaseSelect
            v-model="form.specialiteId"
            label="Spécialité"
            :options="specialties"
            placeholder="Sélectionner une spécialité"
            required
          />
          <BaseSelect
            v-model="form.chirurgienId"
            label="Chirurgien"
            :options="surgeons"
            :placeholder="
              form.specialiteId
                ? 'Sélectionner un chirurgien'
                : 'Sélectionner d’abord une spécialité'
            "
            :disabled="!form.specialiteId"
            required
          />
          <BaseInput
            v-model="form.dateProgrammee"
            label="Date programmée"
            type="date"
            :min="minimumDate"
            required
          />
          <BaseSelect
            v-model="form.salle"
            label="Salle"
            :options="rooms"
            required
          />
        </div>
      </fieldset>

      <fieldset class="programme-form-section">
        <legend class="section-title">Chirurgies du programme</legend>
        <p class="text-muted mt-2">
          L’ordre de cette liste devient l’ordre initial du programme.
        </p>

        <ol class="surgery-model-list">
          <li
            v-for="(_, index) in form.chirurgieModeleIds"
            :key="index"
            class="surgery-model-row"
          >
            <BaseSelect
              v-model="form.chirurgieModeleIds[index]"
              :label="`Chirurgie ${index + 1}`"
              :options="surgeries"
              :placeholder="
                form.specialiteId
                  ? 'Sélectionner une intervention'
                  : 'Sélectionner d’abord une spécialité'
              "
              :disabled="!form.specialiteId"
              required
            />
            <BaseButton
              v-if="form.chirurgieModeleIds.length > 1"
              type="button"
              variant="ghost"
              size="sm"
              class="surgery-remove-button"
              @click="removeSurgery(index)"
            >
              Retirer
            </BaseButton>
          </li>
        </ol>

        <BaseButton
          type="button"
          variant="secondary"
          class="mt-4"
          @click="addSurgery"
        >
          + Ajouter une chirurgie
        </BaseButton>
      </fieldset>

      <div class="form-actions">
        <BaseButton type="button" variant="secondary" @click="cancel"
          >Annuler</BaseButton
        >
        <BaseButton
          type="submit"
          :disabled="referencesLoading"
          :loading="planning"
        >
          {{
            referencesLoading
              ? 'Chargement des référentiels…'
              : 'Planifier le programme'
          }}
        </BaseButton>
      </div>
    </form>
  </PageContainer>
</template>
