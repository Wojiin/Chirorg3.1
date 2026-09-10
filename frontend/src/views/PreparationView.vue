<script setup>
/** Vue de préparation : son script ne relie que l'affichage au composable dédié. */
import { usePreparationView } from '@/composables/usePreparationView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PreparationItem from '@/components/PreparationItem.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import ProgressBar from '@/components/ui/ProgressBar.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const {
  error,
  goBack,
  isPartial,
  isResolved,
  loading,
  preparation,
  progress,
  savingId,
  setMaterialState,
  surgery,
  validate,
} = usePreparationView(props)
</script>

<template>
  <PageContainer>
    <LoadingState
      v-if="loading && !preparation"
      label="Chargement de la checklist…"
    />
    <template v-else-if="surgery">
      <section class="preparation-overview">
        <div>
          <div class="flex flex-wrap items-center gap-3">
            <StatusBadge
              :status="
                surgery.valide
                  ? 'Validée'
                  : isPartial
                    ? 'Validation partielle'
                    : isResolved
                      ? 'Prêt à valider'
                      : 'En cours'
              "
            />
            <span class="text-muted"
              >{{ surgery.salle }} · {{ surgery.date }}</span
            >
          </div>
          <h2 class="page-title mt-4">
            {{ surgery.chirurgieModele.intitule }}
          </h2>
          <p class="mt-2 text-gray-600 dark:text-gray-300">
            Dr {{ surgery.chirurgien.prenom }} {{ surgery.chirurgien.nom }}
          </p>
        </div>
        <div class="preparation-progress">
          <ProgressBar
            :total="progress.total"
            :value="progress.traites ?? progress.coches"
            label="Progression de la préparation"
          />
        </div>
      </section>

      <ErrorMessage v-if="error" :message="error" />

      <div class="preparation-layout">
        <section aria-labelledby="materials-title">
          <h2 id="materials-title" class="section-title">
            Matériel à préparer
          </h2>
          <p class="page-description">
            Pour chaque élément, indiquez s’il est prêt ou absent.
          </p>
          <ul class="mt-4 space-y-3">
            <li v-for="item in preparation.preparations" :key="item.id">
              <PreparationItem
                :item="item"
                :disabled="Boolean(savingId) || surgery.valide"
                @set-state="setMaterialState"
              />
            </li>
          </ul>
        </section>

        <aside class="validation-panel">
          <h2 class="item-title">Validation</h2>
          <p class="text-muted mt-2 leading-6">
            La fiche technique sera disponible dans la vue finale après
            validation.
          </p>
          <p v-if="isPartial" class="feedback-error-soft mt-4">
            Le matériel absent reste modifiable. Marquez-le « Prêt » dès qu’il
            est disponible.
          </p>
          <p v-else-if="!isResolved" class="feedback-error-soft mt-4">
            Tout le matériel doit être déclaré prêt ou absent avant de valider.
          </p>
          <div class="mt-5 flex flex-col gap-3">
            <BaseButton
              class="w-full"
              size="lg"
              :disabled="!isResolved || surgery.valide || isPartial"
              :loading="loading"
              @click="validate"
            >
              {{
                surgery.valide
                  ? 'Chirurgie validée'
                  : isPartial
                    ? 'Validation partielle'
                    : 'Valider la chirurgie'
              }}
            </BaseButton>
            <BaseButton
              class="w-full"
              size="lg"
              variant="secondary"
              @click="goBack"
            >
              Retour
            </BaseButton>
          </div>
        </aside>
      </div>
    </template>
    <ErrorMessage v-else-if="error" :message="error" />
  </PageContainer>
</template>
