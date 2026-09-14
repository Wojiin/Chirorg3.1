/** Déduit le libellé principal le plus pertinent pour une ligne d'administration. */
export function getAdminItemTitle(item) {
  if (item.intitule) return item.intitule
  if (item.prenom || item.nom)
    return `${item.prenom ?? ''} ${item.nom ?? ''}`.trim()
  if (item.email) return item.email
  if (item.titre) return item.titre
  return `Élément #${item.id}`
}

function relationId(relation) {
  if (relation && typeof relation === 'object') {
    return relation.id ?? relationId(relation['@id'])
  }
  if (typeof relation !== 'string') return null
  const match = relation.match(/\/(\d+)$/)
  return match ? Number(match[1]) : null
}

/** Résout une relation compacte API Platform depuis un référentiel déjà chargé. */
function resolveRelation(relation, collection = []) {
  if (
    relation &&
    typeof relation === 'object' &&
    (relation.prenom || relation.nom)
  ) {
    return relation
  }
  const id = relationId(relation)
  return collection.find((item) => Number(item.id) === Number(id)) ?? null
}

/** Déduit l'information secondaire la plus utile pour une ligne d'administration. */
export function getAdminItemDetails(item, collections = {}) {
  if (item.specialite?.intitule) return item.specialite.intitule
  if (item.typeMateriel || item.adresse) {
    return [item.typeMateriel, item.adresse].filter(Boolean).join(' · ')
  }
  if (item.roles) return item.roles.join(', ')
  if (item.chirurgien) {
    const surgeon = resolveRelation(item.chirurgien, collections.chirurgiens)
    if (surgeon) return `Dr ${surgeon.prenom} ${surgeon.nom}`
  }
  if (item.description) return item.description
  return 'Référentiel ChirOrg'
}
