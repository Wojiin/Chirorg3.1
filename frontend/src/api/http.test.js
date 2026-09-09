import { afterEach, describe, expect, it, vi } from 'vitest'

import {
  apiClient,
  configureUnauthorizedHandler,
  getAccessToken,
  setAccessToken,
} from './http.js'

const originalAdapter = apiClient.defaults.adapter

describe('HTTP client', () => {
  afterEach(() => {
    apiClient.defaults.adapter = originalAdapter
    configureUnauthorizedHandler(null)
    setAccessToken(null)
  })

  it('keeps the JWT in memory and sends it as a bearer token', async () => {
    apiClient.defaults.adapter = vi.fn(async (config) => ({
      data: config.headers.Authorization,
      status: 200,
      statusText: 'OK',
      headers: {},
      config,
    }))
    setAccessToken('secret-jwt')

    expect(getAccessToken()).toBe('secret-jwt')
    await expect(apiClient.get('/me')).resolves.toMatchObject({
      data: 'Bearer secret-jwt',
    })
    setAccessToken('')
    expect(getAccessToken()).toBeNull()
  })

  it('refreshes once after a 401 then retries the original request', async () => {
    let attempts = 0
    const refresh = vi.fn(async () => setAccessToken('renewed-jwt'))
    configureUnauthorizedHandler(refresh)
    apiClient.defaults.adapter = vi.fn(async (config) => {
      attempts += 1
      if (attempts === 1) {
        return Promise.reject({ config, response: { status: 401 } })
      }
      return { data: 'ok', status: 200, statusText: 'OK', headers: {}, config }
    })

    await expect(apiClient.get('/protected')).resolves.toMatchObject({
      data: 'ok',
    })
    expect(refresh).toHaveBeenCalledOnce()
    expect(attempts).toBe(2)
  })

  it('does not retry authentication requests or an already retried request', async () => {
    const refresh = vi.fn()
    configureUnauthorizedHandler(refresh)
    apiClient.defaults.adapter = vi.fn(async (config) =>
      Promise.reject({ config, response: { status: 401 } }),
    )

    await expect(apiClient.get('/auth/login')).rejects.toBeDefined()
    await expect(
      apiClient.get('/protected', { _refreshAttempted: true }),
    ).rejects.toBeDefined()
    expect(refresh).not.toHaveBeenCalled()
  })
})
