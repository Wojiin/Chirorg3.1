<script setup>
/** Sélecteur de matériels par nom avec ajout et retrait explicites. */
import { computed, ref } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'

const props = defineProps({
  label: { type: String, required: true },
  options: { type: Array, default: () => [] },
  emptyMessage: {
    type: String,
    default: 'Aucun matériel disponible pour cette recherche.',
  },
  required: Boolean,
})
const model = defineModel({ type: Array, required: true })
const search = ref('')

const selectedIds = computed(() => new Set(model.value.map(String)))
const selectedOptions = computed(() =>
  props.options.filter((option) => selectedIds.value.has(String(option.value))),
)
const availableOptions = computed(() => {
  const needle = search.value.trim().toLocaleLowerCase('fr')
  return props.options.filter(
    (option) =>
      !selectedIds.value.has(String(option.value)) &&
      (!needle ||
        `${option.label} ${option.meta ?? ''}`
          .toLocaleLowerCase('fr')
          .includes(needle)),
  )
})

function add(value) {
  if (!selectedIds.value.has(String(value)))
    model.value = [...model.value, value]
}

function remove(value) {
  model.value = model.value.filter(
    (selected) => String(selected) !== String(value),
  )
}
</script>

<template>
  <fieldset class="md:col-span-2">
    <legend class="field-label">
      {{ label }}
      <span v-if="required" aria-hidden="true" class="field-required">*</span>
    </legend>

    <div
      class="mt-2 grid gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700 lg:grid-cols-2"
    >
      <section aria-labelledby="selected-materials-title">
        <h3
          id="selected-materials-title"
          class="text-sm font-bold text-gray-900 dark:text-white"
        >
          Matériels ajoutés ({{ selectedOptions.length }})
        </h3>
        <p v-if="!selectedOptions.length" class="text-muted mt-3 text-sm">
          Aucun matériel ajouté.
        </p>
        <ul v-else class="mt-3 space-y-2">
          <li
            v-for="option in selectedOptions"
            :key="option.value"
            class="flex items-center justify-between gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800"
          >
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold">{{ option.label }}</p>
              <p
                v-if="option.meta"
                class="truncate text-xs text-gray-500 dark:text-gray-400"
              >
                {{ option.meta }}
              </p>
            </div>
            <BaseButton
              type="button"
              variant="danger"
              size="sm"
              @click="remove(option.value)"
            >
              Retirer
            </BaseButton>
          </li>
        </ul>
      </section>

      <section aria-labelledby="available-materials-title">
        <h3 id="available-materials-title" class="sr-only">
          Matériels disponibles
        </h3>
        <BaseInput
          v-model="search"
          label="Rechercher un matériel par nom"
          type="search"
          placeholder="Nom du matériel…"
        />
        <p v-if="!availableOptions.length" class="text-muted mt-3 text-sm">
          {{ emptyMessage }}
        </p>
        <ul v-else class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-1">
          <li
            v-for="option in availableOptions"
            :key="option.value"
            class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700"
          >
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold">{{ option.label }}</p>
              <p
                v-if="option.meta"
                class="truncate text-xs text-gray-500 dark:text-gray-400"
              >
                {{ option.meta }}
              </p>
            </div>
            <BaseButton
              type="button"
              variant="secondary"
              size="sm"
              @click="add(option.value)"
            >
              Ajouter
            </BaseButton>
          </li>
        </ul>
      </section>
    </div>
  </fieldset>
</template>
