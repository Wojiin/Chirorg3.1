<script setup>
/** Pagination accessible conservant l’identité visuelle ChirOrg. */
import {
  PaginationEllipsis,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
  PaginationRoot,
} from 'reka-ui'

defineProps({
  total: { type: Number, required: true },
  itemsPerPage: { type: Number, default: 10 },
  label: { type: String, default: 'Pagination des résultats' },
})
const page = defineModel('page', { type: Number, required: true })
</script>

<template>
  <PaginationRoot
    v-if="total > itemsPerPage"
    v-model:page="page"
    :total="total"
    :items-per-page="itemsPerPage"
    :sibling-count="1"
    show-edges
    :aria-label="label"
    class="mt-6"
  >
    <PaginationList
      v-slot="{ items }"
      class="flex flex-wrap justify-center gap-2"
    >
      <PaginationPrev
        class="pagination-button px-3 hover:border-chirorg-400 hover:text-chirorg-800 data-[selected]:border-chirorg-700 data-[selected]:bg-chirorg-700 data-[selected]:text-white dark:data-[selected]:border-chirorg-500 dark:data-[selected]:bg-chirorg-700"
      >
        Précédent
      </PaginationPrev>
      <template v-for="(item, index) in items" :key="index">
        <PaginationListItem
          v-if="item.type === 'page'"
          :value="item.value"
          class="pagination-button hover:border-chirorg-400 hover:text-chirorg-800 data-[selected]:border-chirorg-700 data-[selected]:bg-chirorg-700 data-[selected]:text-white dark:data-[selected]:border-chirorg-500 dark:data-[selected]:bg-chirorg-700"
        >
          {{ item.value }}
        </PaginationListItem>
        <PaginationEllipsis v-else :index="index" class="px-2 py-2">
          <span aria-hidden="true">…</span>
          <span class="sr-only">Pages intermédiaires</span>
        </PaginationEllipsis>
      </template>
      <PaginationNext
        class="pagination-button px-3 hover:border-chirorg-400 hover:text-chirorg-800 data-[selected]:border-chirorg-700 data-[selected]:bg-chirorg-700 data-[selected]:text-white dark:data-[selected]:border-chirorg-500 dark:data-[selected]:bg-chirorg-700"
      >
        Suivant
      </PaginationNext>
    </PaginationList>
  </PaginationRoot>
</template>
