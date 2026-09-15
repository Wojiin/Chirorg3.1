<script setup>
/** Calcule et expose une progression matériel bornée, y compris pour les lecteurs d'écran. */
import { computed } from 'vue'

const props = defineProps({
  total: { type: Number, default: 0 },
  value: { type: Number, default: 0 },
  label: { type: String, default: '' },
  dangerRemainder: Boolean,
})

const safeValue = computed(() =>
  Math.min(Math.max(props.value, 0), Math.max(props.total, 0)),
)
const percent = computed(() =>
  props.total > 0 ? Math.round((safeValue.value / props.total) * 100) : 0,
)
const remaining = computed(() => Math.max(props.total - safeValue.value, 0))
const accessibleLabel = computed(
  () =>
    props.label ||
    (props.dangerRemainder
      ? `${safeValue.value} matériels prêts sur ${props.total}, ${remaining.value} absents ou non préparés`
      : `${safeValue.value} / ${props.total} matériels prêts`),
)
</script>

<template>
  <div>
    <div class="progress-caption">
      <span>{{ accessibleLabel }}</span>
      <span>{{ percent }} %</span>
    </div>
    <div
      role="progressbar"
      :aria-label="accessibleLabel"
      aria-valuemin="0"
      :aria-valuemax="total"
      :aria-valuenow="safeValue"
      class="progress-track"
      :class="{ 'progress-track-danger': dangerRemainder && total > 0 }"
    >
      <div class="progress-value" :style="{ width: `${percent}%` }"></div>
    </div>
  </div>
</template>
