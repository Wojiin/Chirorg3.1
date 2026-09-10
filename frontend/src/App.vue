<script setup>
/** Racine minimale de la SPA : le routeur décide seul de la vue et du shell à afficher. */
import { RouterView } from 'vue-router'
import { Toaster } from 'vue-sonner'
import PageMetadata from '@/components/ui/PageMetadata.vue'
import 'vue-sonner/style.css'
</script>

<template>
  <PageMetadata />
  <RouterView />
  <Toaster
    position="top-right"
    rich-colors
    close-button
    :toast-options="{
      classes: {
        toast: 'chirorg-toast',
        title: 'chirorg-toast-title',
        description: 'chirorg-toast-description',
      },
    }"
  />
</template>
