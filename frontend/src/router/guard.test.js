import { describe, expect, it, vi } from 'vitest'

import { createNavigationGuard } from './guard.js'

function authState(overrides = {}) {
  return {
    initialize: vi.fn().mockResolvedValue(undefined),
    isAuthenticated: false,
    roles: [],
    ...overrides,
  }
}

function target(meta = {}, fullPath = '/') {
  return { meta, fullPath }
}

describe('navigation guard', () => {
  it('redirects an anonymous visitor to login with the requested URL', async () => {
    const auth = authState()
    const guard = createNavigationGuard(auth)

    await expect(
      guard(target({ requiresAuth: true }, '/administration')),
    ).resolves.toEqual({
      name: 'connexion',
      query: { redirect: '/administration' },
    })
    expect(auth.initialize).toHaveBeenCalledOnce()
  })

  it('redirects a logged-in user away from the login page', async () => {
    const guard = createNavigationGuard(authState({ isAuthenticated: true }))

    await expect(
      guard(target({ guestOnly: true }, '/connexion')),
    ).resolves.toEqual({
      name: 'accueil',
    })
  })

  it('allows ROLE_USER to access an authenticated user route', async () => {
    const guard = createNavigationGuard(
      authState({ isAuthenticated: true, roles: ['ROLE_USER'] }),
    )

    await expect(
      guard(target({ requiresAuth: true, roles: ['ROLE_USER'] })),
    ).resolves.toBe(true)
  })

  it('sends a non-admin user to the access denied page', async () => {
    const guard = createNavigationGuard(
      authState({ isAuthenticated: true, roles: ['ROLE_USER'] }),
    )

    await expect(
      guard(
        target(
          { requiresAuth: true, roles: ['ROLE_ADMIN'] },
          '/administration',
        ),
      ),
    ).resolves.toEqual({ name: 'acces-refuse' })
  })

  it('allows ROLE_ADMIN to access the administration route', async () => {
    const guard = createNavigationGuard(
      authState({ isAuthenticated: true, roles: ['ROLE_USER', 'ROLE_ADMIN'] }),
    )

    await expect(
      guard(
        target(
          { requiresAuth: true, roles: ['ROLE_ADMIN'] },
          '/administration',
        ),
      ),
    ).resolves.toBe(true)
  })
})
