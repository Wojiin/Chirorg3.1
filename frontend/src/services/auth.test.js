import { afterEach, describe, expect, it, vi } from 'vitest'

import { apiClient, authClient } from '../api/http.js'
import { authService } from './auth.js'

describe('auth service', () => {
  afterEach(() => vi.restoreAllMocks())

  it('calls every authentication endpoint', async () => {
    const authPost = vi
      .spyOn(authClient, 'post')
      .mockResolvedValueOnce({ data: { token: 'jwt' } })
      .mockResolvedValueOnce({ data: { token: 'renewed' } })
      .mockResolvedValueOnce({ data: null })
    const apiGet = vi
      .spyOn(apiClient, 'get')
      .mockResolvedValue({ data: { id: 1 } })

    await expect(
      authService.login({ email: 'a@b.fr', password: 'secret' }),
    ).resolves.toEqual({ token: 'jwt' })
    await expect(authService.refresh()).resolves.toEqual({ token: 'renewed' })
    await expect(authService.logout()).resolves.toBeUndefined()
    await expect(authService.me()).resolves.toEqual({ id: 1 })

    expect(authPost).toHaveBeenNthCalledWith(1, '/auth/login', {
      email: 'a@b.fr',
      password: 'secret',
    })
    expect(authPost).toHaveBeenNthCalledWith(2, '/auth/refresh')
    expect(authPost).toHaveBeenNthCalledWith(3, '/auth/logout')
    expect(apiGet).toHaveBeenCalledWith('/me')
  })
})
