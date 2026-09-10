<script setup>
/** Tableau d'administration : son script ne relie que l'affichage au composable dédié. */
import { useAdminDashboardView } from '@/composables/useAdminDashboardView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import AdminResourceCard from '@/components/AdminResourceCard.vue'

const { resources } = useAdminDashboardView()
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Gestion"
      title="Administration"
      description="Gérez les référentiels et les comptes utilisateurs de ChirOrg."
    />
    <section aria-labelledby="admin-resources-title">
      <h2 id="admin-resources-title" class="sr-only">
        Référentiels administrables
      </h2>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <AdminResourceCard
          v-for="resource in resources"
          :key="resource.slug"
          :resource="resource"
        />
      </div>
    </section>
  </PageContainer>
</template>
