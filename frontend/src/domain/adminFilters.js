const frenchCollator = new Intl.Collator('fr', { sensitivity: 'base' })

const FILTERS_BY_RESOURCE = {
  chirurgiens: { speciality: true },
  'chirurgie-modeles': { speciality: true },
  materiels: { speciality: true },
  'fiches-techniques': { speciality: true },
  'listes-materiel': { speciality: true, surgeon: true },
}

// Retourne la configuration des filtres du référentiel administré. Dépendances utilisées : aucune dépendance importée directe.
/** Centralise la configuration des filtres disponibles pour chaque liste CRUD. */
export function getAdminListFilterConfig(resource) {
  return FILTERS_BY_RESOURCE[resource] ?? {}
}

// Retourne les référentiels nécessaires aux filtres d'administration. Dépendances utilisées : aucune dépendance importée directe.
/** Retourne les spécialités et les chirurgiens nécessaires aux filtres du référentiel administratif affiché. */
export function getAdminListFilterReferences(resource) {
  const config = getAdminListFilterConfig(resource)
  return [
    ...(config.speciality ? ['specialites'] : []),
    ...(config.surgeon ? ['chirurgiens'] : []),
  ]
}

// Trie les options du référentiel par libellé. Dépendances utilisées : aucune dépendance importée directe.
function sortedOptions(options) {
  return options.sort((left, right) =>
    frenchCollator.compare(left.label, right.label),
  )
}

// Construit les options du filtre par spécialité. Dépendances utilisées : aucune dépendance importée directe.
export function getSpecialityFilterOptions(specialities) {
  return sortedOptions(
    specialities.map((speciality) => ({
      value: String(speciality.id),
      label: speciality.intitule,
    })),
  )
}

// Construit les options du filtre par chirurgien. Dépendances utilisées : aucune dépendance importée directe.
export function getSurgeonFilterOptions(surgeons) {
  return sortedOptions(
    surgeons.map((surgeon) => ({
      value: String(surgeon.id),
      label: `Dr ${surgeon.prenom} ${surgeon.nom}`,
    })),
  )
}

// Construit les paramètres API des filtres d'administration. Dépendances utilisées : aucune dépendance importée directe.
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

// Retourne les matériels associés au chirurgien sélectionné. Dépendances utilisées : aucune dépendance importée directe.
/** Limite les matériels à la spécialité du chirurgien sélectionné. */
export function getMaterialsForSurgeon(materials, surgeons, surgeonId) {
  const surgeon = surgeons.find((item) => String(item.id) === String(surgeonId))
  const specialityId = surgeon?.specialite?.id
  if (specialityId == null) return []
  return materials.filter(
    (material) => String(material.specialite?.id) === String(specialityId),
  )
}
