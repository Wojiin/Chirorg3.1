<script setup>
/** Vue liste des programmes : son script ne relie que l'affichage au composable dédié. */
import { useProgrammeOperatoireView } from '@/composables/useProgrammeOperatoireView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import ProgrammeSummaryCard from '@/components/ProgrammeSummaryCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'

const {
  clearFilters,
  error,
  filteredProgrammes,
  filters,
  loading,
  loadProgrammes,
  rooms,
} = useProgrammeOperatoireView()
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Vue d’ensemble"
      title="Programme opératoire"
      description="Retrouvez les programmes par chirurgien, date et salle."
    >
      <template #action>
        <RouterLink :to="{ name: 'planification' }" class="primary-link">
          + Planifier un programme
        </RouterLink>
      </template>
    </PageHeading>

    <form
      aria-label="Filtrer les programmes"
      class="programme-filters"
      @submit.prevent="loadProgrammes"
    >
      <BaseInput v-model="filters.date" label="Date" type="date" />
      <BaseSelect
        v-model="filters.room"
        label="Salle"
        placeholder="Toutes les salles"
        :options="rooms"
        allow-empty
      />
      <BaseButton
        v-if="filters.date || filters.room"
        type="button"
        variant="ghost"
        size="sm"
        class="justify-self-start sm:col-span-2"
        @click="clearFilters"
      >
        Effacer les filtres
      </BaseButton>
    </form>

    <ErrorMessage v-if="error" :message="error" />
    <LoadingState v-if="loading" label="Chargement du programme opératoire…" />
    <EmptyState
      v-else-if="!filteredProgrammes.length"
      title="Aucun programme planifié"
      message="Modifiez les filtres ou planifiez un nouveau programme."
    />
    <section v-else aria-label="Programmes opératoires">
      <ol class="programme-summary-list">
        <li v-for="programme in filteredProgrammes" :key="programme.id">
          <ProgrammeSummaryCard :programme="programme" />
        </li>
      </ol>
    </section>
  </PageContainer>
</template>
