<script setup>
/** Carte légère d'un programme : affiche son identité et compose la route de détail. */
import { computed } from 'vue'
import { formatDate } from '@/utils/date'
import ProgressBar from '@/components/ui/ProgressBar.vue'
import { getProgrammeDetailRoute } from '@/config/navigation'

const props = defineProps({
  programme: { type: Object, required: true },
})

const detailRoute = computed(() => getProgrammeDetailRoute(props.programme))

const formattedDate = computed(() => formatDate(props.programme.date))
const progress = computed(
  () =>
    props.programme.progressionPreparation ?? {
      total: 0,
      coches: 0,
      complete: false,
    },
)
</script>

<template>
  <!-- Présente le résumé d'un programme opératoire comme une unité autonome. -->
  <article class="programme-summary-card">
    <!-- Aligne les informations et les commandes du résumé d'un programme opératoire. -->
    <div class="min-w-0 flex-1">
      <h2 class="item-title">
        Dr {{ programme.chirurgien.nom }} {{ programme.chirurgien.prenom }}
      </h2>
      <dl class="programme-summary-data">
        <!-- Regroupe les informations affichées par le résumé d'un programme opératoire. -->
        <div>
          <dt>Date</dt>
          <dd>{{ formattedDate }}</dd>
        </div>
        <!-- Regroupe les informations affichées par le résumé d'un programme opératoire. -->
        <div>
          <dt>Salle</dt>
          <dd>{{ programme.salle }}</dd>
        </div>
        <!-- Regroupe les informations affichées par le résumé d'un programme opératoire. -->
        <div>
          <dt>Créé par</dt>
          <dd>{{ programme.creePar ?? 'Non renseigné' }}</dd>
        </div>
      </dl>
      <!-- Regroupe les informations affichées par le résumé d'un programme opératoire. -->
      <div class="mt-4">
        <ProgressBar
          :total="progress.total"
          :value="progress.traites ?? progress.coches"
          label="Avancement du programme"
        />
      </div>
    </div>
    <RouterLink :to="detailRoute" class="secondary-link">
      Voir le détail du programme
    </RouterLink>
  </article>
</template>
