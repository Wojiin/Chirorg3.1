<script setup>
import { computed, ref, watch } from 'vue'

import AppAlert from '../components/AppAlert.vue'
import AppLoading from '../components/AppLoading.vue'
import ConfirmDialog from '../components/admin/ConfirmDialog.vue'
import { getAdminResource } from '../config/adminResources.js'
import { itemDetails, itemSpecialityId, itemTitle } from '../domain/admin.js'
import { adminApi } from '../services/admin.js'
import { useAdminStore } from '../stores/admin.js'

const props = defineProps({ resource: { type: String, required: true } })
const store = useAdminStore()
const search = ref('')
const speciality = ref('')
const specialities = ref([])
const pendingRemoval = ref(null)

const definition = computed(() => getAdminResource(props.resource))
const supportsSpeciality = computed(() =>
  [
    'chirurgiens',
    'chirurgie-modeles',
    'materiels',
    'fiches-techniques',
    'listes-materiel',
  ].includes(props.resource),
)
const displayedItems = computed(() => {
  const needle = search.value.trim().toLocaleLowerCase('fr')
  return store.items.filter((item) => {
    const matchesSearch =
      !needle || JSON.stringify(item).toLocaleLowerCase('fr').includes(needle)
    const matchesSpeciality =
      !speciality.value || String(itemSpecialityId(item)) === speciality.value
    return matchesSearch && matchesSpeciality
  })
})

async function load() {
  search.value = ''
  speciality.value = ''
  if (!definition.value) return
  const requests = [store.loadItems(props.resource)]
  if (supportsSpeciality.value) {
    requests.push(
      adminApi
        .list('specialites')
        .then((items) => (specialities.value = items)),
    )
  }
  await Promise.all(requests)
}

async function confirmRemoval() {
  if (!pendingRemoval.value) return
  if (await store.removeItem(props.resource, pendingRemoval.value.id))
    pendingRemoval.value = null
}

watch(() => props.resource, load, { immediate: true })
</script>

<template>
  <section class="page-section">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>{{ definition?.label ?? 'Référentiel inconnu' }}</h1>
        <p>{{ definition?.description }}</p>
      </div>
      <RouterLink
        v-if="definition"
        class="primary-button"
        :to="{ name: 'admin-create', params: { resource } }"
      >
        Ajouter
      </RouterLink>
    </header>

    <AppAlert v-if="!definition" message="Ce référentiel n’existe pas." />
    <template v-else>
      <div class="admin-filters">
        <label>
          <span>Rechercher</span>
          <input
            v-model="search"
            type="search"
            placeholder="Nom, intitulé, email…"
          />
        </label>
        <label v-if="supportsSpeciality">
          <span>Spécialité</span>
          <select v-model="speciality">
            <option value="">Toutes les spécialités</option>
            <option
              v-for="option in specialities"
              :key="option.id"
              :value="String(option.id)"
            >
              {{ option.intitule }}
            </option>
          </select>
        </label>
      </div>

      <AppAlert v-if="store.error" :message="store.error" />
      <AppLoading v-if="store.loading" message="Chargement du référentiel…" />
      <div v-else-if="displayedItems.length" class="admin-table-shell">
        <table class="admin-table">
          <caption class="sr-only">
            {{
              definition.label
            }}
          </caption>
          <thead>
            <tr>
              <th>Intitulé</th>
              <th>Informations</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in displayedItems" :key="item.id">
              <th scope="row">{{ itemTitle(item) }}</th>
              <td>{{ itemDetails(item) }}</td>
              <td class="admin-actions">
                <RouterLink
                  :to="{
                    name: 'admin-edit',
                    params: { resource, id: item.id },
                  }"
                  >Modifier</RouterLink
                >
                <button
                  type="button"
                  :disabled="store.deletingId === item.id"
                  @click="pendingRemoval = item"
                >
                  Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">
        <span>Aucun résultat</span>
        <h2>Ce référentiel est vide</h2>
        <p>Ajoutez un élément ou modifiez les filtres.</p>
      </div>
    </template>

    <ConfirmDialog
      :open="Boolean(pendingRemoval)"
      title="Confirmer la suppression"
      :message="`Supprimer « ${pendingRemoval ? itemTitle(pendingRemoval) : ''} » ? Cette action est définitive.`"
      confirm-label="Supprimer"
      :busy="store.deletingId !== null"
      danger
      @cancel="pendingRemoval = null"
      @confirm="confirmRemoval"
    />
  </section>
</template>
