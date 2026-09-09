import { describe, expect, it } from 'vitest'

import { itemDetails, itemSpecialityId, itemTitle } from './admin.js'

describe('admin item presentation', () => {
  it('presents reference and user rows', () => {
    expect(itemTitle({ prenom: 'Lina', nom: 'Martin' })).toBe('Lina Martin')
    expect(itemDetails({ roles: ['ROLE_ADMIN'], actif: true })).toBe(
      'Administrateur · Actif',
    )
  })

  it('finds direct and nested specialities', () => {
    expect(itemSpecialityId({ specialite: { id: 4 } })).toBe(4)
    expect(
      itemSpecialityId({ chirurgieModele: { specialite: { id: 7 } } }),
    ).toBe(7)
  })
})
