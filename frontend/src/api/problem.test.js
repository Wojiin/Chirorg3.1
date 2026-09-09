import { describe, expect, it } from 'vitest'

import { apiErrorMessage } from './problem.js'

describe('apiErrorMessage', () => {
  it('formats API Platform validation violations', () => {
    const error = {
      response: {
        data: {
          violations: [
            { propertyPath: 'email', message: 'Adresse invalide.' },
            { propertyPath: 'password', message: 'Mot de passe requis.' },
          ],
        },
      },
    }

    expect(apiErrorMessage(error)).toBe(
      'email : Adresse invalide. · password : Mot de passe requis.',
    )
  })

  it('uses the RFC 7807 detail before the generic fallback', () => {
    expect(
      apiErrorMessage({ response: { data: { detail: 'Accès refusé.' } } }),
    ).toBe('Accès refusé.')
    expect(apiErrorMessage({}, 'Indisponible.')).toBe('Indisponible.')
  })
})
