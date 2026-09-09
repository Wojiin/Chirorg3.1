import { defineStore } from 'pinia'

import { apiErrorMessage } from '../api/problem.js'
import { normalizeProgramme, normalizeProgrammes } from '../domain/programme.js'
import { programmeApi } from '../services/programme.js'

export const useProgrammeStore = defineStore('programme', {
  state: () => ({
    programmes: [],
    selected: null,
    loading: false,
    saving: false,
    error: null,
    filters: { date: '', salle: '', chirurgien: '' },
  }),
  actions: {
    async fetch(filters = this.filters) {
      this.loading = true
      this.error = null
      this.filters = { ...this.filters, ...filters }
      try {
        const params = Object.fromEntries(
          Object.entries(this.filters).filter(([, value]) => value !== ''),
        )
        this.programmes = normalizeProgrammes(await programmeApi.list(params))
        return this.programmes
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Impossible de charger les programmes opératoires.',
        )
        return []
      } finally {
        this.loading = false
      }
    },
    async fetchOne(reference) {
      this.loading = true
      this.error = null
      try {
        this.selected = normalizeProgramme(await programmeApi.get(reference))
        return this.selected
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Impossible de charger ce programme.',
        )
        this.selected = null
        return null
      } finally {
        this.loading = false
      }
    },
    async create(payload) {
      this.saving = true
      this.error = null
      try {
        const programme = normalizeProgramme(await programmeApi.create(payload))
        this.programmes.push(programme)
        return programme
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'Le programme n’a pas pu être créé.',
        )
        return null
      } finally {
        this.saving = false
      }
    },
    async reorder(chirurgieIds) {
      if (!this.selected) return false
      const previous = [...this.selected.chirurgies]
      const rows = new Map(previous.map((item) => [Number(item.id), item]))
      this.selected.chirurgies = chirurgieIds.map((id, index) => ({
        ...rows.get(Number(id)),
        ordre: index + 1,
      }))
      this.saving = true
      this.error = null
      try {
        this.selected = normalizeProgramme(
          await programmeApi.reorder({
            date: this.selected.date,
            salle: this.selected.salle,
            chirurgien: this.selected.chirurgien.id,
            chirurgieIds,
          }),
        )
        return true
      } catch (error) {
        this.selected.chirurgies = previous
        this.error = apiErrorMessage(
          error,
          'L’ordre n’a pas pu être enregistré.',
        )
        return false
      } finally {
        this.saving = false
      }
    },
    async removeSurgery(id) {
      this.saving = true
      this.error = null
      try {
        await programmeApi.removeSurgery(id)
        if (this.selected) {
          this.selected.chirurgies = this.selected.chirurgies.filter(
            (item) => Number(item.id) !== Number(id),
          )
          this.selected.nombreChirurgies = this.selected.chirurgies.length
        }
        return true
      } catch (error) {
        this.error = apiErrorMessage(
          error,
          'La chirurgie n’a pas pu être supprimée.',
        )
        return false
      } finally {
        this.saving = false
      }
    },
  },
})
