<script setup>
import {
  DialogContent,
  DialogDescription,
  DialogOverlay,
  DialogPortal,
  DialogRoot,
  DialogTitle,
} from 'reka-ui'

defineProps({
  open: Boolean,
  description: {
    type: String,
    default: 'Fenêtre de dialogue de l’application ChirOrg.',
  },
  size: { type: String, default: 'md' },
  closeLabel: { type: String, default: 'Fermer la fenêtre' },
})

const emit = defineEmits(['close'])

const sizes = {
  sm: 'w-[min(94vw,32rem)]',
  md: 'w-[min(94vw,44rem)]',
  wide: 'h-[min(94vh,70rem)] w-[min(96vw,90rem)]',
}

function handleOpenChange(open) {
  if (!open) emit('close')
}
</script>

<template>
  <DialogRoot :open="open" @update:open="handleOpenChange">
    <DialogPortal>
      <DialogOverlay
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/80 p-3 backdrop-blur-sm sm:p-5"
      />
      <DialogContent
        class="fixed left-1/2 top-1/2 z-50 flex max-h-[94vh] -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl focus:outline-none dark:border-gray-700 dark:bg-gray-900"
        :class="sizes[size] ?? sizes.md"
      >
        <header
          class="flex shrink-0 items-start justify-between gap-5 border-b border-gray-200 px-5 py-4 dark:border-gray-700 sm:px-7"
        >
          <div>
            <slot name="eyebrow" />
            <DialogTitle
              class="text-xl font-bold text-gray-950 dark:text-white sm:text-2xl"
            >
              <slot name="title" />
            </DialogTitle>
            <DialogDescription class="sr-only">
              {{ description }}
            </DialogDescription>
          </div>
          <button
            type="button"
            class="grid size-11 shrink-0 place-items-center rounded-xl border border-gray-300 text-2xl leading-none text-gray-700 transition hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-chirorg-500 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
            :aria-label="closeLabel"
            @click="emit('close')"
          >
            <span aria-hidden="true">×</span>
          </button>
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-7">
          <slot />
        </div>

        <footer
          v-if="$slots.footer"
          class="flex shrink-0 flex-col-reverse gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:justify-end sm:px-7 dark:border-gray-700"
        >
          <slot name="footer" />
        </footer>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
