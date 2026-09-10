<script setup>
/**
 * Présente un programme et calcule une permutation complète lors d'un glisser-déposer
 * ou d'un déplacement clavier, sans modifier directement le store parent.
 */
import { ref } from 'vue'
import ProgrammeCard from './ProgrammeCard.vue'

const props = defineProps({
  programme: { type: Object, required: true },
  saving: Boolean,
  deletingId: { type: [Number, String], default: null },
})

const emit = defineEmits(['remove', 'reorder'])
const draggedId = ref(null)
const dropTargetId = ref(null)
const dropPosition = ref('before')

/** Initialise le transfert HTML5 avec l'identifiant stable de la chirurgie déplacée. */
function startDrag(event, surgeryId) {
  draggedId.value = String(surgeryId)
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', String(surgeryId))
}

/** Mémorise la cible et le côté d'insertion pour donner un retour visuel correct. */
function indicateDrop(event, targetId) {
  const bounds = event.currentTarget.getBoundingClientRect()
  dropTargetId.value = String(targetId)
  dropPosition.value =
    event.clientY > bounds.top + bounds.height / 2 ? 'after' : 'before'
}

/** Réinitialise l'état visuel du glisser-déposer après abandon ou dépôt. */
function clearDragState() {
  draggedId.value = null
  dropTargetId.value = null
  dropPosition.value = 'before'
}

/** Construit le nouvel ordre complet à partir de la source, de la cible et du côté de dépôt. */
function drop(event, targetId) {
  event.preventDefault()
  const sourceId = draggedId.value || event.dataTransfer.getData('text/plain')
  const normalizedTargetId = String(targetId)
  const position = dropPosition.value

  if (!sourceId || sourceId === normalizedTargetId) {
    clearDragState()
    return
  }

  const ids = props.programme.chirurgies.map((surgery) => surgery.id)
  const sourceIndex = ids.findIndex((id) => String(id) === sourceId)
  if (sourceIndex === -1) {
    clearDragState()
    return
  }

  const [movedId] = ids.splice(sourceIndex, 1)
  const targetIndex = ids.findIndex((id) => String(id) === normalizedTargetId)
  ids.splice(targetIndex + (position === 'after' ? 1 : 0), 0, movedId)
  clearDragState()
  emit('reorder', ids)
}

/** Produit la même permutation avec les boutons accessibles haut / bas. */
function move(surgeryId, offset) {
  const ids = props.programme.chirurgies.map((surgery) => surgery.id)
  const currentIndex = ids.findIndex((id) => String(id) === String(surgeryId))
  const destinationIndex = currentIndex + offset
  if (
    currentIndex < 0 ||
    destinationIndex < 0 ||
    destinationIndex >= ids.length
  )
    return

  const [movedId] = ids.splice(currentIndex, 1)
  ids.splice(destinationIndex, 0, movedId)
  emit('reorder', ids)
}
</script>

<template>
  <section class="programme-group">
    <header class="programme-group-header">
      <div>
        <p class="page-eyebrow">{{ programme.salle }} · {{ programme.date }}</p>
        <h2 class="section-title">
          Dr {{ programme.chirurgien.prenom }} {{ programme.chirurgien.nom }}
        </h2>
      </div>
      <p class="programme-group-count">
        {{ programme.chirurgies.length }}
        {{ programme.chirurgies.length > 1 ? 'chirurgies' : 'chirurgie' }}
      </p>
    </header>

    <p id="programme-order-help" class="programme-order-help">
      <span
        >Glissez une chirurgie ou utilisez les boutons pour modifier
        l’ordre.</span
      >
      <span aria-live="polite">{{ saving ? 'Enregistrement…' : '' }}</span>
    </p>

    <ol class="programme-surgery-list" aria-describedby="programme-order-help">
      <li
        v-for="(chirurgie, index) in programme.chirurgies"
        :key="chirurgie.id"
        class="draggable-programme-item"
        :class="{
          'draggable-programme-item-active': draggedId === String(chirurgie.id),
          'draggable-programme-item-before':
            dropTargetId === String(chirurgie.id) && dropPosition === 'before',
          'draggable-programme-item-after':
            dropTargetId === String(chirurgie.id) && dropPosition === 'after',
        }"
        :draggable="!saving"
        @dragstart="startDrag($event, chirurgie.id)"
        @dragend="clearDragState"
        @dragover.prevent="indicateDrop($event, chirurgie.id)"
        @drop="drop($event, chirurgie.id)"
      >
        <div class="drag-handle">
          <span aria-hidden="true">⋮⋮</span>
          <strong
            ><span class="sr-only">Position </span>{{ chirurgie.ordre }}</strong
          >
          <div class="drag-move-actions">
            <button
              type="button"
              :disabled="saving || index === 0"
              :aria-label="`Déplacer ${chirurgie.chirurgieModele?.intitule} vers le haut`"
              @click="move(chirurgie.id, -1)"
            >
              <span aria-hidden="true">↑</span>
              <span class="drag-move-label">Monter</span>
            </button>
            <button
              type="button"
              :disabled="saving || index === programme.chirurgies.length - 1"
              :aria-label="`Déplacer ${chirurgie.chirurgieModele?.intitule} vers le bas`"
              @click="move(chirurgie.id, 1)"
            >
              <span aria-hidden="true">↓</span>
              <span class="drag-move-label">Descendre</span>
            </button>
          </div>
        </div>
        <ProgrammeCard
          :chirurgie="chirurgie"
          :deleting="String(deletingId) === String(chirurgie.id)"
          @remove="emit('remove', $event)"
        />
      </li>
    </ol>
  </section>
</template>
