import { defineStore } from 'pinia'
import { preparationApi } from '@/services/preparationApi'
import { getApiErrorMessage } from '@/api/response'
import { normalizeFinalView, normalizePreparation } from '@/mappers/preparation'

/** Gère la checklist, sa progression optimiste et la validation finale d'une chirurgie. */
export const usePreparationStore = defineStore('preparation', {
  state: () => ({
    preparation: null,
    finalView: null,
    pendingLoads: 0,
    loadRequestId: 0,
    savingId: null,
    error: '',
  }),

  getters: {
    loading: (state) => state.pendingLoads > 0,
    isComplete: (state) =>
      Boolean(state.preparation?.preparations?.length) &&
      state.preparation.preparations.every((item) => item.coche),
    isResolved: (state) =>
      Boolean(state.preparation?.preparations?.length) &&
      state.preparation.preparations.every((item) => item.coche || item.absent),
    isPartial: (state) =>
      state.preparation?.chirurgie?.etatValidation === 'VALIDATION_PARTIELLE',
  },

  actions: {
    /** Recalcule localement les compteurs utilisés par la barre de progression. */
    updateProgress() {
      if (!this.preparation) return

      const total = this.preparation.preparations.length
      const coches = this.preparation.preparations.filter(
        (item) => item.coche,
      ).length
      const absents = this.preparation.preparations.filter(
        (item) => item.absent,
      ).length
      const traites = coches + absents
      this.preparation.progressionPreparation = {
        total,
        coches,
        absents,
        traites,
        complete: total > 0 && total === traites,
      }
    },

    /** Charge la checklist fraîche d'une chirurgie et réinitialise les vues incompatibles. */
    async loadPreparation(id) {
      const requestId = ++this.loadRequestId
      this.pendingLoads += 1
      this.error = ''
      this.preparation = null
      this.finalView = null

      try {
        const data = await preparationApi.getPreparation(id)
        const preparation = normalizePreparation(data)
        if (requestId !== this.loadRequestId) return null
        this.preparation = preparation
        return this.preparation
      } catch (error) {
        if (requestId === this.loadRequestId) {
          this.error = getApiErrorMessage(
            error,
            'Impossible de charger la préparation.',
          )
        }
        return null
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Bascule l'état d'un matériel de façon optimiste puis restaure l'ancien état si l'API échoue. */
    async setMaterialState(item, state) {
      const previous = { coche: item.coche, absent: item.absent }
      item.coche = state === 'ready' ? !item.coche : false
      item.absent = state === 'absent' ? !item.absent : false
      this.updateProgress()
      this.savingId = item.id
      this.error = ''

      try {
        Object.assign(
          item,
          await preparationApi.toggle(item.id, {
            coche: item.coche,
            absent: item.absent,
          }),
        )
        this.updateProgress()
        return true
      } catch (error) {
        Object.assign(item, previous)
        this.updateProgress()
        this.error = getApiErrorMessage(
          error,
          'La mise à jour du matériel a échoué.',
        )
        return false
      } finally {
        this.savingId = null
      }
    },

    async toggleMaterial(item) {
      return this.setMaterialState(item, 'ready')
    },

    /** Valide une chirurgie uniquement lorsque toutes ses lignes sont cochées. */
    async validateSurgery() {
      if (!this.isResolved || !this.preparation) return false

      this.pendingLoads += 1
      this.error = ''

      try {
        const data = await preparationApi.validate(
          this.preparation.chirurgie.id,
        )
        this.preparation.chirurgie = {
          ...this.preparation.chirurgie,
          ...data,
          etatValidation: data.valide ? 'VALIDEE' : 'VALIDATION_PARTIELLE',
        }
        return data.valide ? 'final' : 'partial'
      } catch (error) {
        this.error = getApiErrorMessage(
          error,
          'La validation de la chirurgie a échoué.',
        )
        return false
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Charge la vue finale et vide la checklist de travail devenue obsolète. */
    async loadFinalView(id) {
      const requestId = ++this.loadRequestId
      this.pendingLoads += 1
      this.error = ''
      this.preparation = null
      this.finalView = null

      try {
        const data = await preparationApi.getFinalView(id)
        const finalView = normalizeFinalView(data)
        if (requestId === this.loadRequestId) this.finalView = finalView
        return finalView
      } catch (error) {
        if (requestId === this.loadRequestId) {
          this.error = getApiErrorMessage(
            error,
            'Impossible de charger la vue finale.',
          )
        }
        return null
      } finally {
        this.pendingLoads -= 1
      }
    },
  },
})
