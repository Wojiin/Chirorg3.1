<script setup>
/** Vue de secours rendue lorsqu'aucune route protégée ne correspond à l'URL demandée. */
import PageContainer from '@/components/ui/PageContainer.vue'
</script>

<template>
  <PageContainer>
    <section
      class="surface-panel mx-auto max-w-2xl p-8 text-center shadow-card"
    >
      <p class="page-eyebrow">Erreur 404</p>
      <h2 class="section-title mt-2">Cette page n’existe pas</h2>
      <p class="page-description mt-3">
        L’adresse demandée est incorrecte ou l’écran a été déplacé.
      </p>
      <RouterLink to="/programme" class="primary-link mt-6">
        Revenir au programme opératoire
      </RouterLink>
    </section>
  </PageContainer>
</template>
