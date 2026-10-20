// Normalise planifiée chirurgie reçu de l'API pour le programme opératoire. Dépendances utilisées : aucune dépendance importée directe.
/** Uniformise une chirurgie planifiée, quelle que soit la forme du payload API reçu. */
function normalizePlannedSurgery(data) {
  return {
    ...data,
    date: data.dateProgrammee,
  }
}

// Normalise programme opératoire reçu de l'API pour le programme opératoire. Dépendances utilisées : aucune dépendance importée directe.
/** Uniformise le détail d'un programme et propage ses informations communes aux chirurgies. */
export function normalizeProgramme(data) {
  return {
    ...data,
    chirurgies: (data.chirurgies ?? []).map((chirurgie) =>
      normalizePlannedSurgery({
        ...chirurgie,
        date: data.date,
        salle: data.salle,
        chirurgien: data.chirurgien,
      }),
    ),
  }
}

// Normalise programme opératoire résumé reçu de l'API pour le programme opératoire. Dépendances utilisées : aucune dépendance importée directe.
/** Normalise la représentation légère utilisée par la liste des programmes. */
function normalizeProgrammeSummary(data) {
  return {
    ...data,
    chirurgies: [],
  }
}

// Normalise programme opératoire summaries reçu de l'API pour le programme opératoire. Dépendances utilisées : aucune dépendance importée directe.
export function normalizeProgrammeSummaries(data) {
  return data.map(normalizeProgrammeSummary)
}
