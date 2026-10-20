<script setup>
/** Présente un chargement dans une région de statut accessible. */
defineProps({ label: { type: String, default: 'Chargement en cours…' } })
</script>

<template>
  <!-- Regroupe les informations affichées par l'indicateur de chargement. -->
  <div role="status" class="loading-state">
    <span
      aria-hidden="true"
      class="loading-spinner size-5 border-chirorg-600"
    ></span>
    {{ label }}
  </div>
</template>
