<script setup>
/** Affiche les fiches techniques ordonnées d'une chirurgie validée. */
import { ref } from 'vue'
import { resolveApiAssetUrl } from '@/api/config'
import BaseModal from '@/components/ui/BaseModal.vue'

defineProps({
  sheets: { type: Array, default: () => [] },
})

const selectedSheet = ref(null)

function openSheet(sheet) {
  selectedSheet.value = sheet
}

function closeSheet() {
  selectedSheet.value = null
}
</script>

<template>
  <section aria-labelledby="technical-sheet-title">
    <h2 id="technical-sheet-title" class="section-title">Fiche technique</h2>
    <ol class="mt-4 grid gap-4 lg:grid-cols-3">
      <li v-for="sheet in sheets" :key="sheet.id">
        <button
          type="button"
          data-testid="technical-sheet-trigger"
          class="technical-card h-full w-full text-left transition hover:-translate-y-0.5 hover:border-chirorg-400 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-chirorg-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950"
          :aria-label="`Afficher la fiche technique ${sheet.titre} en grand`"
          aria-haspopup="dialog"
          @click="openSheet(sheet)"
        >
          <span
            class="block text-xs font-bold uppercase tracking-wider text-chirorg-700 dark:text-chirorg-500"
          >
            Étape {{ sheet.ordre }}
          </span>
          <span class="item-title mt-2 block">{{ sheet.titre }}</span>
          <span
            v-if="sheet.contenu"
            class="mt-3 block whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300"
          >
            {{ sheet.contenu }}
          </span>
          <img
            v-if="sheet.image"
            :src="resolveApiAssetUrl(sheet.image)"
            :alt="`Illustration technique de l’étape ${sheet.ordre} : ${sheet.titre}`"
            class="mt-4 max-h-80 w-full rounded-xl border border-gray-200 bg-white object-contain dark:border-gray-700 dark:bg-gray-900"
          />
          <span
            class="mt-4 block text-xs font-semibold text-chirorg-700 dark:text-chirorg-300"
          >
            Cliquer pour agrandir
          </span>
        </button>
      </li>
    </ol>

    <BaseModal
      :open="Boolean(selectedSheet)"
      title-id="technical-sheet-dialog-title"
      size="wide"
      close-label="Fermer la fiche technique"
      @close="closeSheet"
    >
      <template #eyebrow>
        <p
          class="text-xs font-bold uppercase tracking-wider text-chirorg-700 dark:text-chirorg-400"
        >
          Étape {{ selectedSheet?.ordre }}
        </p>
      </template>
      <template #title>{{ selectedSheet?.titre }}</template>

      <p
        v-if="selectedSheet?.contenu"
        class="whitespace-pre-line text-base leading-7 text-gray-700 dark:text-gray-200"
      >
        {{ selectedSheet.contenu }}
      </p>
      <img
        v-if="selectedSheet?.image"
        :src="resolveApiAssetUrl(selectedSheet.image)"
        :alt="`Illustration technique de l’étape ${selectedSheet.ordre} : ${selectedSheet.titre}`"
        class="mx-auto mt-5 max-h-[calc(94vh-12rem)] w-full rounded-xl bg-white object-contain dark:bg-gray-950"
        :class="{ 'mt-0': !selectedSheet.contenu }"
      />
    </BaseModal>
  </section>
</template>
