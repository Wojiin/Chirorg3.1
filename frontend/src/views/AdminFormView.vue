<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import ConfirmDialog from '../components/admin/ConfirmDialog.vue'
import {
  getAdminFormFields,
  referencesByResource,
} from '../config/adminForms.js'
import { getAdminResource } from '../config/adminResources.js'
import { buildAdminPayload, createAdminForm } from '../mappers/admin.js'
import { adminApi } from '../services/admin.js'
import { useAdminStore } from '../stores/admin.js'

const props = defineProps({
  resource: { type: String, required: true },
  id: { type: Number, default: null },
})
const router = useRouter()
const store = useAdminStore()
const collections = ref({})
const form = reactive({})
const localError = ref(null)
const confirmationOpen = ref(false)

const definition = computed(() => getAdminResource(props.resource))
const editing = computed(() => Number.isInteger(props.id) && props.id > 0)
const eligibleCollections = computed(() => {
  if (props.resource !== 'listes-materiel' || !form.chirurgien)
    return collections.value
  const surgeon = collections.value.chirurgiens?.find(
    (item) => String(item.id) === String(form.chirurgien),
  )
  const specialityId = surgeon?.specialite?.id
  if (!specialityId) return collections.value
  return {
    ...collections.value,
    materiels: collections.value.materiels?.filter(
      (item) => item.specialite?.id === specialityId,
    ),
    'chirurgie-modeles': collections.value['chirurgie-modeles']?.filter(
      (item) => item.specialite?.id === specialityId,
    ),
  }
})
const fields = computed(() =>
  getAdminFormFields(props.resource, eligibleCollections.value, editing.value),
)

function replaceForm(values) {
  Object.keys(form).forEach((key) => delete form[key])
  Object.assign(form, values)
}

async function load() {
  localError.value = null
  if (!definition.value) return
  try {
    const references = referencesByResource[props.resource] ?? []
    const [referenceEntries, existing] = await Promise.all([
      Promise.all(
        references.map(async (resource) => [
          resource,
          await adminApi.list(resource),
        ]),
      ),
      editing.value ? store.loadItem(props.resource, props.id) : null,
    ])
    collections.value = Object.fromEntries(referenceEntries)
    replaceForm(
      createAdminForm(
        getAdminFormFields(props.resource, collections.value, editing.value),
        existing,
      ),
    )
  } catch {
    localError.value = 'Le formulaire n’a pas pu être préparé.'
  }
}

function submit() {
  const missing = fields.value.find((field) => {
    const value = form[field.key]
    return (
      field.required && (Array.isArray(value) ? value.length === 0 : !value)
    )
  })
  localError.value = missing
    ? `Le champ « ${missing.label} » est obligatoire.`
    : null
  if (!localError.value) confirmationOpen.value = true
}

async function save() {
  confirmationOpen.value = false
  const saved = await store.saveItem(
    props.resource,
    editing.value ? props.id : null,
    buildAdminPayload(form),
  )
  if (saved)
    await router.push({
      name: 'admin-list',
      params: { resource: props.resource },
    })
}

watch(() => [props.resource, props.id], load, { immediate: true })

watch(
  () => form.chirurgien,
  (surgeonId, previousId) => {
    if (
      props.resource !== 'listes-materiel' ||
      previousId == null ||
      String(surgeonId) === String(previousId)
    )
      return
    const materialIds = new Set(
      (eligibleCollections.value.materiels ?? []).map((item) =>
        String(item.id),
      ),
    )
    const surgeryIds = new Set(
      (eligibleCollections.value['chirurgie-modeles'] ?? []).map((item) =>
        String(item.id),
      ),
    )
    form.materiels = (form.materiels ?? []).filter((id) =>
      materialIds.has(String(id)),
    )
    if (!surgeryIds.has(String(form.chirurgieModele))) form.chirurgieModele = ''
  },
)
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>
          {{ editing ? 'Modifier' : 'Ajouter' }}
          {{ definition?.label?.toLocaleLowerCase('fr') }}
        </h1>
        <p>Renseignez les informations du référentiel.</p>
      </div>
    </header>

    <AppAlert v-if="!definition" message="Ce référentiel n’existe pas." />
    <AppLoading
      v-else-if="store.loading"
      message="Préparation du formulaire…"
    />
    <form v-else class="admin-form" @submit.prevent="submit">
      <AppAlert
        v-if="localError || store.error"
        :message="localError || store.error"
      />
      <div class="admin-form-grid">
        <label
          v-for="field in fields"
          :key="field.key"
          :class="{
            'field-wide': ['textarea', 'multiselect'].includes(field.type),
          }"
        >
          <span
            >{{ field.label
            }}<b v-if="field.required" aria-hidden="true"> *</b></span
          >
          <textarea
            v-if="field.type === 'textarea'"
            v-model="form[field.key]"
            rows="7"
          />
          <select
            v-else-if="field.type === 'select'"
            v-model="form[field.key]"
            :required="field.required"
          >
            <option value="">Sélectionner…</option>
            <option
              v-for="option in field.options"
              :key="option.value ?? option"
              :value="option.value ?? option"
            >
              {{ option.label ?? option }}
            </option>
          </select>
          <select
            v-else-if="field.type === 'multiselect'"
            v-model="form[field.key]"
            multiple
            size="8"
            :required="field.required"
          >
            <option
              v-for="option in field.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          <input
            v-else-if="field.type === 'checkbox'"
            v-model="form[field.key]"
            type="checkbox"
          />
          <input
            v-else
            v-model="form[field.key]"
            :type="field.type ?? 'text'"
            :required="field.required"
          />
          <small v-if="field.key === 'motDePasse'"
            >12 caractères minimum, avec minuscule, majuscule, chiffre et
            caractère spécial.</small
          >
          <small v-if="field.type === 'multiselect'"
            >Maintenez Ctrl pour sélectionner plusieurs matériels.</small
          >
        </label>
      </div>
      <div class="form-actions">
        <button
          class="secondary-button"
          type="button"
          @click="router.push({ name: 'admin-list', params: { resource } })"
        >
          Retour
        </button>
        <button class="primary-button" type="submit" :disabled="store.saving">
          Enregistrer
        </button>
      </div>
    </form>

    <ConfirmDialog
      :open="confirmationOpen"
      :title="editing ? 'Confirmer les modifications' : 'Confirmer la création'"
      :message="
        editing
          ? 'Enregistrer les modifications de cette ressource ?'
          : 'Créer cette nouvelle ressource ?'
      "
      :confirm-label="editing ? 'Enregistrer' : 'Créer'"
      :busy="store.saving"
      @cancel="confirmationOpen = false"
      @confirm="save"
    />
  </section>
</template>
