<script setup>
defineProps({
  open: Boolean,
  title: { type: String, required: true },
  message: { type: String, required: true },
  confirmLabel: { type: String, default: 'Confirmer' },
  busy: Boolean,
  danger: Boolean,
})

defineEmits(['cancel', 'confirm'])
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="dialog-backdrop"
      role="presentation"
      @click.self="$emit('cancel')"
    >
      <section
        class="dialog"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
      >
        <p class="eyebrow">Confirmation</p>
        <h2>{{ title }}</h2>
        <p>{{ message }}</p>
        <div class="form-actions">
          <button
            class="secondary-button"
            type="button"
            :disabled="busy"
            @click="$emit('cancel')"
          >
            Annuler
          </button>
          <button
            :class="danger ? 'danger-button' : 'primary-button'"
            type="button"
            :disabled="busy"
            @click="$emit('confirm')"
          >
            {{ busy ? 'Traitement…' : confirmLabel }}
          </button>
        </div>
      </section>
    </div>
  </Teleport>
</template>
