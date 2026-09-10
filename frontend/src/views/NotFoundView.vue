<script setup>
/** Vue de secours rendue lorsqu'aucune route protégée ne correspond à l'URL demandée. */
import PageContainer from '@/components/ui/PageContainer.vue'
import { ERROR_MESSAGES } from '@/config/errorMessages'
</script>

<template>
  <PageContainer>
    <section
      class="surface-panel mx-auto max-w-2xl p-8 text-center shadow-card"
    >
      <p class="page-eyebrow">{{ ERROR_MESSAGES.notFoundCode }}</p>
      <h2 class="section-title mt-2">{{ ERROR_MESSAGES.notFoundTitle }}</h2>
      <p class="page-description mt-3">
        {{ ERROR_MESSAGES.notFoundDescription }}
      </p>
      <RouterLink to="/programme" class="primary-link mt-6">
        Revenir au programme opératoire
      </RouterLink>
    </section>
  </PageContainer>
</template>
