<script setup>
/** Variante mobile de la navigation principale alimentée par la configuration partagée. */
import { RouterLink } from 'vue-router'
import { useAppNavigation } from '@/composables/useAppNavigation'

const { links } = useAppNavigation()
</script>

<template>
  <nav aria-label="Navigation mobile" class="bottom-navigation">
    <div class="mx-auto flex h-17 max-w-xl items-stretch justify-around">
      <RouterLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        :aria-label="link.label"
        class="bottom-navigation-link"
        active-class="!text-chirorg-700 dark:!text-chirorg-300"
      >
        <span
          aria-hidden="true"
          class="grid size-6 place-items-center rounded-md text-xs font-bold"
          >{{ link.symbol }}</span
        >
        <span class="truncate">{{ link.shortLabel }}</span>
      </RouterLink>
    </div>
  </nav>
</template>
