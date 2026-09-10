<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import { resolvePageMetadata } from '@/config/pageMetadata'

/** Synchronise le titre et la description HTML avec la route affichée par la SPA. */
const route = useRoute()

watch(
  () => route.fullPath,
  () => {
    const metadata = resolvePageMetadata(route)
    document.title = metadata.title
    document
      .querySelector('meta[name="description"]')
      ?.setAttribute('content', metadata.description)
  },
  { immediate: true },
)
</script>

<template></template>
