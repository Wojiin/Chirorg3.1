export function normalizeSurgery(data, programme = {}) {
  const rows = data.preparationsMateriel ?? []
  return {
    ...data,
    dateProgrammee: data.dateProgrammee ?? programme.date ?? '',
    salle: data.salle ?? programme.salle ?? '',
    chirurgien: data.chirurgien ?? programme.chirurgien ?? null,
    progressionPreparation: data.progressionPreparation ?? {
      total: rows.length,
      coches: rows.filter((item) => item.coche).length,
      absents: rows.filter((item) => item.absent).length,
      traites: rows.filter((item) => item.coche || item.absent).length,
      complete:
        rows.length > 0 && rows.every((item) => item.coche || item.absent),
    },
  }
}

export function normalizeProgramme(data) {
  return {
    ...data,
    id:
      data.id ??
      `${data.date}|${data.salle}|${data.chirurgien?.id ?? 'inconnu'}`,
    chirurgies: (data.chirurgies ?? []).map((item) =>
      normalizeSurgery(item, data),
    ),
  }
}

export function normalizeProgrammes(items) {
  return items.map(normalizeProgramme)
}

export function normalizePreparation(data) {
  return {
    chirurgie: normalizeSurgery({
      id: data.id,
      dateProgrammee: data.dateProgrammee,
      salle: data.salle,
      ordre: data.ordre,
      valide: data.valide,
      etatValidation:
        data.etatValidation ?? (data.valide ? 'VALIDEE' : 'EN_PREPARATION'),
      chirurgien: data.chirurgien,
      chirurgieModele: data.chirurgieModele,
    }),
    preparations: (data.preparationsMateriel ?? []).map((item) => ({
      ...item,
      materiel: {
        ...item.materiel,
        typeMateriel: item.materiel?.typeMateriel ?? item.materiel?.type ?? '',
      },
    })),
    progressionPreparation: data.progressionPreparation ?? {
      total: 0,
      coches: 0,
      absents: 0,
      traites: 0,
      complete: false,
    },
  }
}

export function normalizeFinalView(data) {
  return {
    chirurgie: normalizeSurgery(data),
    validePar: data.validePar ?? null,
    materiels: (data.materielsValides ?? []).map((item) => ({
      ...item,
      typeMateriel: item.typeMateriel ?? item.type ?? '',
    })),
    fichesTechniques: (data.ficheTechnique ?? []).map((item) => ({
      ...item,
      description: item.description ?? item.contenu ?? '',
      lienImage: item.lienImage ?? item.image ?? null,
    })),
  }
}

export function programmeDetailRoute(programme) {
  return {
    name: 'programme-detail',
    params: {
      date: programme.date,
      salle: programme.salle,
      chirurgien: programme.chirurgien.id,
    },
  }
}
