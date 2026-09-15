<script setup>
/** Vue de liste administrative : son script ne relie que l'affichage au composable dédié. */
import { defineAsyncComponent } from 'vue'
import { useAdminListView } from '@/composables/useAdminListView'
import PageContainer from '@/components/ui/PageContainer.vue'
import PageHeading from '@/components/ui/PageHeading.vue'
import AdminItemActions from '@/components/AdminItemActions.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BasePagination from '@/components/ui/BasePagination.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorMessage from '@/components/ui/ErrorMessage.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

const BaseCombobox = defineAsyncComponent(
  () => import('@/components/ui/BaseCombobox.vue'),
)

const props = defineProps({
  resourceSlug: { type: String, required: true },
})

const {
  deletingId,
  displayedError,
  getAdminItemDetails,
  getAdminItemTitle,
  hasDisplayedItems,
  hasSpecialityFilter,
  hasSurgeonFilter,
  isTechnicalSheetList,
  pageLoading,
  page,
  itemsPerPage,
  items,
  paginationTotal,
  pendingRemoval,
  requestRemoval,
  cancelRemoval,
  confirmRemoval,
  resource,
  search,
  specialityFilter,
  specialityOptions,
  surgeonFilter,
  surgeonOptions,
  technicalSheetGroups,
} = useAdminListView(props)
</script>

<template>
  <PageContainer>
    <PageHeading
      eyebrow="Administration"
      :title="resource?.label ?? 'Référentiel'"
      :description="resource?.description"
    >
      <template #action>
        <div v-if="resource" class="flex flex-wrap gap-3">
          <RouterLink :to="{ name: 'admin' }" class="secondary-link">
            ← Retour aux référentiels
          </RouterLink>
          <RouterLink
            :to="{ name: 'admin-new', params: { resource: resourceSlug } }"
            class="primary-link"
          >
            + Ajouter dans {{ resource.label }}
          </RouterLink>
        </div>
      </template>
    </PageHeading>

    <div
      v-if="resource"
      class="grid max-w-6xl gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
      <BaseInput
        v-model="search"
        label="Rechercher"
        type="search"
        placeholder="Rechercher dans le référentiel…"
      />
      <Suspense v-if="hasSpecialityFilter">
        <BaseCombobox
          v-model="specialityFilter"
          :label="
            isTechnicalSheetList ? 'Spécialité de chirurgie' : 'Spécialité'
          "
          :options="specialityOptions"
          placeholder="Toutes les spécialités"
          allow-empty
        />
        <template #fallback>
          <div
            class="h-[70px] animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"
          />
        </template>
      </Suspense>
      <Suspense v-if="hasSurgeonFilter">
        <BaseCombobox
          v-model="surgeonFilter"
          label="Chirurgien"
          :options="surgeonOptions"
          placeholder="Tous les chirurgiens"
          allow-empty
        />
        <template #fallback>
          <div
            class="h-[70px] animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"
          />
        </template>
      </Suspense>
    </div>
    <ErrorMessage v-if="displayedError" :message="displayedError" />
    <LoadingState v-if="pageLoading" />
    <EmptyState v-else-if="resource && !hasDisplayedItems" />

    <section
      v-else-if="resource && isTechnicalSheetList"
      aria-labelledby="resource-list-title"
      class="space-y-8"
    >
      <h2 id="resource-list-title" class="sr-only">
        Fiches techniques regroupées par chirurgie
      </h2>

      <article v-for="group in technicalSheetGroups" :key="group.id">
        <header class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div>
            <p
              class="text-xs font-semibold uppercase tracking-wide text-chirorg-700 dark:text-chirorg-300"
            >
              {{ group.speciality }}
            </p>
            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
              {{ group.title }}
            </h3>
          </div>
          <span class="status-badge status-neutral">
            {{ group.items.length }} fiche{{
              group.items.length > 1 ? 's' : ''
            }}
          </span>
        </header>

        <ul class="admin-mobile-list">
          <li v-for="item in group.items" :key="item.id">
            <article class="admin-mobile-card">
              <p
                class="text-xs font-semibold text-chirorg-700 dark:text-chirorg-300"
              >
                Étape {{ item.ordre }}
              </p>
              <h4 class="item-title mt-1">{{ getAdminItemTitle(item) }}</h4>
              <p class="text-muted mt-1 line-clamp-2">
                {{ getAdminItemDetails(item) }}
              </p>
              <AdminItemActions
                :resource-slug="resourceSlug"
                :item="item"
                :title="getAdminItemTitle(item)"
                :deleting="deletingId === item.id"
                @remove="requestRemoval(item)"
              />
            </article>
          </li>
        </ul>

        <div class="admin-table-shell">
          <table class="w-full text-left text-sm">
            <caption class="sr-only">
              Fiches techniques de
              {{
                group.title
              }}
            </caption>
            <thead class="admin-table-head">
              <tr>
                <th scope="col" class="px-5 py-4">Ordre</th>
                <th scope="col" class="px-5 py-4">Intitulé</th>
                <th scope="col" class="px-5 py-4">Consigne</th>
                <th scope="col" class="px-5 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="item in group.items" :key="item.id">
                <td class="admin-table-cell font-semibold">{{ item.ordre }}</td>
                <th scope="row" class="admin-table-cell item-title">
                  {{ getAdminItemTitle(item) }}
                </th>
                <td
                  class="admin-table-cell max-w-lg text-gray-500 dark:text-gray-400"
                >
                  {{ getAdminItemDetails(item) }}
                </td>
                <td class="admin-table-cell">
                  <AdminItemActions
                    :resource-slug="resourceSlug"
                    :item="item"
                    :title="getAdminItemTitle(item)"
                    :deleting="deletingId === item.id"
                    align-end
                    @remove="requestRemoval(item)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section v-else-if="resource" aria-labelledby="resource-list-title">
      <h2 id="resource-list-title" class="sr-only">Liste des éléments</h2>
      <ul class="admin-mobile-list">
        <li v-for="item in items" :key="item.id">
          <article class="admin-mobile-card">
            <h3 class="item-title">{{ getAdminItemTitle(item) }}</h3>
            <p class="text-muted mt-1 line-clamp-2">
              {{ getAdminItemDetails(item) }}
            </p>
            <AdminItemActions
              :resource-slug="resourceSlug"
              :item="item"
              :title="getAdminItemTitle(item)"
              :deleting="deletingId === item.id"
              @remove="requestRemoval(item)"
            />
          </article>
        </li>
      </ul>

      <div class="admin-table-shell">
        <table class="w-full text-left text-sm">
          <caption class="sr-only">
            Éléments du référentiel
            {{
              resource.label
            }}
          </caption>
          <thead class="admin-table-head">
            <tr>
              <th scope="col" class="px-5 py-4">Intitulé</th>
              <th scope="col" class="px-5 py-4">Informations</th>
              <th scope="col" class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="item in items" :key="item.id">
              <th scope="row" class="admin-table-cell item-title">
                {{ getAdminItemTitle(item) }}
              </th>
              <td
                class="admin-table-cell max-w-lg text-gray-500 dark:text-gray-400"
              >
                {{ getAdminItemDetails(item) }}
              </td>
              <td class="admin-table-cell">
                <AdminItemActions
                  :resource-slug="resourceSlug"
                  :item="item"
                  :title="getAdminItemTitle(item)"
                  :deleting="deletingId === item.id"
                  align-end
                  @remove="requestRemoval(item)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <BasePagination
      v-if="resource && hasDisplayedItems && !pageLoading"
      v-model:page="page"
      :total="paginationTotal"
      :items-per-page="itemsPerPage"
      :label="`Pagination du référentiel ${resource.label}`"
    />

    <ConfirmationModal
      :open="Boolean(pendingRemoval)"
      variant="danger"
      title="Confirmer la suppression"
      :message="`Voulez-vous vraiment supprimer « ${pendingRemoval ? getAdminItemTitle(pendingRemoval) : ''} » ? Cette action est irréversible.`"
      confirm-label="Supprimer"
      :loading="deletingId === pendingRemoval?.id"
      @cancel="cancelRemoval"
      @confirm="confirmRemoval"
    />
  </PageContainer>
</template>
