import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { programmeApi } from '../services/programme.js'
import { useProgrammeStore } from './programme.js'

const programme = {
  id: 'p1',
  date: '2026-09-10',
  salle: 'Bloc 1',
  chirurgien: { id: 2 },
  nombreChirurgies: 2,
  chirurgies: [
    { id: 10, ordre: 1, preparationsMateriel: [] },
    { id: 11, ordre: 2, preparationsMateriel: [] },
  ],
}

describe('programme store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.restoreAllMocks()
  })

  it('loads filtered programmes and a detail', async () => {
    vi.spyOn(programmeApi, 'list').mockResolvedValue([programme])
    vi.spyOn(programmeApi, 'get').mockResolvedValue(programme)
    const store = useProgrammeStore()
    await store.fetch({ date: '2026-09-10', salle: '', chirurgien: '2' })
    await store.fetchOne({ date: '2026-09-10', salle: 'Bloc 1', chirurgien: 2 })
    expect(programmeApi.list).toHaveBeenCalledWith({
      date: '2026-09-10',
      chirurgien: '2',
    })
    expect(store.selected.chirurgies).toHaveLength(2)
    expect(store.loading).toBe(false)
  })

  it('creates, reorders and removes surgeries', async () => {
    vi.spyOn(programmeApi, 'create').mockResolvedValue(programme)
    vi.spyOn(programmeApi, 'reorder').mockResolvedValue({
      ...programme,
      chirurgies: [programme.chirurgies[1], programme.chirurgies[0]],
    })
    vi.spyOn(programmeApi, 'removeSurgery').mockResolvedValue()
    const store = useProgrammeStore()
    await expect(store.create({})).resolves.toMatchObject({ id: 'p1' })
    store.selected = { ...programme, chirurgies: [...programme.chirurgies] }
    await expect(store.reorder([11, 10])).resolves.toBe(true)
    await expect(store.removeSurgery(10)).resolves.toBe(true)
    expect(store.selected.chirurgies.map((item) => item.id)).toEqual([11])
  })

  it.each([
    ['list', 'fetch', [], 'charge'],
    ['get', 'fetchOne', [{}], 'charge'],
    ['create', 'create', [{}], 'créé'],
    ['reorder', 'reorder', [[11, 10]], 'ordre'],
    ['removeSurgery', 'removeSurgery', [10], 'supprimée'],
  ])('exposes API errors from %s', async (method, action, args, message) => {
    vi.spyOn(programmeApi, method).mockRejectedValue({
      response: { data: { detail: `Échec ${message}` } },
    })
    const store = useProgrammeStore()
    store.selected = { ...programme, chirurgies: [...programme.chirurgies] }
    await store[action](...args)
    expect(store.error).toContain('Échec')
    expect(store.loading).toBe(false)
    expect(store.saving).toBe(false)
  })
})
