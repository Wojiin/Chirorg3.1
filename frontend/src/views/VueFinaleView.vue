<script setup>
import { onMounted } from 'vue'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import { usePreparationStore } from '../stores/preparation.js'

const props = defineProps({ id: { type: Number, required: true } })
const preparationStore = usePreparationStore()

onMounted(() => preparationStore.fetchFinalView(props.id))
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Vue finale en lecture seule</p>
        <h1>
          {{ preparationStore.finalView?.chirurgie.chirurgieModele?.intitule }}
        </h1>
        <p v-if="preparationStore.finalView">
          Validée par
          {{ preparationStore.finalView.validePar?.email || 'un utilisateur' }}
        </p>
      </div>
      <span class="status-badge"><i></i> Intervention validée</span>
    </header>

    <AppAlert v-if="preparationStore.error" :message="preparationStore.error" />
    <AppLoading
      v-if="preparationStore.loading"
      message="Chargement de la synthèse…"
    />

    <div v-else-if="preparationStore.finalView" class="final-grid">
      <section class="final-card">
        <h2>Matériel validé</h2>
        <ul>
          <li
            v-for="item in preparationStore.finalView.materiels"
            :key="item.id"
          >
            <strong>{{ item.intitule }}</strong>
            <span>{{ item.typeMateriel }} · {{ item.adresse }}</span>
          </li>
        </ul>
      </section>
      <section class="final-card">
        <h2>Fiches techniques</h2>
        <article
          v-for="fiche in preparationStore.finalView.fichesTechniques"
          :key="fiche.id"
          class="technical-sheet"
        >
          <h3>{{ fiche.titre }}</h3>
          <p>{{ fiche.description }}</p>
          <a
            v-if="fiche.lienImage"
            :href="fiche.lienImage"
            target="_blank"
            rel="noreferrer"
          >
            Voir l’illustration
          </a>
        </article>
      </section>
    </div>
  </section>
</template>
