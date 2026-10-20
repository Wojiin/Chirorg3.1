<script setup>
/** Vue publique de connexion : son script ne relie que l'affichage au composable dédié. */
import { useLoginView } from '@/composables/useLoginView'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({
  redirect: { type: String, default: '' },
})

const { error, form, loading, submit } = useLoginView(props)
const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <!-- Présente le contenu principal de la page consacrée à la session de l'utilisateur. -->
  <main class="login-page">
    <button
      type="button"
      class="header-action login-theme-toggle"
      :aria-label="
        isDark ? 'Activer le thème clair' : 'Activer le thème sombre'
      "
      @click="toggleTheme"
    >
      <span aria-hidden="true">{{ isDark ? '☀' : '☾' }}</span>
      {{ isDark ? 'Thème clair' : 'Thème sombre' }}
    </button>
    <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
    <div class="login-container">
      <!-- Regroupe le titre et les actions de la page consacrée à la session de l'utilisateur. -->
      <header class="login-brand">
        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div aria-hidden="true" class="login-logo">C</div>
        <h1 class="login-title">Chir<span>Org</span></h1>
        <p class="text-muted mt-3">Connexion à l’espace bloc opératoire</p>
      </header>

      <!-- Regroupe les champs et la soumission de la page consacrée à la session de l'utilisateur. -->
      <form class="login-panel" novalidate @submit.prevent="submit">
        <!-- Regroupe les informations affichées par la page consacrée à la session de l'utilisateur. -->
        <div>
          <h2 class="section-title">Connexion</h2>
          <p class="text-muted mt-1">
            Accédez au programme et aux préparations du jour.
          </p>
        </div>
        <ErrorMessage v-if="error" :message="error" />
        <BaseInput
          v-model="form.email"
          label="Email"
          type="email"
          autocomplete="username"
          placeholder="nom@exemple.fr"
          required
        />
        <BaseInput
          v-model="form.password"
          label="Mot de passe"
          type="password"
          autocomplete="current-password"
          placeholder="••••••••"
          required
        />
        <BaseButton type="submit" size="lg" class="w-full" :loading="loading">
          Se connecter
        </BaseButton>
      </form>
    </div>
  </main>
</template>
