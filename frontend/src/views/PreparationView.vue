<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import { usePreparationStore } from '../stores/preparation.js'

const props = defineProps({ id: { type: Number, required: true } })
const router = useRouter()
const preparationStore = usePreparationStore()
const surgery = computed(() => preparationStore.preparation?.chirurgie)
const rows = computed(() => preparationStore.preparation?.preparations ?? [])
const progress = computed(
  () =>
    preparationStore.preparation?.progressionPreparation ?? {
      total: 0,
      traites: 0,
    },
)

onMounted(async () => {
  const loaded = await preparationStore.fetch(props.id)
  if (loaded?.chirurgie.valide) {
    await router.replace({ name: 'vue-finale', params: { id: props.id } })
  }
})

async function validate() {
  const result = await preparationStore.validate()
  if (result === 'final') {
    await router.push({ name: 'vue-finale', params: { id: props.id } })
  }
}
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Checklist matériel</p>
        <h1>{{ surgery?.chirurgieModele?.intitule || 'Préparation' }}</h1>
        <p v-if="surgery">
          {{ surgery.dateProgrammee }} · {{ surgery.salle }} · Dr
          {{ surgery.chirurgien.prenom }} {{ surgery.chirurgien.nom }}
        </p>
      </div>
      <span class="status-badge">
        {{ progress.traites }}/{{ progress.total }} traité(s)
      </span>
    </header>

    <AppAlert v-if="preparationStore.error" :message="preparationStore.error" />
    <AppLoading
      v-if="preparationStore.loading"
      message="Chargement de la préparation…"
    />

    <div v-else-if="rows.length" class="material-list">
      <article
        v-for="item in rows"
        :key="item.id"
        :class="[
          'material-row',
          {
            'material-row--ready': item.coche,
            'material-row--missing': item.absent,
          },
        ]"
      >
        <div>
          <h2>{{ item.materiel.intitule }}</h2>
          <p>
            {{ item.materiel.typeMateriel || 'Matériel' }} ·
            {{ item.materiel.adresse || 'Adresse non renseignée' }}
          </p>
        </div>
        <div class="material-actions">
          <button
            class="secondary-button"
            type="button"
            :disabled="preparationStore.savingId === item.id"
            @click="preparationStore.setState(item, 'ready')"
          >
            {{ item.coche ? 'Annuler prêt' : 'Marquer prêt' }}
          </button>
          <button
            class="secondary-button"
            type="button"
            :disabled="preparationStore.savingId === item.id"
            @click="preparationStore.setState(item, 'absent')"
          >
            {{ item.absent ? 'Annuler absent' : 'Signaler absent' }}
          </button>
        </div>
      </article>

      <div v-if="progress.absents" class="partial-notice">
        {{ progress.absents }} matériel(s) absent(s) : la validation restera
        partielle tant qu’ils ne seront pas disponibles.
      </div>
      <div
        v-if="surgery?.etatValidation === 'VALIDATION_PARTIELLE'"
        class="partial-notice"
      >
        Validation partielle enregistrée. Remplacez les matériels absents puis
        validez à nouveau pour clôturer l’intervention.
      </div>

      <div class="form-actions">
        <button class="secondary-button" type="button" @click="router.back()">
          Retour
        </button>
        <button
          class="primary-button"
          type="button"
          :disabled="!preparationStore.isResolved || preparationStore.loading"
          @click="validate"
        >
          Valider la préparation
        </button>
      </div>
    </div>

    <div v-else-if="!preparationStore.loading" class="empty-state">
      <span>Checklist vide</span>
      <h2>Aucun matériel n’est associé à cette intervention.</h2>
    </div>
  </section>
</template>
