<script setup>
import { useAuthStore } from '../stores/auth.js'

const auth = useAuthStore()
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Vue d’ensemble</p>
        <h1>Bonjour</h1>
        <p>Votre session est active pour {{ auth.profile?.email }}.</p>
      </div>
      <span class="status-badge"><i></i> API connectée</span>
    </header>

    <div class="dashboard-grid">
      <article class="feature-card feature-card--accent">
        <span class="feature-card__index">01</span>
        <div>
          <h2>Programmes opératoires</h2>
          <p>
            La consultation et la planification constitueront la prochaine
            tranche métier.
          </p>
        </div>
        <span class="feature-card__state">À venir</span>
      </article>

      <article class="feature-card">
        <span class="feature-card__index">02</span>
        <div>
          <h2>Préparation du matériel</h2>
          <p>
            Suivi de disponibilité et validation sécurisée avant intervention.
          </p>
        </div>
        <span class="feature-card__state">À venir</span>
      </article>

      <article v-if="auth.isAdmin" class="feature-card">
        <span class="feature-card__index">03</span>
        <div>
          <h2>Administration</h2>
          <p>
            Votre rôle autorise la gestion des utilisateurs et des référentiels.
          </p>
        </div>
        <RouterLink class="text-link" :to="{ name: 'administration' }"
          >Ouvrir</RouterLink
        >
      </article>
    </div>
  </section>
</template>
