<script setup>
/** Ligne interactive de checklist : affiche un matériel et remonte une intention de bascule au parent. */
defineProps({
  item: { type: Object, required: true },
  disabled: Boolean,
})
defineEmits(['set-state'])
</script>

<template>
  <article
    class="preparation-item"
    :class="
      item.absent
        ? 'preparation-item-absent'
        : item.coche
          ? 'preparation-item-ready'
          : ''
    "
  >
    <span class="min-w-0 flex-1">
      <span class="item-title block">{{ item.materiel.intitule }}</span>
      <span class="text-muted mt-1 block">
        Adresse : {{ item.materiel.adresse }} · Type : {{ item.materiel.type }}
      </span>
    </span>
    <span class="preparation-item-actions">
      <label class="preparation-choice preparation-choice-ready">
        <input
          type="checkbox"
          :checked="item.coche"
          :disabled="disabled"
          @change="$emit('set-state', item, 'ready')"
        />
        <span>Prêt</span>
      </label>
      <label class="preparation-choice preparation-choice-absent">
        <input
          type="checkbox"
          :checked="item.absent"
          :disabled="disabled"
          @change="$emit('set-state', item, 'absent')"
        />
        <span>Absent</span>
      </label>
    </span>
  </article>
</template>
