import { describe, expect, it } from 'vitest'
import { groupTechnicalSheets } from '@/utils/technicalSheets'
import { getSpecialityFilterOptions } from '@/domain/adminFilters'

const sheets = [
  {
    id: 3,
    titre: 'Fermeture',
    ordre: 2,
    chirurgieModele: {
      id: 20,
      intitule: 'Arthroscopie',
      specialite: { id: 2, intitule: 'Orthopédie' },
    },
  },
  {
    id: 1,
    titre: 'Installation',
    ordre: 1,
    chirurgieModele: {
      id: 20,
      intitule: 'Arthroscopie',
      specialite: { id: 2, intitule: 'Orthopédie' },
    },
  },
  {
    id: 2,
    titre: 'Préparation',
    ordre: 1,
    chirurgieModele: {
      id: 10,
      intitule: 'Appendicectomie',
      specialite: { id: 1, intitule: 'Digestif' },
    },
  },
]

describe('technical sheet administration grouping', () => {
  it('offers unique speciality filters in alphabetical order', () => {
    expect(
      getSpecialityFilterOptions([
        { id: 3, intitule: 'Cardiologie' },
        { id: 2, intitule: 'Orthopédie' },
        { id: 1, intitule: 'Digestif' },
      ]),
    ).toEqual([
      { value: '3', label: 'Cardiologie' },
      { value: '1', label: 'Digestif' },
      { value: '2', label: 'Orthopédie' },
    ])
  })

  it('filters by speciality and groups sheets by surgery in step order', () => {
    const groups = groupTechnicalSheets(sheets, '2')

    expect(groups).toHaveLength(1)
    expect(groups[0].title).toBe('Arthroscopie')
    expect(groups[0].items.map((sheet) => sheet.id)).toEqual([1, 3])
  })
})
