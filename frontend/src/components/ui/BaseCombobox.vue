<script setup>
/** Sélecteur filtrable accessible pour les référentiels volumineux. */
import { computed, useId } from 'vue'
import {
  ComboboxAnchor,
  ComboboxContent,
  ComboboxEmpty,
  ComboboxInput,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxPortal,
  ComboboxRoot,
  ComboboxTrigger,
  ComboboxViewport,
} from 'reka-ui'

const props = defineProps({
  label: { type: String, required: true },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Sélectionner ou rechercher' },
  emptyLabel: { type: String, default: 'Aucun résultat.' },
  error: { type: String, default: '' },
  required: Boolean,
  allowEmpty: Boolean,
  disabled: Boolean,
})
const model = defineModel({ type: [String, Number], required: true })

const id = useId()
const errorId = computed(() => `${id}-error`)
const hasValue = computed(() => model.value !== '' && model.value != null)
const normalizedOptions = computed(() =>
  props.options.map((option) =>
    typeof option === 'object' ? option : { label: option, value: option },
  ),
)

function displayValue(value) {
  return (
    normalizedOptions.value.find(
      (option) => String(option.value) === String(value),
    )?.label ?? ''
  )
}
</script>

<template>
  <div>
    <label :for="id" class="field-label">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="field-required">*</span>
    </label>
    <ComboboxRoot
      v-model="model"
      :disabled="disabled"
      :required="required"
      open-on-click
      open-on-focus
      reset-search-term-on-select
    >
      <ComboboxAnchor class="relative mt-2 block">
        <ComboboxInput
          :id="id"
          :display-value="displayValue"
          :placeholder="placeholder"
          :aria-invalid="Boolean(error)"
          :aria-describedby="error ? errorId : undefined"
          class="field-control mt-0 pr-20"
          :class="{ 'field-control-invalid': error }"
        />
        <button
          v-if="allowEmpty && hasValue"
          type="button"
          class="absolute inset-y-0 right-10 grid w-9 place-items-center text-gray-500 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-chirorg-500 dark:text-gray-300 dark:hover:text-white"
          :aria-label="`Effacer le filtre ${label}`"
          @click="model = ''"
        >
          <span aria-hidden="true">×</span>
        </button>
        <ComboboxTrigger
          class="absolute inset-y-0 right-0 grid w-11 place-items-center rounded-r-xl text-gray-500 hover:text-chirorg-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-chirorg-500 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-300"
          :aria-label="`Afficher les options pour ${label}`"
        >
          <span aria-hidden="true">⌄</span>
        </ComboboxTrigger>
      </ComboboxAnchor>
      <ComboboxPortal>
        <ComboboxContent
          position="popper"
          :side-offset="6"
          class="z-[70] max-h-72 w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900"
        >
          <ComboboxViewport class="max-h-70 overflow-y-auto">
            <ComboboxItem
              v-for="option in normalizedOptions"
              :key="option.value"
              :value="option.value"
              class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700 outline-none data-[highlighted]:bg-chirorg-100 data-[highlighted]:text-chirorg-900 dark:text-gray-200 dark:data-[highlighted]:bg-chirorg-900/50 dark:data-[highlighted]:text-white"
            >
              <span>{{ option.label }}</span>
              <ComboboxItemIndicator aria-hidden="true"
                >✓</ComboboxItemIndicator
              >
            </ComboboxItem>
            <ComboboxEmpty class="px-3 py-4 text-center text-sm text-gray-500">
              {{ emptyLabel }}
            </ComboboxEmpty>
          </ComboboxViewport>
        </ComboboxContent>
      </ComboboxPortal>
    </ComboboxRoot>
    <p v-if="error" :id="errorId" class="field-error">{{ error }}</p>
  </div>
</template>
