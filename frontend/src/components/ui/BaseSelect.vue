<script setup>
/** Sélecteur accessible qui accepte des options primitives ou des couples label / valeur. */
import { computed, useId } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Sélectionner' },
  error: { type: String, default: '' },
  required: Boolean,
  allowEmpty: Boolean,
  disabled: Boolean,
})
const model = defineModel({ type: [String, Number], required: true })

const id = useId()
const errorId = computed(() => `${id}-error`)
const normalizedOptions = computed(() =>
  props.options.map((option) =>
    typeof option === 'object' ? option : { label: option, value: option },
  ),
)
</script>

<template>
  <div>
    <label :for="id" class="field-label">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="field-required">*</span>
    </label>
    <select
      :id="id"
      v-model="model"
      :required="required"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? errorId : undefined"
      class="field-control"
      :class="{ 'field-control-invalid': error }"
    >
      <option value="" :disabled="!allowEmpty">{{ placeholder }}</option>
      <option
        v-for="option in normalizedOptions"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" :id="errorId" class="field-error">{{ error }}</p>
  </div>
</template>
