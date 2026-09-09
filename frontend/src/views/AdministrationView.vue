<script setup>
import { adminResources } from '../config/adminResources.js'
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Accès ROLE_ADMIN</p>
        <h1>Administration</h1>
        <p>
          Gérez les référentiels métier et les comptes utilisateurs de ChirOrg.
        </p>
      </div>
    </header>

    <div class="resource-grid">
      <RouterLink
        v-for="(resource, index) in adminResources"
        :key="resource.slug"
        class="resource-card"
        :to="{ name: 'admin-list', params: { resource: resource.slug } }"
      >
        <span>{{ String(index + 1).padStart(2, '0') }}</span>
        <h2>{{ resource.label }}</h2>
        <p>{{ resource.description }}</p>
        <strong>Gérer →</strong>
      </RouterLink>
    </div>
  </section>
</template>
