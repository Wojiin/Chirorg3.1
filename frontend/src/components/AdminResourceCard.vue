<script setup>
/** Carte de navigation vers un référentiel administratif déclaré dans la configuration. */
import { RouterLink } from 'vue-router'

defineProps({
  resource: { type: Object, required: true },
})
</script>

<template>
  <RouterLink :to="`/admin/${resource.slug}`" class="resource-card group">
    <div>
      <h3
        class="font-bold text-gray-950 group-hover:text-chirorg-700 dark:text-white dark:group-hover:text-chirorg-500"
      >
        {{ resource.label }}
      </h3>
      <p class="text-muted mt-1">{{ resource.description }}</p>
    </div>
    <span
      aria-hidden="true"
      class="text-2xl text-gray-500 transition group-hover:translate-x-1 group-hover:text-chirorg-700 dark:text-gray-400 dark:group-hover:text-chirorg-300"
      >›</span
    >
  </RouterLink>
</template>
