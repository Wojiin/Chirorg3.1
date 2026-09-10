<script setup>
/** Structure commune des routes authentifiées : navigation latérale, en-tête et vue enfant. */
import { RouterView } from 'vue-router'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'
import AppBottomNav from './AppBottomNav.vue'
</script>

<template>
  <div class="app-shell">
    <AppSidebar />
    <div class="min-h-screen md:pl-56">
      <AppHeader />
      <main id="main-content" class="pb-24 md:pb-8">
        <RouterView />
      </main>
    </div>
    <AppBottomNav />
  </div>
</template>
