<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

import { apiErrorMessage } from '../api/problem.js'
import { useAuthStore } from '../stores/auth.js'
import AppAlert from './AppAlert.vue'

const auth = useAuthStore()
const router = useRouter()
const logoutError = ref(null)

const initials = computed(
  () => auth.profile?.email?.slice(0, 2).toUpperCase() || 'CH',
)

async function logout() {
  logoutError.value = null
  try {
    await auth.logout()
  } catch (error) {
    logoutError.value = apiErrorMessage(
      error,
      'La déconnexion distante a échoué.',
    )
  } finally {
    await router.replace({ name: 'connexion' })
  }
}
</script>

<template>
  <div class="app-layout">
    <aside class="sidebar" aria-label="Navigation principale">
      <RouterLink class="brand" :to="{ name: 'accueil' }">
        <span class="brand__mark" aria-hidden="true">C</span>
        <span><strong>ChirOrg</strong><small>Bloc opératoire</small></span>
      </RouterLink>

      <nav class="navigation">
        <p class="navigation__label">Espace de travail</p>
        <RouterLink :to="{ name: 'accueil' }">Accueil</RouterLink>
        <RouterLink v-if="auth.isAdmin" :to="{ name: 'administration' }">
          Administration
        </RouterLink>
        <button
          class="mobile-logout"
          type="button"
          :disabled="auth.loading"
          @click="logout"
        >
          Quitter
        </button>
      </nav>

      <div class="sidebar__profile">
        <span class="avatar" aria-hidden="true">{{ initials }}</span>
        <span class="profile-copy">
          <strong>{{ auth.profile?.email }}</strong>
          <small>{{ auth.isAdmin ? 'Administrateur' : 'Utilisateur' }}</small>
        </span>
        <button
          class="logout-button"
          type="button"
          :disabled="auth.loading"
          @click="logout"
        >
          {{ auth.loading ? 'Déconnexion…' : 'Se déconnecter' }}
        </button>
      </div>
    </aside>

    <main class="main-content">
      <AppAlert v-if="logoutError" :message="logoutError" />
      <slot />
    </main>
  </div>
</template>
