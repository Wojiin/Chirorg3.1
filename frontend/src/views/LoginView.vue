<script setup>
/** Vue publique de connexion : son script ne relie que l'affichage au composable dédié. */
import { useLoginView } from '@/composables/useLoginView'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'

const props = defineProps({
  redirect: { type: String, default: '' },
})

const { error, form, loading, submit } = useLoginView(props)
</script>

<template>
  <main class="login-page">
    <div class="login-container">
      <header class="login-brand">
        <div aria-hidden="true" class="login-logo">C</div>
        <h1 class="login-title">Chir<span>Org</span></h1>
        <p class="text-muted mt-3">Connexion à l’espace bloc opératoire</p>
      </header>

      <form class="login-panel" @submit.prevent="submit">
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
