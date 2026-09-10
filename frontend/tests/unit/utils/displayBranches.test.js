import { describe, expect, it } from 'vitest'
import { getAdminItemDetails, getAdminItemTitle } from '@/presenters/admin'
import { groupTechnicalSheets } from '@/utils/technicalSheets'
import { formatDate, formatDateTime, getTomorrowDateValue } from '@/utils/date'
import { validatePasswordChange } from '@/utils/password'

describe('display and validation fallbacks', () => {
  it.each([
    [{ intitule: 'Scalpel' }, 'Scalpel'],
    [{ prenom: 'Ada' }, 'Ada'],
    [{ nom: 'Lovelace' }, 'Lovelace'],
    [{ email: 'ada@test.fr' }, 'ada@test.fr'],
    [{ titre: 'Installation' }, 'Installation'],
    [{ id: 42 }, 'Élément #42'],
  ])('chooses the administrator item title for %j', (item, expected) => {
    expect(getAdminItemTitle(item)).toBe(expected)
  })

  it.each([
    [{ specialite: { intitule: 'Orthopédie' } }, 'Orthopédie'],
    [{ typeMateriel: 'Instrument', adresse: 'A-1' }, 'Instrument · A-1'],
    [{ adresse: 'A-1' }, 'A-1'],
    [{ roles: ['ROLE_USER', 'ROLE_ADMIN'] }, 'ROLE_USER, ROLE_ADMIN'],
    [{ chirurgien: { prenom: 'Ada', nom: 'Lovelace' } }, 'Dr Ada Lovelace'],
    [{ description: 'Installer.' }, 'Installer.'],
    [{ id: 42 }, 'Référentiel ChirOrg'],
  ])('chooses administrator details for %j', (item, expected) => {
    expect(getAdminItemDetails(item)).toBe(expected)
  })

  it('groups incomplete technical sheets with stable fallbacks and ordering', () => {
    const groups = groupTechnicalSheets([
      { id: 0, titre: 'Ignorée', chirurgieModele: null },
      {
        id: 1,
        titre: 'Zèbre',
        chirurgieModele: { intitule: null, specialite: null },
      },
      {
        id: 2,
        titre: 'Alpha',
        ordre: null,
        chirurgieModele: { intitule: null, specialite: null },
      },
      {
        id: 3,
        titre: 'Digestif',
        ordre: 1,
        chirurgieModele: {
          id: 8,
          intitule: 'Appendicectomie',
          specialite: { id: 2, intitule: 'Digestif' },
        },
      },
    ])

    expect(groups).toHaveLength(2)
    expect(groups[0].title).toBe('Appendicectomie')
    expect(groups[1].title).toBe('Chirurgie sans intitulé')
    expect(groups[1].speciality).toBe('Sans spécialité')
    expect(groups[1].items.map((item) => item.id)).toEqual([2, 1])
    expect(groupTechnicalSheets(groups[0].items, '999')).toEqual([])
  })

  it('formats valid dates and returns explicit fallbacks for invalid values', () => {
    expect(getTomorrowDateValue(new Date(2030, 0, 31))).toBe('2030-02-01')
    expect(formatDate('', 'Indisponible')).toBe('Indisponible')
    expect(formatDate('invalid')).toBe('Non renseignée')
    expect(formatDate('2030-01-15')).toContain('2030')
    expect(formatDateTime(null, 'Jamais')).toBe('Jamais')
    expect(formatDateTime('invalid')).toBe('Non renseignée')
    expect(formatDateTime('2030-01-15T10:30:00Z')).toContain('2030')
  })

  it('reports empty and mismatched password fields, then accepts a strong password', () => {
    expect(
      validatePasswordChange({
        currentPassword: '',
        newPassword: 'weak',
        newPasswordConfirmation: '',
      }),
    ).toHaveProperty('currentPassword')
    expect(
      validatePasswordChange({
        currentPassword: 'Current-2026!',
        newPassword: 'NewPassword-2026!',
        newPasswordConfirmation: 'Different-2026!',
      }),
    ).toHaveProperty('newPasswordConfirmation')
    expect(
      validatePasswordChange({
        currentPassword: 'Current-2026!',
        newPassword: 'NewPassword-2026!',
        newPasswordConfirmation: 'NewPassword-2026!',
      }),
    ).toEqual({})
  })
})
