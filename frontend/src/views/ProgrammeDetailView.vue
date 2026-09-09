<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import { useProgrammeStore } from '../stores/programme.js'

const props = defineProps({
  date: { type: String, required: true },
  salle: { type: String, required: true },
  chirurgien: { type: Number, required: true },
})
const router = useRouter()
const programmeStore = useProgrammeStore()
const programme = computed(() => programmeStore.selected)

onMounted(() =>
  programmeStore.fetchOne({
    date: props.date,
    salle: props.salle,
    chirurgien: props.chirurgien,
  }),
)

async function move(index, offset) {
  if (!programme.value) return
  const target = index + offset
  if (target < 0 || target >= programme.value.chirurgies.length) return
  const ids = programme.value.chirurgies.map((item) => item.id)
  ;[ids[index], ids[target]] = [ids[target], ids[index]]
  await programmeStore.reorder(ids)
}

async function remove(chirurgie) {
  if (
    chirurgie.valide ||
    !window.confirm(
      `Supprimer « ${chirurgie.chirurgieModele.intitule} » du programme ?`,
    )
  ) {
    return
  }
  const removed = await programmeStore.removeSurgery(chirurgie.id)
  if (removed && programme.value?.chirurgies.length === 0) {
    await router.push({ name: 'programmes' })
  }
}
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Programme du {{ date }}</p>
        <h1>{{ salle }}</h1>
        <p v-if="programme">
          Dr {{ programme.chirurgien.prenom }} {{ programme.chirurgien.nom }} ·
          {{ programme.nombreChirurgies }} intervention(s)
        </p>
      </div>
      <RouterLink class="secondary-button" :to="{ name: 'programmes' }">
        Retour aux programmes
      </RouterLink>
    </header>

    <AppAlert v-if="programmeStore.error" :message="programmeStore.error" />
    <AppLoading
      v-if="programmeStore.loading"
      message="Chargement du programme…"
    />

    <ol v-else-if="programme" class="surgery-list">
      <li
        v-for="(chirurgie, index) in programme.chirurgies"
        :key="chirurgie.id"
      >
        <span class="surgery-order">{{ chirurgie.ordre ?? index + 1 }}</span>
        <div class="surgery-copy">
          <h2>{{ chirurgie.chirurgieModele.intitule }}</h2>
          <p>
            Matériel traité : {{ chirurgie.progressionPreparation.traites }}/{{
              chirurgie.progressionPreparation.total
            }}
          </p>
          <span
            class="status-badge"
            :class="{ 'status-badge--warning': !chirurgie.valide }"
          >
            {{ chirurgie.valide ? 'Validée' : chirurgie.etatValidation }}
          </span>
        </div>
        <div class="surgery-actions">
          <button
            class="secondary-button"
            type="button"
            :disabled="index === 0 || programmeStore.saving || chirurgie.valide"
            aria-label="Monter"
            @click="move(index, -1)"
          >
            ↑
          </button>
          <button
            class="secondary-button"
            type="button"
            :disabled="
              index === programme.chirurgies.length - 1 ||
              programmeStore.saving ||
              chirurgie.valide
            "
            aria-label="Descendre"
            @click="move(index, 1)"
          >
            ↓
          </button>
          <RouterLink
            class="primary-button"
            :to="
              chirurgie.valide
                ? { name: 'vue-finale', params: { id: chirurgie.id } }
                : { name: 'preparation', params: { id: chirurgie.id } }
            "
          >
            {{ chirurgie.valide ? 'Consulter' : 'Préparer' }}
          </RouterLink>
          <button
            v-if="!chirurgie.valide"
            class="danger-button"
            type="button"
            :disabled="programmeStore.saving"
            @click="remove(chirurgie)"
          >
            Supprimer
          </button>
        </div>
      </li>
    </ol>
  </section>
</template>
