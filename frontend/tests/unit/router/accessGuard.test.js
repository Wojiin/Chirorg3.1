import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { installAccessGuard } from '@/router/accessGuard'
import { useAuthStore } from '@/stores/auth'

describe('route access guard', () => {
  let guard
  let authStore

  beforeEach(() => {
    setActivePinia(createPinia())
    authStore = useAuthStore()
    installAccessGuard({
      beforeEach(callback) {
        guard = callback
      },
    })
  })

  it('redirects an authenticated user away from the login screen', () => {
    authStore.token = 'token'
    authStore.user = { roles: ['ROLE_USER'] }

    expect(guard({ meta: { guestOnly: true } })).toEqual({
      name: 'programme',
    })
  })

  it('preserves the destination when authentication is required', () => {
    expect(
      guard({ meta: { requiresAuth: true }, fullPath: '/compte' }),
    ).toEqual({ name: 'login', query: { redirect: '/compte' } })
  })

  it('refuses an administration route to a standard user', () => {
    authStore.token = 'token'
    authStore.user = { roles: ['ROLE_USER'] }

    expect(guard({ meta: { requiresAdmin: true } })).toEqual({
      name: 'programme',
    })
  })

  it('allows a route matching the current permissions', () => {
    authStore.token = 'token'
    authStore.user = { roles: ['ROLE_ADMIN', 'ROLE_USER'] }

    expect(guard({ meta: { requiresAuth: true, requiresAdmin: true } })).toBe(
      true,
    )
  })
})
