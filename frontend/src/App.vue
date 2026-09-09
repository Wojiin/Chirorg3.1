<script setup>
import { RouterView, useRoute } from 'vue-router'

import AppLoading from './components/AppLoading.vue'
import AppShell from './components/AppShell.vue'
import { useAuthStore } from './stores/auth.js'

const auth = useAuthStore()
const route = useRoute()
</script>

<template>
  <AppLoading
    v-if="!auth.initialized"
    class="app-loading--screen"
    message="Session en cours…"
  />
  <AppShell v-else-if="route.meta.shell">
    <RouterView />
  </AppShell>
  <RouterView v-else />
</template>
