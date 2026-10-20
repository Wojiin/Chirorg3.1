<script setup>
/** Structure commune des routes authentifiées : navigation latérale, en-tête et vue enfant. */
import { RouterView } from 'vue-router'
import AppHeader from './AppHeader.vue'
import AppSidebar from './AppSidebar.vue'
import AppBottomNav from './AppBottomNav.vue'
</script>

<template>
  <!-- Regroupe les informations affichées par la structure générale de l'application. -->
  <div class="app-shell">
    <AppSidebar />
    <!-- Regroupe les informations affichées par la structure générale de l'application. -->
    <div class="min-h-screen lg:pl-56">
      <AppHeader />
      <!-- Présente le contenu principal de la structure générale de l'application. -->
      <main id="main-content" class="pb-24 lg:pb-8">
        <RouterView />
      </main>
    </div>
    <AppBottomNav />
  </div>
</template>
