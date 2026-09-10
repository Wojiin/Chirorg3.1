<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  open: Boolean,
  titleId: { type: String, required: true },
  size: { type: String, default: 'md' },
  closeLabel: { type: String, default: 'Fermer la fenêtre' },
})

const emit = defineEmits(['close'])
const closeButton = ref(null)
const dialog = ref(null)
let triggerElement = null
let previousBodyOverflow = ''

const sizes = {
  sm: 'w-[min(94vw,32rem)]',
  md: 'w-[min(94vw,44rem)]',
  wide: 'h-[min(94vh,70rem)] w-[min(96vw,90rem)]',
}

function trapFocus(event) {
  const focusable = [
    ...(dialog.value?.querySelectorAll(
      'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    ) ?? []),
  ]
  if (!focusable.length) return

  const first = focusable[0]
  const last = focusable.at(-1)
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

watch(
  () => props.open,
  async (open) => {
    if (open) {
      triggerElement = document.activeElement
      previousBodyOverflow = document.body.style.overflow
      document.body.style.overflow = 'hidden'
      await nextTick()
      closeButton.value?.focus()
      return
    }

    document.body.style.overflow = previousBodyOverflow
    await nextTick()
    triggerElement?.focus?.()
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  document.body.style.overflow = previousBodyOverflow
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/80 p-3 backdrop-blur-sm sm:p-5"
      role="presentation"
      @click.self="emit('close')"
      @keydown.esc="emit('close')"
      @keydown.tab="trapFocus"
    >
      <section
        ref="dialog"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        class="flex max-h-[94vh] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
        :class="sizes[size] ?? sizes.md"
      >
        <header
          class="flex shrink-0 items-start justify-between gap-5 border-b border-gray-200 px-5 py-4 dark:border-gray-700 sm:px-7"
        >
          <div>
            <slot name="eyebrow" />
            <h2
              :id="titleId"
              class="text-xl font-bold text-gray-950 dark:text-white sm:text-2xl"
            >
              <slot name="title" />
            </h2>
          </div>
          <button
            ref="closeButton"
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
      </section>
    </div>
  </Teleport>
</template>
