import { describe, expect, it } from 'vitest'
import { getApiErrorMessage } from '@/api/response'
import { ERROR_MESSAGES } from '@/config/errorMessages'

describe('API error messages', () => {
  it('preserves business messages returned by API Platform', () => {
    expect(
      getApiErrorMessage({
        response: { data: { detail: 'Cette ressource est utilisée.' } },
      }),
    ).toBe('Cette ressource est utilisée.')
    expect(
      getApiErrorMessage({
        response: { data: { 'hydra:description': 'Violation métier.' } },
      }),
    ).toBe('Violation métier.')
  })

  it.each([
    [400, 'Syntax error', ERROR_MESSAGES.invalidRequest],
    [403, 'Access Denied.', ERROR_MESSAGES.accessDenied],
    [404, 'Not Found', ERROR_MESSAGES.resourceNotFound],
    [405, 'No route found for POST /items', ERROR_MESSAGES.methodNotAllowed],
    [500, 'Syntax error', ERROR_MESSAGES.serviceUnavailable],
    [503, '', ERROR_MESSAGES.serviceUnavailable],
  ])('normalizes technical status %i responses', (status, detail, expected) => {
    expect(getApiErrorMessage({ response: { status, data: { detail } } })).toBe(
      expected,
    )
  })

  it('uses the contextual login fallback for invalid credentials', () => {
    expect(
      getApiErrorMessage(
        {
          response: {
            status: 401,
            data: { message: 'Invalid credentials.' },
          },
        },
        ERROR_MESSAGES.invalidCredentials,
      ),
    ).toBe(ERROR_MESSAGES.invalidCredentials)
  })

  it('uses a safe default for an unauthenticated request and a network error', () => {
    expect(
      getApiErrorMessage({
        response: { status: 401, data: { message: 'JWT Token not found' } },
      }),
    ).toBe(ERROR_MESSAGES.sessionExpired)
    expect(getApiErrorMessage({})).toBe(ERROR_MESSAGES.generic)
  })
})
