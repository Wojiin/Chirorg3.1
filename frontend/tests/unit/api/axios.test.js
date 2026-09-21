import { beforeEach, describe, expect, it, vi } from 'vitest'

let apiClient
let refreshClient
let requestHandler
let responseSuccess
let responseFailure

function makeClient(callable = false) {
  const client = callable ? vi.fn() : {}
  client.post = vi.fn()
  client.interceptors = {
    request: { use: vi.fn((handler) => (requestHandler = handler)) },
    response: {
      use: vi.fn((success, failure) => {
        responseSuccess = success
        responseFailure = failure
      }),
    },
  }
  return client
}

async function loadAxiosModule() {
  apiClient = makeClient(true)
  refreshClient = makeClient()
  const create = vi
    .fn()
    .mockReturnValueOnce(apiClient)
    .mockReturnValueOnce(refreshClient)
  vi.doMock('axios', () => ({ default: { create } }))
  const module = await import('@/api/axios')
  return { ...module, create }
}

describe('central Axios authentication', () => {
  beforeEach(() => {
    vi.resetModules()
    vi.clearAllMocks()
    requestHandler = null
    responseSuccess = null
    responseFailure = null
  })

  it('creates credentialed clients and injects a bearer token', async () => {
    const { configureApiAuth, create } = await loadAxiosModule()
    configureApiAuth({
      getAccessToken: () => 'access-token',
      setAccessToken: vi.fn(),
      onSessionExpired: vi.fn(),
    })
    const plainConfig = requestHandler({ headers: {} })
    expect(plainConfig.headers.Authorization).toBe('Bearer access-token')
    const headers = { set: vi.fn() }
    requestHandler({ headers })
    expect(headers.set).toHaveBeenCalledWith(
      'Authorization',
      'Bearer access-token',
    )
    expect(create).toHaveBeenCalledTimes(2)
  })

  it('does not send a stale bearer token to authentication endpoints', async () => {
    const { configureApiAuth } = await loadAxiosModule()
    configureApiAuth({
      getAccessToken: () => 'revoked-token',
      setAccessToken: vi.fn(),
      onSessionExpired: vi.fn(),
    })

    const loginRequest = requestHandler({ url: '/auth/login', headers: {} })

    expect(loginRequest.headers.Authorization).toBeUndefined()
  })

  it('leaves headers untouched without a token and keeps valid callbacks', async () => {
    const { configureApiAuth } = await loadAxiosModule()
    configureApiAuth({
      getAccessToken: () => null,
      setAccessToken: 'invalid',
      onSessionExpired: null,
    })
    const config = { headers: {} }
    expect(requestHandler(config)).toBe(config)
    expect(config.headers).toEqual({})
  })

  it('shares one refresh request and stores the renewed token', async () => {
    const { configureApiAuth, refreshAccessToken } = await loadAxiosModule()
    const setAccessToken = vi.fn()
    configureApiAuth({
      getAccessToken: vi.fn(),
      setAccessToken,
      onSessionExpired: vi.fn(),
    })
    let resolveRefresh
    refreshClient.post.mockReturnValue(
      new Promise((resolve) => {
        resolveRefresh = resolve
      }),
    )
    const first = refreshAccessToken()
    const second = refreshAccessToken()
    resolveRefresh({ data: { token: 'renewed' } })
    await expect(first).resolves.toBe('renewed')
    await expect(second).resolves.toBe('renewed')
    expect(refreshClient.post).toHaveBeenCalledOnce()
    expect(setAccessToken).toHaveBeenCalledWith('renewed')
  })

  it('rejects a refresh response without a token and allows a later retry', async () => {
    const { refreshAccessToken } = await loadAxiosModule()
    refreshClient.post
      .mockResolvedValueOnce({ data: {} })
      .mockResolvedValueOnce({ data: { token: 'later' } })
    await expect(refreshAccessToken()).rejects.toThrow('aucun token')
    await expect(refreshAccessToken()).resolves.toBe('later')
    expect(refreshClient.post).toHaveBeenCalledTimes(2)
  })

  it('returns responses and ignores ineligible refresh errors', async () => {
    await loadAxiosModule()
    const response = { data: { ok: true } }
    expect(responseSuccess(response)).toBe(response)
    const errors = [
      { response: { status: 500 }, config: { url: '/items' } },
      { response: { status: 401 } },
      { response: { status: 401 }, config: { url: '/items', _retry: true } },
      { response: { status: 401 }, config: { url: '/auth/login' } },
      {
        response: { status: 401 },
        config: { url: 'https://api.test/auth/logout' },
      },
    ]
    for (const error of errors)
      await expect(responseFailure(error)).rejects.toBe(error)
    expect(refreshClient.post).not.toHaveBeenCalled()
  })

  it('refreshes one failed request and retries it with the new token', async () => {
    await loadAxiosModule()
    refreshClient.post.mockResolvedValue({ data: { token: 'new-token' } })
    apiClient.mockResolvedValue({ data: { ok: true } })
    const request = { url: '/items', headers: {} }
    await expect(
      responseFailure({ response: { status: 401 }, config: request }),
    ).resolves.toEqual({ data: { ok: true } })
    expect(request._retry).toBe(true)
    expect(request.headers.Authorization).toBe('Bearer new-token')
    expect(apiClient).toHaveBeenCalledWith(request)
  })

  it('expires the session when refresh itself fails', async () => {
    const { configureApiAuth } = await loadAxiosModule()
    const onSessionExpired = vi.fn()
    configureApiAuth({
      getAccessToken: vi.fn(),
      setAccessToken: vi.fn(),
      onSessionExpired,
    })
    const refreshError = new Error('expired')
    refreshClient.post.mockRejectedValue(refreshError)
    await expect(
      responseFailure({
        response: { status: 401 },
        config: { url: '/items', headers: { set: vi.fn() } },
      }),
    ).rejects.toBe(refreshError)
    expect(onSessionExpired).toHaveBeenCalledOnce()
  })
})
