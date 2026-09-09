<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import { programmeDetailRoute } from '../domain/programme.js'
import { adminApi } from '../services/admin.js'
import { useProgrammeStore } from '../stores/programme.js'

const router = useRouter()
const programmeStore = useProgrammeStore()
const referencesLoading = ref(true)
const referenceError = ref(null)
const specialites = ref([])
const chirurgiens = ref([])
const modeles = ref([])
const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10)
const form = reactive({
  specialite: '',
  chirurgienId: '',
  dateProgrammee: tomorrow,
  salle: 'Salle A',
  chirurgieModeleIds: [''],
})

const chirurgiensFiltres = computed(() =>
  chirurgiens.value.filter(
    (item) => String(item.specialite?.id) === String(form.specialite),
  ),
)
const modelesFiltres = computed(() =>
  modeles.value.filter(
    (item) => String(item.specialite?.id) === String(form.specialite),
  ),
)

watch(
  () => form.specialite,
  () => {
    form.chirurgienId = ''
    form.chirurgieModeleIds = ['']
  },
)

function addSurgery() {
  form.chirurgieModeleIds.push('')
}

function removeSurgery(index) {
  if (form.chirurgieModeleIds.length > 1)
    form.chirurgieModeleIds.splice(index, 1)
}

async function submit() {
  const ids = form.chirurgieModeleIds.map(Number)
  if (
    !form.chirurgienId ||
    !form.specialite ||
    !form.dateProgrammee ||
    !form.salle.trim() ||
    ids.some((id) => !id)
  ) {
    referenceError.value =
      'Tous les champs et chaque intervention sont obligatoires.'
    return
  }

  const programme = await programmeStore.create({
    chirurgienId: Number(form.chirurgienId),
    dateProgrammee: form.dateProgrammee,
    salle: form.salle.trim(),
    chirurgieModeleIds: ids,
  })
  if (programme) await router.push(programmeDetailRoute(programme))
}

onMounted(async () => {
  try {
    ;[specialites.value, chirurgiens.value, modeles.value] = await Promise.all([
      adminApi.list('specialites'),
      adminApi.list('chirurgiens'),
      adminApi.list('chirurgie-modeles'),
    ])
  } catch {
    referenceError.value = 'Impossible de charger les référentiels.'
  } finally {
    referencesLoading.value = false
  }
})
</script>

<template>
  <section class="page-section page-section--narrow">
    <header class="page-header">
      <div>
        <p class="eyebrow">Nouvelle planification</p>
        <h1>Planifier un programme</h1>
        <p>Une liste de matériel sera initialisée pour chaque intervention.</p>
      </div>
    </header>

    <AppLoading
      v-if="referencesLoading"
      message="Chargement des référentiels…"
    />
    <form v-else class="admin-form" @submit.prevent="submit">
      <AppAlert
        v-if="referenceError || programmeStore.error"
        :message="referenceError || programmeStore.error"
      />
      <div class="admin-form-grid">
        <label>
          Spécialité
          <select v-model="form.specialite" required>
            <option value="">Sélectionner</option>
            <option v-for="item in specialites" :key="item.id" :value="item.id">
              {{ item.intitule }}
            </option>
          </select>
        </label>
        <label>
          Chirurgien
          <select
            v-model="form.chirurgienId"
            required
            :disabled="!form.specialite"
          >
            <option value="">Sélectionner</option>
            <option
              v-for="item in chirurgiensFiltres"
              :key="item.id"
              :value="item.id"
            >
              Dr {{ item.prenom }} {{ item.nom }}
            </option>
          </select>
        </label>
        <label>
          Date
          <input
            v-model="form.dateProgrammee"
            type="date"
            :min="tomorrow"
            required
          />
        </label>
        <label>
          Salle
          <input v-model="form.salle" maxlength="50" required />
        </label>
      </div>

      <fieldset class="surgery-picker">
        <legend>Interventions dans l’ordre du programme</legend>
        <div
          v-for="(_, index) in form.chirurgieModeleIds"
          :key="index"
          class="surgery-picker__row"
        >
          <span>{{ index + 1 }}</span>
          <select
            v-model="form.chirurgieModeleIds[index]"
            :disabled="!form.specialite"
            required
          >
            <option value="">Sélectionner une intervention</option>
            <option
              v-for="item in modelesFiltres"
              :key="item.id"
              :value="item.id"
            >
              {{ item.intitule }}
            </option>
          </select>
          <button
            class="secondary-button"
            type="button"
            :disabled="form.chirurgieModeleIds.length === 1"
            @click="removeSurgery(index)"
          >
            Retirer
          </button>
        </div>
        <button class="text-link button-link" type="button" @click="addSurgery">
          + Ajouter une intervention
        </button>
      </fieldset>

      <div class="form-actions">
        <RouterLink class="secondary-button" :to="{ name: 'programmes' }">
          Annuler
        </RouterLink>
        <button
          class="primary-button"
          type="submit"
          :disabled="programmeStore.saving"
        >
          {{ programmeStore.saving ? 'Planification…' : 'Planifier' }}
        </button>
      </div>
    </form>
  </section>
</template>
