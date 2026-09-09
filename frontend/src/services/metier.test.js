import { afterEach, describe, expect, it, vi } from 'vitest'

import { apiClient } from '../api/http.js'
import { preparationApi } from './preparation.js'
import { programmeApi } from './programme.js'

describe('programme and preparation APIs', () => {
  afterEach(() => vi.restoreAllMocks())

  it('uses grouped programme endpoints and media types', async () => {
    const get = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { member: [] } })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { id: 'p' } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 'p' } })
    const remove = vi.spyOn(apiClient, 'delete').mockResolvedValue({})

    await programmeApi.list({ date: '2026-09-10' })
    await programmeApi.get({
      date: '2026-09-10',
      salle: 'Bloc 1',
      chirurgien: 2,
    })
    await programmeApi.create({ chirurgieModeleIds: [1] })
    await programmeApi.reorder({
      date: '2026-09-10',
      salle: 'Bloc 1',
      chirurgien: 2,
      chirurgieIds: [4, 3],
    })
    await programmeApi.removeSurgery(4)

    expect(get).toHaveBeenNthCalledWith(1, '/programmes-operatoires', {
      params: { date: '2026-09-10' },
    })
    expect(get).toHaveBeenNthCalledWith(
      2,
      '/programmes-operatoires/2026-09-10/Bloc%201/2',
    )
    expect(post).toHaveBeenCalledWith(
      '/programmes-operatoires',
      expect.any(Object),
      expect.objectContaining({
        headers: { 'Content-Type': 'application/ld+json' },
      }),
    )
    expect(patch).toHaveBeenCalledWith(
      '/programmes-operatoires/2026-09-10/Bloc%201/2/ordre',
      { chirurgieIds: [4, 3] },
      expect.any(Object),
    )
    expect(remove).toHaveBeenCalledWith('/chirurgies-planifiees/4')
  })

  it('uses preparation transition and final-view endpoints', async () => {
    const get = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { id: 7 } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 8 } })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { valide: true } })

    await preparationApi.get(7)
    await preparationApi.setState(8, { coche: true, absent: false })
    await preparationApi.validate(7)
    await preparationApi.getFinalView(7)

    expect(get).toHaveBeenNthCalledWith(
      1,
      '/chirurgies-planifiees/7/preparation',
    )
    expect(patch).toHaveBeenCalledWith(
      '/preparations-materiel/8/cocher',
      { coche: true, absent: false },
      expect.any(Object),
    )
    expect(post).toHaveBeenCalledWith('/chirurgies-planifiees/7/validation')
    expect(get).toHaveBeenNthCalledWith(
      2,
      '/chirurgies-planifiees/7/vue-finale',
    )
  })
})
