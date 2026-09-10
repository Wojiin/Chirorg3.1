<script setup>
/** État vide réutilisable qui explique l'absence de résultat et accepte une action optionnelle. */
defineProps({
  title: { type: String, default: 'Aucune donnée' },
  message: {
    type: String,
    default: 'Aucun élément ne correspond à votre recherche.',
  },
})
</script>

<template>
  <div class="empty-state">
    <p class="item-title">{{ title }}</p>
    <p class="text-muted mx-auto mt-2 max-w-md">{{ message }}</p>
    <div v-if="$slots.default" class="mt-5"><slot /></div>
  </div>
</template>
