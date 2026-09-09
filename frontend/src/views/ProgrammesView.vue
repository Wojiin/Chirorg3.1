<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import { programmeDetailRoute } from '../domain/programme.js'
import { adminApi } from '../services/admin.js'
import { useProgrammeStore } from '../stores/programme.js'

const programmeStore = useProgrammeStore()
const filters = reactive({ date: '', specialite: '', chirurgien: '' })
const specialites = ref([])
const chirurgiens = ref([])
let timer

const chirurgiensFiltres = computed(() =>
  chirurgiens.value.filter(
    (item) =>
      !filters.specialite ||
      String(item.specialite?.id) === String(filters.specialite),
  ),
)
const programmesFiltres = computed(() => {
  if (!filters.specialite || filters.chirurgien)
    return programmeStore.programmes
  const ids = new Set(chirurgiensFiltres.value.map((item) => Number(item.id)))
  return programmeStore.programmes.filter((item) =>
    ids.has(Number(item.chirurgien.id)),
  )
})

async function load() {
  await programmeStore.fetch({
    date: filters.date,
    salle: '',
    chirurgien: filters.chirurgien,
  })
}

watch(
  filters,
  () => {
    window.clearTimeout(timer)
    timer = window.setTimeout(load, 200)
  },
  { deep: true },
)

watch(
  () => filters.specialite,
  () => {
    if (
      !chirurgiensFiltres.value.some(
        (item) => String(item.id) === String(filters.chirurgien),
      )
    ) {
      filters.chirurgien = ''
    }
  },
)

onMounted(async () => {
  const [loadedSpecialites, loadedChirurgiens] = await Promise.all([
    adminApi.list('specialites'),
    adminApi.list('chirurgiens'),
  ])
  specialites.value = loadedSpecialites
  chirurgiens.value = loadedChirurgiens
  await load()
})
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Activité du bloc</p>
        <h1>Programmes opératoires</h1>
        <p>Les interventions sont regroupées par date, salle et chirurgien.</p>
      </div>
      <RouterLink class="primary-button" :to="{ name: 'planification' }">
        Planifier un programme
      </RouterLink>
    </header>

    <form class="programme-filters" @submit.prevent="load">
      <label>
        Date
        <input v-model="filters.date" type="date" />
      </label>
      <label>
        Spécialité
        <select v-model="filters.specialite">
          <option value="">Toutes</option>
          <option v-for="item in specialites" :key="item.id" :value="item.id">
            {{ item.intitule }}
          </option>
        </select>
      </label>
      <label>
        Chirurgien
        <select v-model="filters.chirurgien">
          <option value="">Tous</option>
          <option
            v-for="item in chirurgiensFiltres"
            :key="item.id"
            :value="item.id"
          >
            Dr {{ item.prenom }} {{ item.nom }}
          </option>
        </select>
      </label>
    </form>

    <AppAlert v-if="programmeStore.error" :message="programmeStore.error" />
    <AppLoading
      v-if="programmeStore.loading"
      message="Chargement des programmes…"
    />

    <div v-else-if="programmesFiltres.length" class="programme-grid">
      <article
        v-for="programme in programmesFiltres"
        :key="programme.id"
        class="programme-card"
      >
        <div>
          <p class="eyebrow">{{ programme.date }} · {{ programme.salle }}</p>
          <h2>
            Dr {{ programme.chirurgien.prenom }} {{ programme.chirurgien.nom }}
          </h2>
        </div>
        <dl class="programme-metrics">
          <div>
            <dt>Interventions</dt>
            <dd>{{ programme.nombreChirurgies }}</dd>
          </div>
          <div>
            <dt>Validées</dt>
            <dd>{{ programme.nombreChirurgiesValidees }}</dd>
          </div>
          <div>
            <dt>Matériel traité</dt>
            <dd>
              {{ programme.progressionPreparation?.traites ?? 0 }}/{{
                programme.progressionPreparation?.total ?? 0
              }}
            </dd>
          </div>
        </dl>
        <RouterLink class="text-link" :to="programmeDetailRoute(programme)">
          Ouvrir le programme
        </RouterLink>
      </article>
    </div>

    <div v-else-if="!programmeStore.loading" class="empty-state">
      <span>Aucun résultat</span>
      <h2>Aucun programme ne correspond aux filtres.</h2>
      <p>Planifiez une intervention ou modifiez vos critères.</p>
    </div>
  </section>
</template>
