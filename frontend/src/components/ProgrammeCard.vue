<script setup>
/** Carte de chirurgie qui synthétise son état et propose la prochaine action autorisée. */
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import ProgressBar from '@/components/ui/ProgressBar.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import { ERROR_MESSAGES } from '@/config/errorMessages'
import { formatDateTime } from '@/utils/date'

const props = defineProps({
  chirurgie: { type: Object, required: true },
  deleting: Boolean,
})

defineEmits(['remove'])

const surgeonName = computed(() =>
  `Dr ${props.chirurgie.chirurgien?.prenom ?? ''} ${props.chirurgie.chirurgien?.nom ?? ''}`.trim(),
)
const progress = computed(
  () =>
    props.chirurgie.progressionPreparation ?? {
      total: 0,
      coches: 0,
      complete: false,
    },
)
const formattedModificationDate = computed(() =>
  formatDateTime(props.chirurgie.modifieLe),
)
const isPartial = computed(
  () =>
    props.chirurgie.etatValidation === 'VALIDATION_PARTIELLE' ||
    (progress.value.complete && (progress.value.absents ?? 0) > 0),
)
</script>

<template>
  <article class="programme-card w-full">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3
          class="mt-2 text-lg font-bold leading-snug text-gray-950 dark:text-white"
        >
          {{ chirurgie.chirurgieModele?.intitule }}
        </h3>
      </div>
      <StatusBadge
        :status="
          chirurgie.valide
            ? 'Validée'
            : isPartial
              ? 'Validation partielle'
              : progress.coches
                ? 'En cours'
                : 'À préparer'
        "
      />
    </div>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      {{ surgeonName }}
    </p>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      Dernière modification par {{ chirurgie.modifiePar ?? 'Non renseigné' }} le
      {{ formattedModificationDate }}
    </p>
    <div class="my-5">
      <ProgressBar
        :total="progress.total"
        :value="progress.traites ?? progress.coches"
      />
    </div>
    <div
      class="mt-auto flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700"
    >
      <RouterLink
        v-if="!chirurgie.valide"
        :to="{
          name: isPartial ? 'validation-partielle' : 'preparation',
          params: { id: chirurgie.id },
        }"
        class="compact-primary-link"
      >
        {{ isPartial ? 'Régulariser' : 'Préparer' }}
      </RouterLink>
      <RouterLink
        v-else
        :to="{ name: 'vue-finale', params: { id: chirurgie.id } }"
        class="compact-primary-link"
      >
        Vue finale
      </RouterLink>
      <BaseButton
        variant="danger"
        size="sm"
        :disabled="chirurgie.valide"
        :loading="deleting"
        :title="
          chirurgie.valide
            ? ERROR_MESSAGES.validatedSurgeryDelete
            : 'Supprimer cette chirurgie'
        "
        :aria-label="`Supprimer ${chirurgie.chirurgieModele?.intitule ?? 'la chirurgie'}`"
        @click="$emit('remove', chirurgie)"
      >
        <svg
          aria-hidden="true"
          class="size-4"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M3 6h18" />
          <path d="M8 6V4h8v2" />
          <path d="M19 6l-1 14H6L5 6" />
          <path d="M10 11v5" />
          <path d="M14 11v5" />
        </svg>
        Supprimer
      </BaseButton>
    </div>
  </article>
</template>
