import { describe, expect, it } from 'vitest'

import { unwrapCollection } from './admin.js'

describe('admin API collection normalization', () => {
  it.each([
    [[{ id: 1 }], [{ id: 1 }]],
    [{ member: [{ id: 2 }] }, [{ id: 2 }]],
    [{ 'hydra:member': [{ id: 3 }] }, [{ id: 3 }]],
  ])('supports API Platform collection formats', (payload, expected) => {
    expect(unwrapCollection(payload)).toEqual(expected)
  })
})
