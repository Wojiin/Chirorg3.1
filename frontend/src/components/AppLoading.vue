<script setup>
defineProps({ message: { type: String, default: 'Chargement…' } })
</script>

<template>
  <div class="app-loading" role="status" aria-live="polite">
    <span class="app-loading__spinner" aria-hidden="true"></span>
    <span>{{ message }}</span>
  </div>
</template>
