import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { getAccessToken, setAccessToken } from '../api/http.js'
import { useAuthStore } from './auth.js'

const authServiceMock = vi.hoisted(() => ({
  login: vi.fn(),
  refresh: vi.fn(),
  logout: vi.fn(),
  me: vi.fn(),
}))

vi.mock('../services/auth.js', () => ({ authService: authServiceMock }))

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    window.localStorage.clear()
    setAccessToken(null)
    vi.clearAllMocks()
  })

  it('keeps the JWT in memory and persists only the profile', async () => {
    authServiceMock.login.mockResolvedValue({ token: 'jwt-secret' })
    authServiceMock.me.mockResolvedValue({
      id: 4,
      email: 'user@chirorg.local',
      roles: ['ROLE_USER'],
    })
    const store = useAuthStore()

    await store.login({ email: 'user@chirorg.local', password: 'secret' })

    expect(getAccessToken()).toBe('jwt-secret')
    expect(store.isAuthenticated).toBe(true)
    expect(JSON.parse(window.localStorage.getItem('chirorg.profile'))).toEqual({
      id: 4,
      email: 'user@chirorg.local',
      roles: ['ROLE_USER'],
    })
    expect(window.localStorage.getItem('chirorg.profile')).not.toContain(
      'jwt-secret',
    )
  })

  it('shares one refresh request between concurrent callers', async () => {
    authServiceMock.refresh.mockResolvedValue({ token: 'renewed-jwt' })
    const store = useAuthStore()

    const [first, second] = await Promise.all([
      store.refreshAccessToken(),
      store.refreshAccessToken(),
    ])

    expect(first).toBe('renewed-jwt')
    expect(second).toBe('renewed-jwt')
    expect(authServiceMock.refresh).toHaveBeenCalledTimes(1)
    expect(getAccessToken()).toBe('renewed-jwt')
  })

  it('restores the profile only after a successful cookie refresh', async () => {
    window.localStorage.setItem(
      'chirorg.profile',
      JSON.stringify({ email: 'cached@chirorg.local', roles: ['ROLE_USER'] }),
    )
    authServiceMock.refresh.mockResolvedValue({ token: 'restored-jwt' })
    authServiceMock.me.mockResolvedValue({
      email: 'fresh@chirorg.local',
      roles: ['ROLE_USER'],
    })
    const store = useAuthStore()

    await store.initialize()

    expect(store.profile.email).toBe('fresh@chirorg.local')
    expect(store.initialized).toBe(true)
    expect(store.isAuthenticated).toBe(true)
  })

  it('clears stale profile data when the refresh cookie is rejected', async () => {
    window.localStorage.setItem(
      'chirorg.profile',
      JSON.stringify({ email: 'stale@chirorg.local', roles: ['ROLE_USER'] }),
    )
    authServiceMock.refresh.mockRejectedValue(new Error('Unauthorized'))
    const store = useAuthStore()

    await store.initialize()

    expect(store.profile).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(window.localStorage.getItem('chirorg.profile')).toBeNull()
  })

  it('clears local state even when remote logout fails', async () => {
    authServiceMock.login.mockResolvedValue({ token: 'jwt-secret' })
    authServiceMock.me.mockResolvedValue({
      email: 'user@chirorg.local',
      roles: ['ROLE_USER'],
    })
    authServiceMock.logout.mockRejectedValue(new Error('Network error'))
    const store = useAuthStore()
    await store.login({ email: 'user@chirorg.local', password: 'secret' })

    await expect(store.logout()).rejects.toThrow('Network error')

    expect(store.profile).toBeNull()
    expect(getAccessToken()).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })
})
