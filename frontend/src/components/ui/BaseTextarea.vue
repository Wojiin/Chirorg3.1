<script setup>
/** Zone de texte accessible, avec contrat v-model et message d'erreur cohérents. */
import { computed, useId } from 'vue'

defineProps({
  label: { type: String, required: true },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  required: Boolean,
  rows: { type: Number, default: 4 },
})
const model = defineModel({ type: String, required: true })

const id = useId()
const errorId = computed(() => `${id}-error`)
</script>

<template>
  <div>
    <label :for="id" class="field-label">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="field-required">*</span>
    </label>
    <textarea
      :id="id"
      v-model="model"
      :placeholder="placeholder"
      :required="required"
      :rows="rows"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? errorId : undefined"
      class="field-control min-h-auto resize-y"
      :class="{ 'field-control-invalid': error }"
    ></textarea>
    <p v-if="error" :id="errorId" class="field-error">{{ error }}</p>
  </div>
</template>
