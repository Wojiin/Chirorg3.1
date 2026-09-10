<script setup>
/** Vue finale : son script ne relie que l'affichage au composable dédié. */
import { useVueFinaleView } from '@/composables/useVueFinaleView'
import PageContainer from '@/components/ui/PageContainer.vue'
import SurgeryOverview from '@/components/SurgeryOverview.vue'
import TechnicalSheet from '@/components/TechnicalSheet.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const { error, goBack, loading, view } = useVueFinaleView(props)
</script>

<template>
  <PageContainer>
    <LoadingState v-if="loading" label="Chargement de la vue finale…" />
    <ErrorMessage v-else-if="error" :message="error" />
    <template v-else-if="view">
      <p class="feedback-info">
        Cette vue est en lecture seule une fois la préparation validée.
      </p>

      <SurgeryOverview
        :surgery="view.chirurgie"
        status="Validée"
        :validated-at="view.chirurgie.valideLe"
      >
        <template #action>
          <BaseButton variant="secondary" @click="goBack">Retour</BaseButton>
        </template>
      </SurgeryOverview>

      <section aria-labelledby="validated-materials-title">
        <h2 id="validated-materials-title" class="section-title">
          Matériel validé
        </h2>
        <ul class="validated-material-list">
          <li
            v-for="item in view.materiels"
            :key="item.id"
            class="validated-material"
          >
            <p class="item-title">{{ item.materiel.intitule }}</p>
            <p class="text-muted mt-1">
              {{ item.materiel.adresse }} · {{ item.materiel.type }}
            </p>
          </li>
        </ul>
      </section>

      <TechnicalSheet :sheets="view.fichesTechniques" />
    </template>
  </PageContainer>
</template>
