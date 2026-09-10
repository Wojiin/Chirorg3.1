<script setup>
/** Navigation principale bureau alimentée par la configuration partagée. */
import { RouterLink } from 'vue-router'
import { useAppNavigation } from '@/composables/useAppNavigation'

const { links } = useAppNavigation()
</script>

<template>
  <aside class="app-sidebar">
    <div class="px-7 pb-7 pt-8">
      <RouterLink to="/programme" class="block rounded-md">
        <span
          class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white"
          >Chir<span class="text-chirorg-700 dark:text-chirorg-300"
            >Org</span
          ></span
        >
        <span class="mt-1 block text-xs text-gray-600 dark:text-gray-300"
          >Bloc opératoire</span
        >
      </RouterLink>
    </div>
    <nav aria-label="Navigation principale" class="flex-1 space-y-1 px-4">
      <RouterLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        class="sidebar-link group"
        active-class="bg-chirorg-50 !text-chirorg-700 dark:bg-gray-700 dark:!text-chirorg-500"
      >
        <span
          aria-hidden="true"
          class="grid size-7 place-items-center rounded-lg bg-gray-100 text-xs font-bold group-[.router-link-active]:bg-chirorg-100 dark:bg-gray-700 dark:group-[.router-link-active]:bg-gray-600"
        >
          {{ link.symbol }}
        </span>
        {{ link.label }}
      </RouterLink>
    </nav>
    <div
      class="m-4 rounded-xl bg-gray-50 p-4 text-xs leading-5 text-gray-500 dark:bg-gray-900/60 dark:text-gray-400"
    >
      Préparation sécurisée du matériel au bloc opératoire.
    </div>
  </aside>
</template>
