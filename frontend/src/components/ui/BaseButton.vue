<script setup>
/** Bouton normalisé : associe variante, taille et état de chargement sans dupliquer le style. */
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  disabled: Boolean,
  loading: Boolean,
  type: { type: String, default: 'button' },
})

const classes = computed(() => {
  const variants = {
    primary: 'button-primary',
    success: 'button-success',
    warning: 'button-warning',
    secondary: 'button-secondary',
    danger: 'button-danger',
    ghost: 'button-ghost',
  }
  const sizes = {
    sm: 'button-sm',
    md: 'button-md',
    lg: 'button-lg',
  }

  return [
    variants[props.variant] ?? variants.primary,
    sizes[props.size] ?? sizes.md,
  ]
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="button-base"
    :class="classes"
  >
    <span
      v-if="loading"
      aria-hidden="true"
      class="loading-spinner size-4"
    ></span>
    <slot />
  </button>
</template>
