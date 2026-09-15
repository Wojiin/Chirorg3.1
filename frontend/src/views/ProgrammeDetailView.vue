<script setup>
/** Vue détaillée d'un programme : son script ne relie que l'affichage au composable dédié. */
import { useProgrammeDetailView } from '@/composables/useProgrammeDetailView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import ProgrammeGroup from '@/components/ProgrammeGroup.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

const props = defineProps({
  date: { type: String, required: true },
  salle: { type: String, required: true },
  chirurgienId: { type: Number, required: true },
})

const {
  addSurgeryFormOpen,
  addingSurgery,
  chirurgieModeleId,
  displayedAddSurgeryError,
  referencesLoading,
  speciality,
  surgeryModels,
  openAddSurgeryForm,
  cancelAddSurgery,
  submitSurgery,
  deletingSurgeryId,
  pendingSurgeryRemoval,
  requestSurgeryRemoval,
  cancelSurgeryRemoval,
  confirmSurgeryRemoval,
  error,
  loading,
  programme,
  reorder,
  savingProgrammeId,
} = useProgrammeDetailView(props)
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Programme opératoire"
      title="Détail du programme"
      description="Consultez les chirurgies et modifiez leur ordre de passage."
    >
      <template #action>
        <div class="flex flex-wrap gap-3">
          <RouterLink :to="{ name: 'programme' }" class="secondary-link">
            Retour à la liste des programmes
          </RouterLink>
          <BaseButton
            v-if="programme"
            type="button"
            @click="openAddSurgeryForm"
          >
            + Ajouter une chirurgie
          </BaseButton>
        </div>
      </template>
    </PageHeading>

    <LoadingState
      v-if="loading && !programme"
      label="Chargement du programme…"
    />
    <ErrorMessage v-else-if="error" :message="error" />
    <template v-else-if="programme">
      <form
        v-if="addSurgeryFormOpen"
        class="form-panel mb-6"
        @submit.prevent="submitSurgery"
      >
        <div>
          <h2 class="section-title">Ajouter une chirurgie</h2>
          <p class="text-muted mt-2">
            Le nouvel élément sera ajouté à la fin du programme en cours.
          </p>
          <dl class="programme-summary-data">
            <div>
              <dt>Date</dt>
              <dd>{{ programme.date }}</dd>
            </div>
            <div>
              <dt>Salle</dt>
              <dd>{{ programme.salle }}</dd>
            </div>
            <div>
              <dt>Chirurgien</dt>
              <dd>
                Dr {{ programme.chirurgien.prenom }}
                {{ programme.chirurgien.nom }}
              </dd>
            </div>
            <div>
              <dt>Spécialité</dt>
              <dd>{{ speciality?.intitule ?? 'Chargement…' }}</dd>
            </div>
          </dl>
        </div>

        <ErrorMessage
          v-if="displayedAddSurgeryError"
          :message="displayedAddSurgeryError"
        />
        <BaseSelect
          v-model="chirurgieModeleId"
          label="Chirurgie modèle"
          :options="surgeryModels"
          :placeholder="
            referencesLoading
              ? 'Chargement des chirurgies modèles…'
              : 'Sélectionner une chirurgie modèle'
          "
          :disabled="referencesLoading || !speciality"
          required
        />

        <div class="form-actions">
          <BaseButton
            type="button"
            variant="secondary"
            @click="cancelAddSurgery"
          >
            Annuler
          </BaseButton>
          <BaseButton
            type="submit"
            :disabled="referencesLoading || !surgeryModels.length"
            :loading="addingSurgery"
          >
            Ajouter la chirurgie
          </BaseButton>
        </div>
      </form>

      <ProgrammeGroup
        :programme="programme"
        :saving="savingProgrammeId === programme.id"
        :deleting-id="deletingSurgeryId"
        @remove="requestSurgeryRemoval"
        @reorder="reorder"
      />
    </template>

    <ConfirmationModal
      :open="Boolean(pendingSurgeryRemoval)"
      variant="danger"
      title="Retirer cette chirurgie ?"
      :message="`La chirurgie « ${pendingSurgeryRemoval?.chirurgieModele?.intitule ?? ''} » sera définitivement supprimée du programme.`"
      confirm-label="Supprimer"
      :loading="deletingSurgeryId === pendingSurgeryRemoval?.id"
      @cancel="cancelSurgeryRemoval"
      @confirm="confirmSurgeryRemoval"
    />
  </PageContainer>
</template>
