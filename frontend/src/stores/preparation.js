import { defineStore } from 'pinia'

import { apiErrorMessage } from '../api/problem.js'
import {
  normalizeFinalView,
  normalizePreparation,
} from '../domain/programme.js'
import { preparationApi } from '../services/preparation.js'

export const usePreparationStore = defineStore('preparation', {
  state: () => ({
    preparation: null,
    finalView: null,
    loading: false,
    savingId: null,
    error: null,
  }),
  getters: {
    isResolved: (state) =>
      Boolean(state.preparation?.preparations.length) &&
      state.preparation.preparations.every((item) => item.coche || item.absent),
  },
  actions: {
    updateProgress() {
      if (!this.preparation) return
      const rows = this.preparation.preparations
      const coches = rows.filter((item) => item.coche).length
      const absents = rows.filter((item) => item.absent).length
      this.preparation.progressionPreparation = {
        total: rows.length,
        coches,
        absents,
        traites: coches + absents,
        complete: rows.length > 0 && coches + absents === rows.length,
      }
    },
    async fetch(id) {
      this.loading = true
      this.error = null
      this.finalView = null
      try {
        this.preparation = normalizePreparation(await preparationApi.get(id))
        return this.preparation
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Impossible de charger la préparation.',
        )
        this.preparation = null
        return null
      } finally {
        this.loading = false
      }
    },
    async setState(item, state) {
      const previous = { coche: item.coche, absent: item.absent }
      item.coche = state === 'ready' ? !item.coche : false
      item.absent = state === 'absent' ? !item.absent : false
      this.updateProgress()
      this.savingId = item.id
      this.error = null
      try {
        Object.assign(
          item,
          await preparationApi.setState(item.id, {
            coche: item.coche,
            absent: item.absent,
          }),
        )
        this.updateProgress()
        return true
      } catch (error) {
        Object.assign(item, previous)
        this.updateProgress()
        this.error = apiErrorMessage(
          error,
          'Le matériel n’a pas pu être mis à jour.',
        )
        return false
      } finally {
        this.savingId = null
      }
    },
    async validate() {
      if (!this.isResolved || !this.preparation) return null
      this.loading = true
      this.error = null
      try {
        const surgery = await preparationApi.validate(
          this.preparation.chirurgie.id,
        )
        Object.assign(this.preparation.chirurgie, surgery)
        this.preparation.chirurgie.etatValidation = surgery.valide
          ? 'VALIDEE'
          : 'VALIDATION_PARTIELLE'
        return surgery.valide ? 'final' : 'partial'
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'La chirurgie n’a pas pu être validée.',
        )
        return null
      } finally {
        this.loading = false
      }
    },
    async fetchFinalView(id) {
      this.loading = true
      this.error = null
      try {
        this.finalView = normalizeFinalView(
          await preparationApi.getFinalView(id),
        )
        return this.finalView
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Impossible de charger la vue finale.',
        )
        this.finalView = null
        return null
      } finally {
        this.loading = false
      }
    },
  },
})
