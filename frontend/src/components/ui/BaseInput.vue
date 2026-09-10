<script setup>
/** Champ texte accessible qui relie label, erreur et valeur v-model par un identifiant unique. */
import { computed, useId } from 'vue'

defineProps({
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  required: Boolean,
  autocomplete: { type: String, default: undefined },
  min: { type: [String, Number], default: undefined },
  minlength: { type: [String, Number], default: undefined },
  maxlength: { type: [String, Number], default: undefined },
  pattern: { type: String, default: undefined },
})
const model = defineModel({ type: [String, Number], required: true })

const id = useId()
const errorId = computed(() => `${id}-error`)
</script>

<template>
  <div>
    <label :for="id" class="field-label">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="field-required">*</span>
    </label>
    <input
      :id="id"
      v-model="model"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      :autocomplete="autocomplete"
      :min="min"
      :minlength="minlength"
      :maxlength="maxlength"
      :pattern="pattern"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? errorId : undefined"
      class="field-control"
      :class="{ 'field-control-invalid': error }"
    />
    <p v-if="error" :id="errorId" class="field-error">{{ error }}</p>
  </div>
</template>
