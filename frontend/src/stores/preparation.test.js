import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { preparationApi } from '../services/preparation.js'
import { usePreparationStore } from './preparation.js'

const payload = {
  id: 5,
  valide: false,
  preparationsMateriel: [
    {
      id: 1,
      coche: false,
      absent: false,
      materiel: { id: 3, intitule: 'Boîte' },
    },
  ],
  progressionPreparation: { total: 1, coches: 0, absents: 0, traites: 0 },
}

describe('preparation store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.restoreAllMocks()
  })

  it('loads, checks material and validates a complete surgery', async () => {
    vi.spyOn(preparationApi, 'get').mockResolvedValue(payload)
    vi.spyOn(preparationApi, 'setState').mockResolvedValue({
      coche: true,
      absent: false,
    })
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({ valide: true })
    const store = usePreparationStore()
    await store.fetch(5)
    await expect(
      store.setState(store.preparation.preparations[0], 'ready'),
    ).resolves.toBe(true)
    expect(store.isResolved).toBe(true)
    await expect(store.validate()).resolves.toBe('final')
  })

  it('supports absent material and partial validation', async () => {
    vi.spyOn(preparationApi, 'get').mockResolvedValue(payload)
    vi.spyOn(preparationApi, 'setState').mockResolvedValue({
      coche: false,
      absent: true,
    })
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({ valide: false })
    const store = usePreparationStore()
    await store.fetch(5)
    await store.setState(store.preparation.preparations[0], 'absent')
    expect(store.preparation.progressionPreparation.absents).toBe(1)
    await expect(store.validate()).resolves.toBe('partial')
    expect(store.preparation.chirurgie.etatValidation).toBe(
      'VALIDATION_PARTIELLE',
    )
  })

  it('restores optimistic state and exposes API errors', async () => {
    vi.spyOn(preparationApi, 'get').mockResolvedValue(payload)
    vi.spyOn(preparationApi, 'setState').mockRejectedValue({
      response: { data: { detail: 'Conflit matériel' } },
    })
    const store = usePreparationStore()
    await store.fetch(5)
    const item = store.preparation.preparations[0]
    await expect(store.setState(item, 'ready')).resolves.toBe(false)
    expect(item.coche).toBe(false)
    expect(store.error).toBe('Conflit matériel')
  })

  it('loads the final view and handles loading failures', async () => {
    vi.spyOn(preparationApi, 'getFinalView').mockResolvedValue({
      id: 5,
      valide: true,
      materielsValides: [],
      ficheTechnique: [],
    })
    const store = usePreparationStore()
    await expect(store.fetchFinalView(5)).resolves.toMatchObject({
      chirurgie: { id: 5, valide: true },
    })
    preparationApi.getFinalView.mockRejectedValue({
      response: { data: { detail: 'Vue indisponible' } },
    })
    await expect(store.fetchFinalView(5)).resolves.toBeNull()
    expect(store.error).toBe('Vue indisponible')
  })

  it('does not validate an unresolved checklist and handles fetch failure', async () => {
    const store = usePreparationStore()
    await expect(store.validate()).resolves.toBeNull()
    vi.spyOn(preparationApi, 'get').mockRejectedValue({
      response: { data: { detail: 'Introuvable' } },
    })
    await expect(store.fetch(404)).resolves.toBeNull()
    expect(store.error).toBe('Introuvable')
  })
})
