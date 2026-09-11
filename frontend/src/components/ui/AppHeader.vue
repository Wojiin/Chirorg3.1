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
          <svg
            v-if="isDark"
            aria-hidden="true"
            class="theme-toggle-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.25"
            stroke-linecap="round"
          >
            <circle cx="12" cy="12" r="4" />
            <path
              d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"
            />
          </svg>
          <svg
            v-else
            aria-hidden="true"
            class="theme-toggle-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.25"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
          </svg>
        </button>
        <button type="button" class="header-action" @click="logout">
          Déconnexion
        </button>
      </div>
    </div>
  </header>
</template>
