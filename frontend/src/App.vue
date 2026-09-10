<script setup>
/** Racine minimale de la SPA : le routeur décide seul de la vue et du shell à afficher. */
import { RouterView } from 'vue-router'
import PageMetadata from '@/components/ui/PageMetadata.vue'
</script>

<template>
  <PageMetadata />
  <RouterView />
</template>
