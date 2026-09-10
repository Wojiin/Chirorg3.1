import { defineStore } from 'pinia'
import { programmeApi } from '@/services/programmeApi'
import { getApiErrorMessage } from '@/api/response'
import {
  normalizeProgramme,
  normalizeProgrammeSummaries,
} from '@/mappers/programme'

/** Gère les programmes, les filtres locaux, la création et le réordonnancement persistant. */
export const useProgrammeStore = defineStore('programme', {
  state: () => ({
    programmes: [],
    selectedProgramme: null,
    filters: {
      date: '',
      room: '',
    },
    pendingLoads: 0,
    listRequestId: 0,
    detailRequestId: 0,
    planning: false,
    deletingSurgeryId: null,
    savingProgrammeId: null,
    error: '',
  }),

  getters: {
    loading: (state) => state.pendingLoads > 0,
    chirurgies: (state) =>
      state.programmes.flatMap((programme) => programme.chirurgies),
    rooms: (state) =>
      [...new Set(state.programmes.map((item) => item.salle))].sort(),
    filteredProgrammes: (state) =>
      state.programmes.filter(
        (programme) =>
          (!state.filters.date || programme.date === state.filters.date) &&
          (!state.filters.room || programme.salle === state.filters.room),
      ),
    filteredChirurgies() {
      return this.filteredProgrammes.flatMap(
        (programme) => programme.chirurgies,
      )
    },
    hasActiveFilters: (state) =>
      Boolean(state.filters.date || state.filters.room),
  },

  actions: {
    /** Remplace les filtres de liste de manière atomique. */
    setFilters({ date = this.filters.date, room = this.filters.room } = {}) {
      this.filters = { date, room }
    },

    /** Réinitialise les filtres facultatifs de programme. */
    clearFilters() {
      this.filters = { date: '', room: '' }
    },

    /** Insère ou remplace un programme dans le cache local à partir de la réponse API. */
    upsertProgramme(data) {
      const programme = normalizeProgramme(data)
      const index = this.programmes.findIndex(
        (item) => item.id === programme.id,
      )

      if (index === -1) {
        this.programmes.push(programme)
      } else {
        this.programmes[index] = programme
      }

      return programme
    },

    /** Charge la liste filtrée et expose toute erreur métier à l'interface. */
    async fetchProgrammes(filters = this.filters) {
      this.setFilters(filters)
      const requestId = ++this.listRequestId
      this.pendingLoads += 1
      this.error = ''

      try {
        const params = {
          ...(this.filters.date ? { date: this.filters.date } : {}),
          ...(this.filters.room ? { salle: this.filters.room } : {}),
        }
        const programmes = normalizeProgrammeSummaries(
          await programmeApi.list(params),
        )
        if (requestId === this.listRequestId) this.programmes = programmes
        return programmes
      } catch (error) {
        if (requestId === this.listRequestId) {
          this.error = getApiErrorMessage(
            error,
            'Impossible de charger le programme opératoire.',
          )
        }
        return []
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Charge le détail d'un programme sélectionné pour sa consultation ou son réordonnancement. */
    async loadProgramme({ date, salle, chirurgien }) {
      const requestId = ++this.detailRequestId
      this.pendingLoads += 1
      this.error = ''
      this.selectedProgramme = null

      try {
        const data = await programmeApi.getProgramme({
          date,
          salle,
          chirurgien,
        })
        const programme = normalizeProgramme(data)
        if (requestId === this.detailRequestId)
          this.selectedProgramme = programme
        return programme
      } catch (error) {
        if (requestId === this.detailRequestId) {
          this.error = getApiErrorMessage(
            error,
            'Impossible de charger le détail du programme.',
          )
        }
        return null
      } finally {
        this.pendingLoads -= 1
      }
    },

    /** Planifie un programme multi-chirurgies, met à jour le cache puis cible ses filtres. */
    async planProgramme(payload) {
      this.planning = true
      this.error = ''

      try {
        const createdProgramme = await programmeApi.planProgram(payload)
        this.upsertProgramme(createdProgramme)
        this.setFilters({
          date: payload.dateProgrammee,
          room: payload.salle,
        })
        return createdProgramme
      } catch (error) {
        this.error = getApiErrorMessage(
          error,
          'Le programme n’a pas pu être planifié.',
        )
        return null
      } finally {
        this.planning = false
      }
    },

    /** Applique un ordre optimiste, le persiste côté API et le restaure en cas d'échec. */
    async reorderProgramme(programme, chirurgieIds) {
      const previousChirurgies = programme.chirurgies.map((chirurgie) => ({
        ...chirurgie,
      }))
      const byId = new Map(
        programme.chirurgies.map((chirurgie) => [
          String(chirurgie.id),
          chirurgie,
        ]),
      )
      programme.chirurgies = chirurgieIds.map((id, index) => ({
        ...byId.get(String(id)),
        ordre: index + 1,
      }))
      this.savingProgrammeId = programme.id
      this.error = ''

      try {
        const data = await programmeApi.reorder({
          date: programme.date,
          salle: programme.salle,
          chirurgien: programme.chirurgien.id,
          chirurgieIds,
        })
        Object.assign(programme, normalizeProgramme(data))
        return true
      } catch (error) {
        programme.chirurgies = previousChirurgies
        this.error = getApiErrorMessage(
          error,
          'Le nouvel ordre n’a pas pu être enregistré.',
        )
        return false
      } finally {
        this.savingProgrammeId = null
      }
    },

    /** Supprime une chirurgie non validée puis synchronise les programmes déjà chargés. */
    async deleteSurgery(programme, chirurgieId) {
      this.deletingSurgeryId = chirurgieId
      this.error = ''

      try {
        await programmeApi.deleteSurgery(chirurgieId)
        programme.chirurgies = programme.chirurgies.filter(
          (chirurgie) => String(chirurgie.id) !== String(chirurgieId),
        )
        programme.nombreChirurgies = programme.chirurgies.length
        this.programmes = this.programmes
          .map((item) => {
            if (item.id !== programme.id) return item
            return {
              ...item,
              chirurgies: programme.chirurgies,
              nombreChirurgies: programme.chirurgies.length,
            }
          })
          .filter((item) => item.chirurgies.length > 0)
        return true
      } catch (error) {
        this.error = getApiErrorMessage(
          error,
          'La chirurgie n’a pas pu être supprimée.',
        )
        return false
      } finally {
        this.deletingSurgeryId = null
      }
    },
  },
})
