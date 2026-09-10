<script setup>
/** Actions de modification et suppression partagées par les rendus admin mobile et tableau. */
import BaseButton from '@/components/ui/BaseButton.vue'

defineProps({
  resourceSlug: { type: String, required: true },
  item: { type: Object, required: true },
  title: { type: String, required: true },
  deleting: Boolean,
  alignEnd: Boolean,
})

defineEmits(['remove'])
</script>

<template>
  <div class="admin-item-actions" :class="{ 'justify-end': alignEnd }">
    <RouterLink
      :to="{
        name: 'admin-edit',
        params: { resource: resourceSlug, id: item.id },
      }"
      :aria-label="`Modifier ${title}`"
      class="secondary-link"
    >
      Modifier
    </RouterLink>
    <BaseButton
      variant="danger"
      size="sm"
      :loading="deleting"
      :aria-label="`Supprimer ${title}`"
      @click="$emit('remove')"
    >
      Supprimer
    </BaseButton>
  </div>
</template>
