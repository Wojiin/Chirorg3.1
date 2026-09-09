<script setup>
import { useAuthStore } from '../stores/auth.js'

const auth = useAuthStore()
</script>

<template>
  <main class="error-page">
    <div class="brand">
      <span class="brand__mark" aria-hidden="true">C</span>
      <span><strong>ChirOrg</strong><small>Bloc opératoire</small></span>
    </div>
    <section>
      <p class="error-code">403</p>
      <h1>Accès refusé</h1>
      <p>Votre rôle ne permet pas d’accéder à cette page.</p>
      <RouterLink
        class="primary-button"
        :to="auth.isAuthenticated ? '/' : '/connexion'"
      >
        {{
          auth.isAuthenticated ? 'Retour à l’accueil' : 'Revenir à la connexion'
        }}
      </RouterLink>
    </section>
  </main>
</template>
