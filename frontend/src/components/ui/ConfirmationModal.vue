<script setup>
import { computed } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'

const props = defineProps({
  open: Boolean,
  title: { type: String, required: true },
  message: { type: String, required: true },
  confirmLabel: { type: String, default: 'Confirmer' },
  cancelLabel: { type: String, default: 'Annuler' },
  variant: { type: String, default: 'info' },
  loading: Boolean,
})

const emit = defineEmits(['cancel', 'confirm'])
const buttonVariant = computed(
  () =>
    ({
      danger: 'danger',
      success: 'success',
      warning: 'warning',
    })[props.variant] ?? 'primary',
)

function cancel() {
  if (!props.loading) emit('cancel')
}
</script>

<template>
  <BaseModal
    :open="open"
    :description="message"
    size="sm"
    close-label="Annuler et fermer"
    @close="cancel"
  >
    <template #eyebrow>
      <p
        class="mb-1 text-xs font-bold uppercase tracking-wider"
        :class="`confirmation-text-${variant}`"
      >
        Confirmation requise
      </p>
    </template>
    <template #title>{{ title }}</template>

    <div class="confirmation-content" :class="`confirmation-${variant}`">
      <span class="confirmation-icon" aria-hidden="true">
        <svg
          v-if="variant === 'danger'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5" />
        </svg>
        <svg
          v-else-if="variant === 'success'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="m5 12 4 4L19 6" />
        </svg>
        <svg
          v-else-if="variant === 'warning'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M12 3 2.5 20h19L12 3Z" />
          <path d="M12 9v4M12 17h.01" />
        </svg>
        <svg
          v-else
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="12" cy="12" r="9" />
          <path d="M12 11v5M12 8h.01" />
        </svg>
      </span>
      <p class="leading-7 text-gray-700 dark:text-gray-200">
        {{ message }}
      </p>
    </div>

    <template #footer>
      <BaseButton variant="secondary" :disabled="loading" @click="cancel">
        {{ cancelLabel }}
      </BaseButton>
      <BaseButton
        :variant="buttonVariant"
        :loading="loading"
        @click="emit('confirm')"
      >
        {{ confirmLabel }}
      </BaseButton>
    </template>
  </BaseModal>
</template>
