import { describe, expect, it } from 'vitest'

import { adminResources, getAdminResource } from './adminResources.js'

describe('admin resources', () => {
  it('declares the seven resources exposed by the backend', () => {
    expect(adminResources.map(({ slug }) => slug)).toEqual([
      'specialites',
      'chirurgiens',
      'chirurgie-modeles',
      'materiels',
      'fiches-techniques',
      'listes-materiel',
      'utilisateurs',
    ])
  })

  it('rejects an unknown resource', () => {
    expect(getAdminResource('inconnu')).toBeNull()
  })
})
