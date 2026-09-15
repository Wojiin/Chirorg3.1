const frenchCollator = new Intl.Collator('fr', { sensitivity: 'base' })

const FILTERS_BY_RESOURCE = {
  chirurgiens: { speciality: true },
  'chirurgie-modeles': { speciality: true },
  materiels: { speciality: true },
  'fiches-techniques': { speciality: true },
  'listes-materiel': { speciality: true, surgeon: true },
}

/** Centralise la configuration des filtres disponibles pour chaque liste CRUD. */
export function getAdminListFilterConfig(resource) {
  return FILTERS_BY_RESOURCE[resource] ?? {}
}

/** Retourne les référentiels nécessaires aux filtres de la ressource. */
export function getAdminListFilterReferences(resource) {
  const config = getAdminListFilterConfig(resource)
  return [
    ...(config.speciality ? ['specialites'] : []),
    ...(config.surgeon ? ['chirurgiens'] : []),
  ]
}

function sortedOptions(options) {
  return options.sort((left, right) =>
    frenchCollator.compare(left.label, right.label),
  )
}

export function getSpecialityFilterOptions(specialities) {
  return sortedOptions(
    specialities.map((speciality) => ({
      value: String(speciality.id),
      label: speciality.intitule,
    })),
  )
}

export function getSurgeonFilterOptions(surgeons) {
  return sortedOptions(
    surgeons.map((surgeon) => ({
      value: String(surgeon.id),
      label: `Dr ${surgeon.prenom} ${surgeon.nom}`,
    })),
  )
}

/** Construit les paramètres de filtrage compris par l'API des listes de matériel. */
export function getAdminListFilterParams(
  resource,
  { specialityId = '', surgeonId = '' } = {},
) {
  return {
    specialite: specialityId || undefined,
    chirurgien: surgeonId || undefined,
    ...(resource === 'specialites' ? { masquerSansSpecialite: true } : {}),
  }
}

/** Limite les matériels à la spécialité du chirurgien sélectionné. */
export function getMaterialsForSurgeon(materials, surgeons, surgeonId) {
  const surgeon = surgeons.find((item) => String(item.id) === String(surgeonId))
  const specialityId = surgeon?.specialite?.id
  if (specialityId == null) return []
  return materials.filter(
    (material) => String(material.specialite?.id) === String(specialityId),
  )
}
