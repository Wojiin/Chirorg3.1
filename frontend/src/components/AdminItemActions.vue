<script setup>
/** Actions de modification et suppression partagées par les rendus admin mobile et tableau. */
import { computed } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const props = defineProps({
  resourceSlug: { type: String, required: true },
  item: { type: Object, required: true },
  title: { type: String, required: true },
  deleting: Boolean,
  alignEnd: Boolean,
})

defineEmits(['remove'])

const deletionDisabled = computed(
  () =>
    props.resourceSlug === 'specialites' &&
    props.item.intitule?.trim().toLocaleLowerCase('fr-FR') ===
      'sans spécialité',
)
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
      :disabled="deletionDisabled"
      :aria-label="
        deletionDisabled
          ? `Suppression impossible pour ${title}`
          : `Supprimer ${title}`
      "
      :title="
        deletionDisabled
          ? 'Cette spécialité système ne peut pas être supprimée.'
          : undefined
      "
      @click="$emit('remove')"
    >
      Supprimer
    </BaseButton>
  </div>
</template>
