import { beforeEach, describe, expect, it, vi } from 'vitest'
import { apiClient } from '@/api/axios'
import { accountApi } from '@/services/accountApi'
import { adminApi } from '@/services/adminApi'
import { authApi } from '@/services/authApi'
import { preparationApi } from '@/services/preparationApi'
import { programmeApi } from '@/services/programmeApi'
import { technicalSheetApi } from '@/services/technicalSheetApi'

describe('API contracts', () => {
  beforeEach(() => vi.restoreAllMocks())

  it('maps every administrator CRUD operation, including users', async () => {
    const get = vi.spyOn(apiClient, 'get').mockResolvedValue({
      data: { member: [{ id: 1 }] },
    })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { id: 2 } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 2, intitule: 'Modifié' } })
    const remove = vi.spyOn(apiClient, 'delete').mockResolvedValue({})

    await expect(adminApi.list('users', { page: 2 })).resolves.toEqual({
      items: [{ id: 1 }],
      totalItems: 1,
    })
    await adminApi.list('specialites')
    await expect(adminApi.get('users', 2)).resolves.toEqual({
      member: [{ id: 1 }],
    })
    await adminApi.get('specialites', 3)
    await expect(
      adminApi.create('users', { email: 'a@b.fr' }),
    ).resolves.toEqual({ id: 2 })
    await adminApi.create('specialites', { intitule: 'Bloc' })
    await expect(
      adminApi.update('users', 2, { actif: false }),
    ).resolves.toEqual({
      id: 2,
      intitule: 'Modifié',
    })
    await adminApi.update('specialites', 3, { intitule: 'Bloc 2' })
    await adminApi.remove('users', 2)
    await adminApi.remove('specialites', 3)

    expect(get).toHaveBeenCalledWith('/utilisateurs', expect.any(Object))
    expect(get).toHaveBeenCalledWith('/specialites/3')
    expect(post).toHaveBeenCalledWith(
      '/utilisateurs',
      { email: 'a@b.fr' },
      expect.any(Object),
    )
    expect(patch).toHaveBeenCalledWith(
      '/utilisateurs/2',
      { actif: false },
      expect.any(Object),
    )
    expect(remove).toHaveBeenCalledWith('/specialites/3')
  })

  it('uses the authentication and account endpoints', async () => {
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { token: 'jwt' } })
    const patch = vi.spyOn(apiClient, 'patch').mockResolvedValue({})

    await expect(authApi.login({ email: 'user@test.fr' })).resolves.toEqual({
      token: 'jwt',
    })
    await expect(authApi.logout()).resolves.toEqual({ token: 'jwt' })
    await accountApi.changePassword({
      currentPassword: 'old',
      newPassword: 'new',
    })

    expect(post).toHaveBeenCalledWith('/auth/login', { email: 'user@test.fr' })
    expect(post).toHaveBeenCalledWith('/auth/logout')
    expect(patch).toHaveBeenCalledWith(
      '/me/mot-de-passe',
      { motDePasseActuel: 'old', nouveauMotDePasse: 'new' },
      expect.any(Object),
    )
  })

  it('maps preparation and final-view operations', async () => {
    const get = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { id: 7 } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 8 } })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { valide: true } })

    await expect(preparationApi.getPreparation(7)).resolves.toEqual({ id: 7 })
    await expect(
      preparationApi.toggle(8, { coche: true, absent: false }),
    ).resolves.toEqual({ id: 8 })
    await expect(preparationApi.validate(7)).resolves.toEqual({ valide: true })
    await preparationApi.getFinalView(7)

    expect(get).toHaveBeenCalledWith('/chirurgies-planifiees/7/preparation')
    expect(get).toHaveBeenCalledWith('/chirurgies-planifiees/7/vue-finale')
    expect(patch).toHaveBeenCalledWith(
      '/preparations-materiel/8/cocher',
      { coche: true, absent: false },
      expect.any(Object),
    )
    expect(post).toHaveBeenCalledWith('/chirurgies-planifiees/7/validation')
  })

  it('maps programme list, detail, planning, reorder and deletion', async () => {
    const get = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { member: [{ id: 1 }] } })
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { id: 'programme' } })
    const patch = vi
      .spyOn(apiClient, 'patch')
      .mockResolvedValue({ data: { id: 'ordered' } })
    const remove = vi.spyOn(apiClient, 'delete').mockResolvedValue({})

    await expect(programmeApi.list({ date: '2030-01-01' })).resolves.toEqual({
      items: [{ id: 1 }],
      totalItems: 1,
    })
    await programmeApi.getProgramme({
      date: '2030-01-01',
      salle: 'Salle A/B',
      chirurgien: 4,
    })
    await expect(
      programmeApi.planProgram({ salle: 'Salle A' }),
    ).resolves.toEqual({ id: 'programme' })
    await expect(
      programmeApi.reorder({
        date: '2030-01-01',
        salle: 'Salle A/B',
        chirurgien: 4,
        chirurgieIds: [2, 1],
      }),
    ).resolves.toEqual({ id: 'ordered' })
    await programmeApi.deleteSurgery(2)

    expect(get).toHaveBeenCalledWith('/programmes-operatoires', {
      params: { date: '2030-01-01' },
    })
    expect(get).toHaveBeenCalledWith(
      '/programmes-operatoires/2030-01-01/Salle%20A%2FB/4',
    )
    expect(post).toHaveBeenCalledWith(
      '/programmes-operatoires',
      { salle: 'Salle A' },
      expect.any(Object),
    )
    expect(patch).toHaveBeenCalledWith(
      '/programmes-operatoires/2030-01-01/Salle%20A%2FB/4/ordre',
      { chirurgieIds: [2, 1] },
      expect.any(Object),
    )
    expect(remove).toHaveBeenCalledWith('/chirurgies-planifiees/2')
  })

  it('uploads a technical-sheet image as multipart data', async () => {
    const post = vi
      .spyOn(apiClient, 'post')
      .mockResolvedValue({ data: { url: '/uploads/image.webp' } })
    const image = new File(['image'], 'image.webp', { type: 'image/webp' })

    await expect(technicalSheetApi.uploadImage(image)).resolves.toBe(
      '/uploads/image.webp',
    )
    expect(post).toHaveBeenCalledWith(
      '/fiche-technique-images',
      expect.any(FormData),
    )
    expect(post.mock.calls[0][1].get('image')).toBe(image)
  })
})
