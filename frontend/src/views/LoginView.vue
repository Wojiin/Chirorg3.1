<script setup>
import { onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppAlert from '../components/AppAlert.vue'
import { useAuthStore } from '../stores/auth.js'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const credentials = reactive({ email: '', password: '' })

onMounted(() => auth.clearError())

function safeRedirect() {
  const redirect = route.query.redirect
  return typeof redirect === 'string' &&
    redirect.startsWith('/') &&
    !redirect.startsWith('//')
    ? redirect
    : '/'
}

async function submit() {
  try {
    await auth.login(credentials)
    await router.replace(safeRedirect())
  } catch {
    // The store exposes the normalized API error to the form.
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-intro">
      <div class="brand brand--light">
        <span class="brand__mark" aria-hidden="true">C</span>
        <span
          ><strong>ChirOrg</strong
          ><small>Organisation du bloc opératoire</small></span
        >
      </div>
      <div class="auth-intro__copy">
        <p class="eyebrow">Coordonner · Préparer · Sécuriser</p>
        <h1>Le programme opératoire, lisible par toute l’équipe.</h1>
        <p>
          Centralisez les interventions et suivez la préparation du matériel
          depuis un espace sécurisé.
        </p>
      </div>
    </section>

    <section class="auth-panel" aria-labelledby="login-title">
      <form class="login-card" @submit.prevent="submit">
        <header>
          <p class="eyebrow">Accès professionnel</p>
          <h2 id="login-title">Connexion</h2>
          <p>Utilisez le compte attribué par votre administrateur.</p>
        </header>

        <AppAlert v-if="auth.error" :message="auth.error" />

        <label>
          Adresse email
          <input
            v-model.trim="credentials.email"
            name="email"
            type="email"
            autocomplete="username"
            required
          />
        </label>

        <label>
          Mot de passe
          <input
            v-model="credentials.password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
          />
        </label>

        <button class="primary-button" type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Connexion en cours…' : 'Se connecter' }}
        </button>
      </form>
    </section>
  </main>
</template>
