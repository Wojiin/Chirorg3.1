<script setup>
/** Vue de validation partielle : son script ne relie que l'affichage au composable dédié. */
import { useValidationPartielleView } from '@/composables/useValidationPartielleView'
import PageContainer from '@/components/ui/PageContainer.vue'
import SurgeryOverview from '@/components/SurgeryOverview.vue'
import TechnicalSheet from '@/components/TechnicalSheet.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const {
  absentMaterials,
  error,
  goBack,
  loading,
  markReady,
  preparation,
  savingId,
  surgery,
  technicalSheets,
  technicalSheetsError,
  technicalSheetsLoading,
} = useValidationPartielleView(props)
</script>

<template>
  <PageContainer>
    <LoadingState
      v-if="loading && !preparation"
      label="Chargement de la validation partielle…"
    />
    <ErrorMessage v-else-if="error && !preparation" :message="error" />
    <template v-else-if="surgery">
      <p class="feedback-error-soft">
        La chirurgie reste modifiable tant que du matériel est absent.
      </p>

      <SurgeryOverview :surgery="surgery" status="Validation partielle">
        <template #action>
          <BaseButton variant="secondary" @click="goBack">Retour</BaseButton>
        </template>
      </SurgeryOverview>

      <ErrorMessage v-if="error" :message="error" />

      <section aria-labelledby="absent-materials-title">
        <h2 id="absent-materials-title" class="section-title">
          Matériel absent
        </h2>
        <p class="page-description">
          Cliquez sur un matériel dès qu’il est disponible pour le passer à «
          Prêt ».
        </p>
        <ul class="absent-material-list">
          <li v-for="item in absentMaterials" :key="item.id">
            <button
              type="button"
              class="absent-material-action"
              :disabled="Boolean(savingId) || loading"
              @click="markReady(item)"
            >
              <span>
                <span class="item-title block">{{
                  item.materiel.intitule
                }}</span>
                <span class="mt-1 block text-sm">
                  {{ item.materiel.adresse }} · {{ item.materiel.type }}
                </span>
              </span>
              <span class="absent-material-ready-label">Passer à « Prêt »</span>
            </button>
          </li>
        </ul>
      </section>

      <LoadingState
        v-if="technicalSheetsLoading"
        label="Chargement des fiches techniques…"
      />
      <ErrorMessage
        v-else-if="technicalSheetsError"
        :message="technicalSheetsError"
      />
      <TechnicalSheet v-else :sheets="technicalSheets" />
    </template>
  </PageContainer>
</template>
