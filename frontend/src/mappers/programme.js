/** Uniformise une chirurgie planifiée, quelle que soit la forme du payload API reçu. */
export function normalizePlannedSurgery(data) {
  return {
    ...data,
    date: data.dateProgrammee ?? data.date ?? '',
    progressionPreparation: data.progressionPreparation ?? {
      total: data.preparationsMateriel?.length ?? 0,
      coches:
        data.preparationsMateriel?.filter((item) => item.coche).length ?? 0,
      absents:
        data.preparationsMateriel?.filter((item) => item.absent).length ?? 0,
      traites:
        data.preparationsMateriel?.filter((item) => item.coche || item.absent)
          .length ?? 0,
      complete:
        Boolean(data.preparationsMateriel?.length) &&
        data.preparationsMateriel.every((item) => item.coche || item.absent),
    },
  }
}

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

/** Normalise la représentation légère utilisée par la liste des programmes. */
function normalizeProgrammeSummary(data) {
  return {
    ...data,
    id: data.id ?? `${data.date}|${data.salle}|${data.chirurgien.id}`,
    chirurgies: data.chirurgies ?? [],
  }
}

export function normalizeProgrammeSummaries(data) {
  return data.map(normalizeProgrammeSummary)
}
