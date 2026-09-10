import { describe, expect, it, vi } from 'vitest'
import { createSessionExpiredHandler } from '@/services/sessionExpiry'

describe('session expiration handling', () => {
  it('clears the session and redirects concurrent failures only once', async () => {
    let resolveRedirect
    const router = {
      currentRoute: {
        value: { name: 'programme', fullPath: '/programme?date=2030-01-15' },
      },
      replace: vi.fn(
        () =>
          new Promise((resolve) => {
            resolveRedirect = resolve
          }),
      ),
    }
    const clearSession = vi.fn()
    const onSessionExpired = createSessionExpiredHandler({
      router,
      clearSession,
    })

    const firstRedirect = onSessionExpired()
    const secondRedirect = onSessionExpired()

    expect(clearSession).toHaveBeenCalledTimes(2)
    expect(router.replace).toHaveBeenCalledTimes(1)
    expect(router.replace).toHaveBeenCalledWith({
      name: 'login',
      query: { redirect: '/programme?date=2030-01-15' },
    })
    expect(secondRedirect).toBe(firstRedirect)

    resolveRedirect()
    await firstRedirect
  })

  it('does not redirect again from the login screen', () => {
    const router = {
      currentRoute: { value: { name: 'login', fullPath: '/login' } },
      replace: vi.fn(),
    }
    const clearSession = vi.fn()

    createSessionExpiredHandler({ router, clearSession })()

    expect(clearSession).toHaveBeenCalledOnce()
    expect(router.replace).not.toHaveBeenCalled()
  })
})
