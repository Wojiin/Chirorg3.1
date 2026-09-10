<script setup>
/** En-tête du shell protégé relié à son composable d'orchestration. */
import { useAppHeader } from '@/composables/useAppHeader'
import { useTheme } from '@/composables/useTheme'

const { displayName, isAdmin, logout, title } = useAppHeader()
const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <header class="app-header">
    <div
      class="flex h-full items-center justify-between gap-4 px-5 sm:px-7 lg:px-10"
    >
      <div class="min-w-0">
        <p
          class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-chirorg-700 lg:hidden dark:text-chirorg-300"
        >
          ChirOrg
        </p>
        <h1
          class="truncate text-lg font-semibold text-gray-950 dark:text-white"
        >
          {{ title }}
        </h1>
      </div>
      <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
          <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ displayName }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ isAdmin ? 'Administrateur' : 'Utilisateur' }}
          </p>
        </div>
        <button
          type="button"
          class="header-action theme-toggle"
          :aria-label="
            isDark ? 'Activer le thème clair' : 'Activer le thème sombre'
          "
          :title="isDark ? 'Thème clair' : 'Thème sombre'"
          @click="toggleTheme"
        >
          <span aria-hidden="true">{{ isDark ? '☀' : '☾' }}</span>
          <span class="hidden md:inline">{{
            isDark ? 'Clair' : 'Sombre'
          }}</span>
        </button>
        <button type="button" class="header-action" @click="logout">
          Déconnexion
        </button>
      </div>
    </div>
  </header>
</template>
