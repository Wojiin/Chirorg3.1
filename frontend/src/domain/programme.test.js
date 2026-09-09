import { describe, expect, it } from 'vitest'

import {
  normalizeFinalView,
  normalizePreparation,
  normalizeProgramme,
  normalizeProgrammes,
  normalizeSurgery,
  programmeDetailRoute,
} from './programme.js'

describe('programme domain', () => {
  it('normalizes a programme and derives preparation progress', () => {
    const programme = normalizeProgramme({
      date: '2026-09-10',
      salle: 'Salle A',
      chirurgien: { id: 4, prenom: 'Claire', nom: 'Martin' },
      chirurgies: [
        {
          id: 8,
          preparationsMateriel: [
            { coche: true, absent: false },
            { coche: false, absent: true },
          ],
        },
      ],
    })

    expect(programme.id).toBe('2026-09-10|Salle A|4')
    expect(programme.chirurgies[0]).toMatchObject({
      dateProgrammee: '2026-09-10',
      salle: 'Salle A',
      progressionPreparation: {
        total: 2,
        coches: 1,
        absents: 1,
        traites: 2,
        complete: true,
      },
    })
    expect(normalizeProgrammes([programme])).toHaveLength(1)
    expect(programmeDetailRoute(programme)).toEqual({
      name: 'programme-detail',
      params: { date: '2026-09-10', salle: 'Salle A', chirurgien: 4 },
    })
  })

  it('preserves API progress and normalizes preparation material types', () => {
    const progress = {
      total: 1,
      coches: 0,
      absents: 0,
      traites: 0,
      complete: false,
    }
    expect(
      normalizeSurgery({ progressionPreparation: progress }),
    ).toMatchObject({
      progressionPreparation: progress,
    })
    const preparation = normalizePreparation({
      id: 9,
      valide: false,
      preparationsMateriel: [{ id: 1, materiel: { id: 2, type: 'Optique' } }],
    })
    expect(preparation.chirurgie.etatValidation).toBe('EN_PREPARATION')
    expect(preparation.preparations[0].materiel.typeMateriel).toBe('Optique')
  })

  it('normalizes the final read-only view', () => {
    const view = normalizeFinalView({
      id: 3,
      valide: true,
      validePar: { email: 'user@chirorg.test' },
      materielsValides: [{ id: 1, intitule: 'Boîte', type: 'Instrument' }],
      ficheTechnique: [
        { id: 2, titre: 'Pose', contenu: 'Installer', image: '/x.jpg' },
      ],
    })
    expect(view.chirurgie.valide).toBe(true)
    expect(view.materiels[0].typeMateriel).toBe('Instrument')
    expect(view.fichesTechniques[0]).toMatchObject({
      description: 'Installer',
      lienImage: '/x.jpg',
    })
  })
})
