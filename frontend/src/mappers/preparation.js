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
      etatValidation: data.etatValidation,
      chirurgien: data.chirurgien,
      chirurgieModele: data.chirurgieModele,
    },
    preparations: (data.preparationsMateriel ?? []).map((item) => ({
      ...item,
      materiel: {
        ...item.materiel,
        type: item.materiel?.typeMateriel,
      },
    })),
    progressionPreparation: data.progressionPreparation,
  }
}

/** Uniformise les champs texte et image des fiches techniques. */
export function normalizeTechnicalSheets(technicalSheets = []) {
  return technicalSheets.map((sheet) => ({
    ...sheet,
    contenu: sheet.description ?? '',
    image: sheet.lienImage ?? null,
  }))
}

/** Adapte les deux formes historiques de vue finale au même contrat d'affichage. */
export function normalizeFinalView(data) {
  return {
    chirurgie: normalizePreparation(data).chirurgie,
    validePar: data.validePar ?? null,
    materiels: data.materielsValides.map((item) => ({
      id: item.id,
      coche: true,
      cocheLe: item.cocheLe,
      materiel: {
        id: item.id,
        intitule: item.intitule,
        adresse: item.adresse,
        type: item.typeMateriel,
      },
    })),
    fichesTechniques: normalizeTechnicalSheets(data.ficheTechnique),
  }
}
