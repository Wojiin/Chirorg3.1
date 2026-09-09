import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { adminApi } from '../services/admin.js'
import { adminResources } from '../config/adminResources.js'
import { useAdminStore } from './admin.js'

describe('admin store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.restoreAllMocks()
  })

  it('loads collections and individual resources', async () => {
    vi.spyOn(adminApi, 'list').mockResolvedValue([
      { id: 1, intitule: 'Cardiologie' },
    ])
    vi.spyOn(adminApi, 'get').mockResolvedValue({
      id: 1,
      intitule: 'Cardiologie',
    })
    const store = useAdminStore()

    await expect(store.loadItems('specialites')).resolves.toHaveLength(1)
    await expect(store.loadItem('specialites', 1)).resolves.toMatchObject({
      id: 1,
    })
    expect(store.loading).toBe(false)
  })

  it.each(adminResources.map(({ slug }) => slug))(
    'loads the %s resource',
    async (resource) => {
      const list = vi.spyOn(adminApi, 'list').mockResolvedValue([])
      await useAdminStore().loadItems(resource)
      expect(list).toHaveBeenCalledWith(resource, {})
    },
  )

  it('creates, updates and removes a resource', async () => {
    vi.spyOn(adminApi, 'create').mockResolvedValue({
      id: 2,
      intitule: 'Urologie',
    })
    vi.spyOn(adminApi, 'update').mockResolvedValue({
      id: 2,
      intitule: 'Urologie pédiatrique',
    })
    vi.spyOn(adminApi, 'remove').mockResolvedValue()
    const store = useAdminStore()
    store.items = [{ id: 2, intitule: 'Urologie' }]

    await expect(
      store.saveItem('specialites', null, { intitule: 'Urologie' }),
    ).resolves.toMatchObject({ id: 2 })
    await expect(
      store.saveItem('specialites', 2, { intitule: 'Urologie pédiatrique' }),
    ).resolves.toMatchObject({ id: 2 })
    await expect(store.removeItem('specialites', 2)).resolves.toBe(true)
    expect(store.items).toEqual([])
  })

  it.each([
    ['list', 'loadItems', ['specialites'], 'Impossible de charger'],
    ['get', 'loadItem', ['specialites', 404], 'introuvable'],
    [
      'create',
      'saveItem',
      ['specialites', null, {}],
      'intitule : Cette valeur ne doit pas être vide',
    ],
    ['remove', 'removeItem', ['specialites', 1], 'encore utilisée'],
  ])(
    'normalizes API errors from %s',
    async (apiMethod, storeMethod, args, message) => {
      vi.spyOn(adminApi, apiMethod).mockRejectedValue({
        response: {
          data:
            apiMethod === 'create'
              ? {
                  violations: [
                    {
                      propertyPath: 'intitule',
                      message: 'Cette valeur ne doit pas être vide',
                    },
                  ],
                }
              : { detail: message },
        },
      })
      const store = useAdminStore()

      await store[storeMethod](...args)
      expect(store.error).toContain(message)
      expect(store.loading).toBe(false)
      expect(store.saving).toBe(false)
      expect(store.deletingId).toBeNull()
    },
  )
})
