<script setup>
/** Résumé visuel partagé par les vues de validation partielle et finale. */
import { formatDate } from '@/utils/date'
import StatusBadge from '@/components/ui/StatusBadge.vue'

defineProps({
  surgery: { type: Object, required: true },
  status: { type: String, required: true },
  validatedAt: { type: String, default: '' },
})
</script>

<template>
  <!-- Délimite la section consacrée au résumé d'une chirurgie planifiée. -->
  <section class="final-overview">
    <!-- Regroupe les informations affichées par le résumé d'une chirurgie planifiée. -->
    <div class="final-overview-heading">
      <!-- Regroupe les informations affichées par le résumé d'une chirurgie planifiée. -->
      <div>
        <h2 class="page-title">{{ surgery.chirurgieModele.intitule }}</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
          Dr {{ surgery.chirurgien.prenom }} {{ surgery.chirurgien.nom }}
        </p>
        <StatusBadge class="mt-3" :status="status" />
      </div>
      <dl class="final-metadata">
        <!-- Regroupe les informations affichées par le résumé d'une chirurgie planifiée. -->
        <div>
          <dt>Salle</dt>
          <dd>{{ surgery.salle }}</dd>
        </div>
        <!-- Regroupe les informations affichées par le résumé d'une chirurgie planifiée. -->
        <div>
          <dt>Date</dt>
          <dd>{{ formatDate(surgery.date) }}</dd>
        </div>
        <!-- Regroupe les informations affichées par le résumé d'une chirurgie planifiée. -->
        <div v-if="validatedAt" class="col-span-2">
          <dt>Validée le</dt>
          <dd>{{ formatDate(validatedAt) }}</dd>
        </div>
      </dl>
    </div>
    <!-- Aligne les informations et les commandes du résumé d'une chirurgie planifiée. -->
    <div
      v-if="$slots.action"
      class="mt-5 flex justify-end border-t border-gray-100 pt-5 dark:border-gray-700"
    >
      <slot name="action" />
    </div>
  </section>
</template>
