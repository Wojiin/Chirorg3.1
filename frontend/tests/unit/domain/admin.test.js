import { describe, expect, it } from 'vitest'
import {
  getAdminListFilterConfig,
  getAdminListFilterParams,
  getMaterialsForSurgeon,
} from '@/domain/adminFilters'
import { getAdminFormFields } from '@/config/adminForms'
import { buildAdminPayload, createAdminForm } from '@/mappers/admin'

describe('speciality filters and material-list form', () => {
  it('configures server-side filters for surgery models and specialities', () => {
    expect(getAdminListFilterConfig('chirurgie-modeles')).toEqual({
      speciality: true,
    })
    expect(
      getAdminListFilterParams('chirurgie-modeles', { specialityId: '7' }),
    ).toEqual({
      specialite: '7',
      chirurgien: undefined,
    })
    expect(getAdminListFilterParams('specialites')).toEqual({
      specialite: undefined,
      chirurgien: undefined,
      masquerSansSpecialite: true,
    })
  })

  it('normalizes selected material names into API relations', () => {
    const fields = getAdminFormFields('listes-materiel', {
      materiels: [
        { id: 8, intitule: 'Pince', specialite: { intitule: 'Digestif' } },
      ],
    })
    const form = createAdminForm(fields, {
      intitule: 'Liste test',
      chirurgien: { id: 2 },
      chirurgieModele: { id: 3 },
      materiels: [{ id: 8, intitule: 'Pince' }],
    })

    expect(form.materiels).toEqual([8])
    expect(buildAdminPayload(form).materiels).toEqual(['/api/materiels/8'])
  })

  it('normalizes API Platform IRIs when editing a material list', () => {
    const fields = getAdminFormFields('listes-materiel')
    const form = createAdminForm(fields, {
      intitule: 'Liste existante',
      chirurgien: '/api/chirurgiens/2',
      chirurgieModele: { '@id': '/api/chirurgie-modeles/3' },
      materiels: ['/api/materiels/8', { '@id': '/api/materiels/9' }],
    })

    expect(form.chirurgien).toBe(2)
    expect(form.chirurgieModele).toBe(3)
    expect(form.materiels).toEqual([8, 9])
  })

  it('only offers materials from the selected surgeon speciality', () => {
    const surgeons = [{ id: 5, specialite: { id: 12, intitule: 'Orthopédie' } }]
    const materials = [
      {
        id: 1,
        intitule: 'Broche',
        specialite: { id: 12, intitule: 'Orthopédie' },
      },
      {
        id: 2,
        intitule: 'Valve',
        specialite: { id: 13, intitule: 'Cardiologie' },
      },
    ]

    expect(getMaterialsForSurgeon(materials, surgeons, '5')).toEqual([
      materials[0],
    ])
    expect(getMaterialsForSurgeon(materials, surgeons, '')).toEqual([])
  })
})
