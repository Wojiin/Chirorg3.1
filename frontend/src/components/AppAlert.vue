<script setup>
defineProps({
  message: { type: String, required: true },
  variant: { type: String, default: 'error' },
})
</script>

<template>
  <div class="app-alert" :class="`app-alert--${variant}`" role="alert">
    <span class="app-alert__dot" aria-hidden="true"></span>
    <span>{{ message }}</span>
  </div>
</template>
