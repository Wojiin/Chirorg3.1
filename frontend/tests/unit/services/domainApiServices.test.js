import { beforeEach, describe, expect, it, vi } from 'vitest'
import { apiClient } from '@/api/axios'
import { accountApi } from '@/services/accountApi'
import { technicalSheetApi } from '@/services/technicalSheetApi'

describe('focused API services', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  it('centralizes current-account reads outside the session service', async () => {
    const profile = { id: 1, email: 'user@chirorg.test', roles: ['ROLE_USER'] }
    const get = vi.spyOn(apiClient, 'get').mockResolvedValue({ data: profile })

    await expect(accountApi.getCurrent()).resolves.toEqual(profile)
    expect(get).toHaveBeenCalledWith('/me')
  })

  it('normalizes technical-sheet collections for a surgery model', async () => {
    const sheets = [{ id: 8, titre: 'Installation', ordre: 1 }]
    const get = vi.spyOn(apiClient, 'get').mockResolvedValue({
      data: { member: sheets },
    })

    await expect(technicalSheetApi.listForSurgeryModel(2)).resolves.toEqual(
      sheets,
    )
    expect(get).toHaveBeenCalledWith('/chirurgie-modeles/2/fiches-techniques')
  })
})
