import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import {
  normalizePlannedSurgery,
  normalizeProgramme,
  normalizeProgrammeSummaries,
} from '@/mappers/programme'
import { normalizeFinalView, normalizePreparation } from '@/mappers/preparation'
import { useProgrammeStore } from '@/stores/programme'
import { usePreparationStore } from '@/stores/preparation'
import { useAuthStore } from '@/stores/auth'
import { authApi } from '@/services/authApi'
import { accountApi } from '@/services/accountApi'
import { preparationApi } from '@/services/preparationApi'
import { programmeApi } from '@/services/programmeApi'
import { configureApiAuth } from '@/api/axios'

const programmeSummary = {
  id: '2030-01-15|Salle B|7',
  date: '2030-01-15',
  salle: 'Salle B',
  chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
  chirurgies: [
    { id: 99, ordre: 1, dateProgrammee: '2030-01-15', salle: 'Salle B' },
  ],
}

const preparationPayload = {
  id: 42,
  dateProgrammee: '2030-01-15',
  salle: 'Salle B',
  ordre: 1,
  nombreChirurgies: 5,
  valide: false,
  chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
  chirurgieModele: { id: 2, intitule: 'Intervention' },
  preparationsMateriel: [
    {
      id: 10,
      coche: false,
      materiel: { id: 3, intitule: 'Scalpel', typeMateriel: 'Instrument' },
    },
    {
      id: 20,
      coche: false,
      materiel: { id: 4, intitule: 'Champ', typeMateriel: 'Consommable' },
    },
  ],
  progressionPreparation: { total: 2, coches: 0, complete: false },
}

describe('ChirOrg stores with API services', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    setActivePinia(createPinia())
    const authStore = useAuthStore()
    configureApiAuth({
      getAccessToken: () => authStore.token,
      setAccessToken: (token) => authStore.setAccessToken(token),
      onSessionExpired: () => authStore.clearSession(),
    })
  })

  it('loads and filters the operating programme', async () => {
    vi.spyOn(programmeApi, 'list').mockResolvedValue([
      programmeSummary,
      {
        ...programmeSummary,
        id: '2030-01-16|Salle A|7',
        date: '2030-01-16',
        salle: 'Salle A',
      },
    ])
    const store = useProgrammeStore()
    expect(store.filters).toEqual({ date: '', room: '' })
    await store.fetchProgrammes()

    expect(store.chirurgies.length).toBe(2)
    store.setFilters({ room: 'Salle B' })
    expect(
      store.filteredChirurgies.every((item) => item.salle === 'Salle B'),
    ).toBe(true)
  })

  it('loads lightweight programme summaries without requesting every detail', async () => {
    let detailRequested = false
    const api = {
      list: async () => [
        {
          date: '2030-01-15',
          salle: 'Salle Test',
          chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
        },
      ],
      getProgramme: async () => {
        detailRequested = true
      },
    }

    const programmes = normalizeProgrammeSummaries(
      await api.list({ date: '2030-01-15' }),
    )

    expect(programmes).toHaveLength(1)
    expect(programmes[0].id).toBe('2030-01-15|Salle Test|7')
    expect(programmes[0].chirurgies).toEqual([])
    expect(detailRequested).toBe(false)
  })

  it('uses the API response as source of truth after a programme reload', async () => {
    vi.spyOn(programmeApi, 'list').mockResolvedValue([programmeSummary])
    const store = useProgrammeStore()

    store.upsertProgramme({
      id: 'temporary-programme',
      date: '2030-01-14',
      salle: 'Salle Test',
      chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
      chirurgies: [],
    })
    await store.fetchProgrammes({ date: '2030-01-15', room: 'Salle B' })

    expect(store.filteredProgrammes).toHaveLength(1)
    expect(store.filteredProgrammes[0].chirurgies[0].id).toBe(99)
    expect(
      store.programmes.some((item) => item.id === 'temporary-programme'),
    ).toBe(false)
  })

  it('ignores an obsolete programme response that finishes after a newer filter', async () => {
    let resolveFirst
    let resolveSecond
    vi.spyOn(programmeApi, 'list')
      .mockImplementationOnce(
        () =>
          new Promise((resolve) => {
            resolveFirst = resolve
          }),
      )
      .mockImplementationOnce(
        () =>
          new Promise((resolve) => {
            resolveSecond = resolve
          }),
      )
    const store = useProgrammeStore()

    const firstLoad = store.fetchProgrammes({ date: '2030-01-15', room: '' })
    const secondLoad = store.fetchProgrammes({ date: '2030-01-16', room: '' })
    resolveSecond([{ ...programmeSummary, id: 'new', date: '2030-01-16' }])
    await secondLoad
    resolveFirst([{ ...programmeSummary, id: 'old', date: '2030-01-15' }])
    await firstLoad

    expect(store.programmes.map((programme) => programme.id)).toEqual(['new'])
    expect(store.loading).toBe(false)
  })

  it('initializes authentication before protected navigation', async () => {
    const store = useAuthStore()
    store.setAccessToken('api-token')
    vi.spyOn(accountApi, 'getCurrent').mockResolvedValue({
      id: 1,
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    })

    expect(store.initialized).toBe(false)
    expect(await store.initialize()).toBe(true)
    expect(store.initialized).toBe(true)
    expect(store.isAuthenticated).toBe(true)
  })

  it('hydrates authentication from signed JWT claims without a profile request', async () => {
    const payload = btoa(
      JSON.stringify({
        username: 'admin@chirorg.test',
        roles: ['ROLE_ADMIN', 'ROLE_USER'],
      }),
    )
      .replace(/=/g, '')
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
    const token = `header.${payload}.signature`
    const meSpy = vi.spyOn(accountApi, 'getCurrent')
    const store = useAuthStore()
    store.setAccessToken(token)

    expect(await store.initialize()).toBe(true)
    expect(store.user).toEqual({
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    })
    expect(meSpy).not.toHaveBeenCalled()
  })

  it('keeps the access token in Pinia memory without browser storage', async () => {
    const storageSpy = vi.spyOn(Storage.prototype, 'setItem')
    vi.spyOn(authApi, 'login').mockResolvedValue({ token: 'memory-only-token' })
    vi.spyOn(accountApi, 'getCurrent').mockResolvedValue({
      id: 1,
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    })
    const store = useAuthStore()

    expect(
      await store.login({ email: 'admin@chirorg.test', password: 'password' }),
    ).toBe(true)
    expect(store.token).toBe('memory-only-token')
    expect(storageSpy).not.toHaveBeenCalled()
  })

  it('keeps the planned surgery identifier used by preparation links', () => {
    const chirurgie = normalizePlannedSurgery({
      id: 42,
      dateProgrammee: '2026-07-24',
      salle: 'Salle A',
      chirurgieModele: { id: 3, intitule: 'Intervention' },
      chirurgien: { id: 2, prenom: 'Jean', nom: 'Dupont' },
      progressionPreparation: { total: 2, coches: 1, complete: false },
    })

    expect(chirurgie.id).toBe(42)
    expect(chirurgie.date).toBe('2026-07-24')
    expect(chirurgie).not.toHaveProperty('heure')
    expect(chirurgie.progressionPreparation.coches).toBe(1)
  })

  it('reorders every surgery in a programme with consecutive positions', async () => {
    vi.spyOn(programmeApi, 'reorder').mockImplementation(
      async ({ chirurgieIds }) => ({
        id: '2026-07-24-Salle A-1',
        date: '2026-07-24',
        salle: 'Salle A',
        chirurgien: { id: 1, prenom: 'Jean', nom: 'Dupont' },
        chirurgies: chirurgieIds.map((id, index) => ({
          id,
          ordre: index + 1,
          dateProgrammee: '2026-07-24',
        })),
      }),
    )
    const store = useProgrammeStore()
    const programme = normalizeProgramme({
      id: '2026-07-24-Salle A-1',
      date: '2026-07-24',
      salle: 'Salle A',
      chirurgien: { id: 1, prenom: 'Jean', nom: 'Dupont' },
      chirurgies: [
        { id: 10, ordre: 1, dateProgrammee: '2026-07-24' },
        { id: 20, ordre: 2, dateProgrammee: '2026-07-24' },
      ],
    })

    store.programmes = [programme]
    expect(await store.reorderProgramme(programme, [20, 10])).toBe(true)
    expect(programme.chirurgies.map((item) => item.id)).toEqual([20, 10])
    expect(programme.chirurgies.map((item) => item.ordre)).toEqual([1, 2])
  })

  it('deletes a planned surgery and updates the programme cache', async () => {
    vi.spyOn(programmeApi, 'deleteSurgery').mockResolvedValue()
    const store = useProgrammeStore()
    const programme = normalizeProgramme({
      id: '2026-07-24-Salle A-1',
      date: '2026-07-24',
      salle: 'Salle A',
      chirurgien: { id: 1, prenom: 'Jean', nom: 'Dupont' },
      chirurgies: [
        { id: 10, ordre: 1, dateProgrammee: '2026-07-24' },
        { id: 20, ordre: 2, dateProgrammee: '2026-07-24' },
      ],
    })
    store.programmes = [programme]

    expect(await store.deleteSurgery(programme, 10)).toBe(true)
    expect(programme.chirurgies.map((item) => item.id)).toEqual([20])
    expect(store.programmes[0].chirurgies.map((item) => item.id)).toEqual([20])
    expect(store.deletingSurgeryId).toBeNull()
  })

  it('loads the selected programme detail', async () => {
    vi.spyOn(programmeApi, 'list').mockResolvedValue([programmeSummary])
    vi.spyOn(programmeApi, 'getProgramme').mockResolvedValue(programmeSummary)
    const store = useProgrammeStore()

    await store.fetchProgrammes()
    const summary = store.programmes[0]
    await store.loadProgramme({
      date: summary.date,
      salle: summary.salle,
      chirurgien: summary.chirurgien.id,
    })

    expect(store.selectedProgramme.id).toBe(summary.id)
    expect(store.selectedProgramme.chirurgies.length).toBeGreaterThan(0)
  })

  it('updates checklist progress and only completes a fully checked list', async () => {
    vi.spyOn(preparationApi, 'getPreparation').mockResolvedValue(
      preparationPayload,
    )
    vi.spyOn(preparationApi, 'toggle').mockImplementation(
      async (id, state) => ({
        ...preparationPayload.preparationsMateriel.find(
          (item) => item.id === id,
        ),
        ...state,
      }),
    )
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({
      ...preparationPayload,
      valide: true,
    })
    const store = usePreparationStore()
    await store.loadPreparation(42)

    const firstUnchecked = store.preparation.preparations.find(
      (item) => !item.coche,
    )
    const previousCount = store.preparation.progressionPreparation.coches
    await store.setMaterialState(firstUnchecked, 'ready')

    expect(store.preparation.progressionPreparation.coches).toBe(
      previousCount + 1,
    )
    expect(store.isComplete).toBe(false)

    for (const item of store.preparation.preparations.filter(
      (entry) => !entry.coche,
    )) {
      await store.setMaterialState(item, 'ready')
    }

    expect(store.isComplete).toBe(true)
    expect(await store.validateSurgery()).toBe('final')
  })

  it('keeps material details when the toggle response contains an IRI', async () => {
    vi.spyOn(preparationApi, 'getPreparation').mockResolvedValue(
      preparationPayload,
    )
    vi.spyOn(preparationApi, 'toggle').mockResolvedValue({
      id: 10,
      coche: true,
      absent: false,
      materiel: '/api/materiels/3',
    })
    const store = usePreparationStore()
    await store.loadPreparation(42)
    const item = store.preparation.preparations[0]

    await store.setMaterialState(item, 'ready')

    expect(item.materiel).toMatchObject({
      id: 3,
      intitule: 'Scalpel',
      type: 'Instrument',
    })
    expect(item.coche).toBe(true)
  })

  it('keeps an absent material editable in partial validation', async () => {
    vi.spyOn(preparationApi, 'getPreparation').mockResolvedValue(
      preparationPayload,
    )
    vi.spyOn(preparationApi, 'toggle').mockImplementation(
      async (id, state) => ({
        ...preparationPayload.preparationsMateriel.find(
          (item) => item.id === id,
        ),
        ...state,
      }),
    )
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({ valide: false })
    const store = usePreparationStore()
    await store.loadPreparation(42)

    await store.setMaterialState(store.preparation.preparations[0], 'absent')
    await store.setMaterialState(store.preparation.preparations[1], 'ready')

    expect(store.isResolved).toBe(true)
    expect(store.isComplete).toBe(false)
    expect(store.preparation.progressionPreparation.absents).toBe(1)
    expect(await store.validateSurgery()).toBe('partial')
    expect(store.isPartial).toBe(true)
  })

  it('adapts the API preparation payload to the view model', () => {
    const preparation = normalizePreparation({
      id: 42,
      dateProgrammee: '2026-07-24',
      salle: 'Salle A',
      ordre: 1,
      nombreChirurgies: 5,
      valide: false,
      chirurgien: { id: 1, prenom: 'Jean', nom: 'Dupont' },
      chirurgieModele: { id: 2, intitule: 'Intervention' },
      preparationsMateriel: [
        {
          id: 7,
          coche: true,
          materiel: { id: 3, intitule: 'Scalpel', typeMateriel: 'Instrument' },
        },
      ],
      progressionPreparation: { total: 1, coches: 1, complete: true },
    })

    expect(preparation.chirurgie.id).toBe(42)
    expect(preparation.chirurgie.nombreChirurgies).toBe(5)
    expect(preparation.preparations[0].coche).toBe(true)
    expect(preparation.preparations[0].materiel.type).toBe('Instrument')
  })

  it('adapts the flat API final-view payload to the component model', () => {
    const finalView = normalizeFinalView({
      id: 42,
      dateProgrammee: '2030-01-15',
      salle: 'Salle A',
      ordre: 1,
      valide: true,
      valideLe: '2030-01-15T10:00:00+01:00',
      chirurgien: { id: 1, prenom: 'Jean', nom: 'Dupont' },
      chirurgieModele: { id: 2, intitule: 'Intervention' },
      materielsValides: [
        {
          id: 7,
          intitule: 'Scalpel',
          adresse: 'Armoire A',
          typeMateriel: 'Instrument',
          cocheLe: '2030-01-15T09:00:00+01:00',
        },
      ],
      ficheTechnique: [
        {
          id: 8,
          titre: 'Installation',
          description: 'Installer le patient.',
          lienImage: null,
          ordre: 1,
        },
      ],
    })

    expect(finalView.chirurgie.id).toBe(42)
    expect(finalView.chirurgie.valideLe).toBe('2030-01-15T10:00:00+01:00')
    expect(finalView.materiels[0].materiel.type).toBe('Instrument')
    expect(finalView.fichesTechniques[0].contenu).toBe('Installer le patient.')
  })
})
