<script setup>
/** Associe les états métier affichés à une couleur de badge cohérente. */
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const classes = computed(() => {
  const status = props.status.toLowerCase()
  if (status.includes('partielle')) {
    return 'badge-warning'
  }
  if (
    status.includes('valid') ||
    status.includes('prêt') ||
    status.includes('complete')
  ) {
    return 'badge-success'
  }
  if (status.includes('cours') || status.includes('info')) {
    return 'badge-info'
  }
  if (status.includes('incomplet') || status.includes('préparer')) {
    return 'badge-warning'
  }
  if (status.includes('admin')) {
    return 'badge-admin'
  }
  return 'badge-neutral'
})
</script>

<template>
  <span class="status-badge" :class="classes">
    {{ status }}
  </span>
</template>
