<script setup>
/** Vue détaillée d'un programme : son script ne relie que l'affichage au composable dédié. */
import { useProgrammeDetailView } from '@/composables/useProgrammeDetailView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import ProgrammeGroup from '@/components/ProgrammeGroup.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

const props = defineProps({
  date: { type: String, required: true },
  salle: { type: String, required: true },
  chirurgienId: { type: Number, required: true },
})

const {
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
        <RouterLink :to="{ name: 'programme' }" class="secondary-link">
          Retour à la liste des programmes
        </RouterLink>
      </template>
    </PageHeading>

    <LoadingState
      v-if="loading && !programme"
      label="Chargement du programme…"
    />
    <ErrorMessage v-else-if="error" :message="error" />
    <ProgrammeGroup
      v-else-if="programme"
      :programme="programme"
      :saving="savingProgrammeId === programme.id"
      :deleting-id="deletingSurgeryId"
      @remove="requestSurgeryRemoval"
      @reorder="reorder"
    />

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
