import { describe, expect, it, vi } from 'vitest'

import { apiClient } from '../api/http.js'
import { adminApi, unwrapCollection } from './admin.js'

describe('admin API collection normalization', () => {
  it.each([
    [[{ id: 1 }], [{ id: 1 }]],
    [{ member: [{ id: 2 }] }, [{ id: 2 }]],
    [{ 'hydra:member': [{ id: 3 }] }, [{ id: 3 }]],
  ])('supports API Platform collection formats', (payload, expected) => {
    expect(unwrapCollection(payload)).toEqual(expected)
  })
})

describe('admin API requests', () => {
  it('uses the expected methods and API Platform media types', async () => {
    const get = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { member: [{ id: 1 }] } })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { id: 2 } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 2 } })
    const remove = vi.spyOn(apiClient, 'delete').mockResolvedValue({})

    await expect(
      adminApi.list('specialites', { intitule: 'Cardio' }),
    ).resolves.toHaveLength(1)
    await adminApi.get('specialites', 2)
    await adminApi.create('specialites', { intitule: 'Cardiologie' })
    await adminApi.update('specialites', 2, { intitule: 'Cardiologie adulte' })
    await adminApi.remove('specialites', 2)

    expect(get).toHaveBeenNthCalledWith(
      1,
      '/specialites',
      expect.objectContaining({ params: { intitule: 'Cardio' } }),
    )
    expect(get).toHaveBeenNthCalledWith(2, '/specialites/2')
    expect(post).toHaveBeenCalledWith(
      '/specialites',
      expect.any(Object),
      expect.objectContaining({
        headers: { 'Content-Type': 'application/ld+json' },
      }),
    )
    expect(patch).toHaveBeenCalledWith(
      '/specialites/2',
      expect.any(Object),
      expect.objectContaining({
        headers: { 'Content-Type': 'application/merge-patch+json' },
      }),
    )
    expect(remove).toHaveBeenCalledWith('/specialites/2')
  })
})
