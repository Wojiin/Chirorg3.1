<script setup>
/** Présente un chargement dans une région de statut accessible. */
defineProps({ label: { type: String, default: 'Chargement en cours…' } })
</script>

<template>
  <div role="status" class="loading-state">
    <span
      aria-hidden="true"
      class="loading-spinner size-5 border-chirorg-600"
    ></span>
    {{ label }}
  </div>
</template>
