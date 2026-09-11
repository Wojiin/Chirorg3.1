/** Adapte la réponse de checklist de l'API au modèle attendu par l'interface de préparation. */
export function normalizePreparation(data) {
  return {
    chirurgie: {
      id: data.id,
      dateProgrammee: data.dateProgrammee,
      date: data.dateProgrammee,
      salle: data.salle,
      ordre: data.ordre,
      nombreChirurgies: data.nombreChirurgies,
      valide: data.valide,
      valideLe: data.valideLe,
      etatValidation:
        data.etatValidation ?? (data.valide ? 'VALIDEE' : 'EN_PREPARATION'),
      chirurgien: data.chirurgien,
      chirurgieModele: data.chirurgieModele,
    },
    preparations: (data.preparationsMateriel ?? []).map((item) => ({
      ...item,
      materiel: {
        ...item.materiel,
        type: item.materiel?.type ?? item.materiel?.typeMateriel,
      },
    })),
    progressionPreparation: data.progressionPreparation,
  }
}

/** Uniformise les champs texte et image des fiches techniques. */
export function normalizeTechnicalSheets(technicalSheets = []) {
  return technicalSheets.map((sheet) => ({
    ...sheet,
    contenu: sheet.contenu ?? sheet.description ?? '',
    image: sheet.image ?? sheet.lienImage ?? null,
  }))
}

/** Adapte les deux formes historiques de vue finale au même contrat d'affichage. */
export function normalizeFinalView(data) {
  const surgerySource = data.chirurgie ?? data
  const materials = data.materiels ?? data.materielsValides ?? []
  const technicalSheets = data.fichesTechniques ?? data.ficheTechnique ?? []

  return {
    chirurgie: normalizePreparation(surgerySource).chirurgie,
    validePar: data.validePar ?? null,
    materiels: materials.map((item) => {
      if (item.materiel) {
        return {
          ...item,
          materiel: {
            ...item.materiel,
            type: item.materiel.type ?? item.materiel.typeMateriel,
          },
        }
      }

      return {
        id: item.id,
        coche: true,
        cocheLe: item.cocheLe,
        materiel: {
          id: item.id,
          intitule: item.intitule,
          adresse: item.adresse,
          type: item.typeMateriel,
        },
      }
    }),
    fichesTechniques: normalizeTechnicalSheets(technicalSheets),
  }
}
