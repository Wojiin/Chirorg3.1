<script setup>
/** En-tête de page homogène avec une zone d'action facultative. */
defineProps({
  eyebrow: { type: String, required: true },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <header class="page-heading">
    <div>
      <p class="page-eyebrow">{{ eyebrow }}</p>
      <p class="page-title">{{ title }}</p>
      <p v-if="description" class="page-description">{{ description }}</p>
    </div>
    <slot name="action" />
  </header>
</template>
