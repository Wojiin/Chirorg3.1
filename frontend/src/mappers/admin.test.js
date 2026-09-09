import { describe, expect, it } from 'vitest'

import { getAdminFormFields } from '../config/adminForms.js'
import { buildAdminPayload, createAdminForm } from './admin.js'

describe('admin form mapping', () => {
  it('maps API Platform relations and material collections to IRIs', () => {
    const fields = getAdminFormFields('listes-materiel', {
      chirurgiens: [{ id: 2, prenom: 'Lina', nom: 'Martin' }],
      'chirurgie-modeles': [{ id: 3, intitule: 'Pontage' }],
      materiels: [{ id: 8, intitule: 'Pince' }],
    })
    const form = createAdminForm(fields, {
      intitule: 'Liste cardiaque',
      chirurgien: { id: 2 },
      chirurgieModele: '/api/chirurgie-modeles/3',
      materiels: [{ id: 8 }],
    })

    expect(buildAdminPayload(form)).toEqual({
      intitule: 'Liste cardiaque',
      chirurgien: '/api/chirurgiens/2',
      chirurgieModele: '/api/chirurgie-modeles/3',
      materiels: ['/api/materiels/8'],
    })
  })

  it('uses the current backend contract for users', () => {
    const fields = getAdminFormFields('utilisateurs', {}, true)
    const form = createAdminForm(fields, {
      email: 'admin@chirorg.test',
      roles: ['ROLE_USER', 'ROLE_ADMIN'],
      actif: false,
    })

    expect(buildAdminPayload(form)).toEqual({
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN'],
      actif: false,
    })
  })
})
