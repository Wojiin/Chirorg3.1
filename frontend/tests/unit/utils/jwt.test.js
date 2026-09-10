import { describe, expect, it } from 'vitest'
import { getUserFromAccessToken } from '@/utils/jwt'

function createUnsignedTestToken(payload) {
  const encode = (value) =>
    btoa(JSON.stringify(value))
      .replace(/=/g, '')
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
  return `${encode({ alg: 'none' })}.${encode(payload)}.`
}

describe('JWT user claims', () => {
  it('extracts the identity used by the interface', () => {
    const token = createUnsignedTestToken({
      username: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    })

    expect(getUserFromAccessToken(token)).toEqual({
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    })
  })

  it('rejects malformed or incomplete payloads', () => {
    expect(getUserFromAccessToken('invalid')).toBeNull()
    expect(
      getUserFromAccessToken(
        createUnsignedTestToken({ username: 'user@chirorg.test' }),
      ),
    ).toBeNull()
  })
})
