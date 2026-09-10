<script setup>
/** Présente une erreur métier dans une région d'alerte accessible. */
defineProps({ message: { type: String, required: true } })
</script>

<template>
  <div role="alert" class="feedback-error">
    {{ message }}
  </div>
</template>
