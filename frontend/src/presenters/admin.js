/** Déduit le libellé principal le plus pertinent pour une ligne d'administration. */
export function getAdminItemTitle(item) {
  if (item.intitule) return item.intitule
  if (item.prenom || item.nom)
    return `${item.prenom ?? ''} ${item.nom ?? ''}`.trim()
  if (item.email) return item.email
  if (item.titre) return item.titre
  return `Élément #${item.id}`
}

/** Déduit l'information secondaire la plus utile pour une ligne d'administration. */
export function getAdminItemDetails(item) {
  if (item.specialite?.intitule) return item.specialite.intitule
  if (item.typeMateriel || item.adresse) {
    return [item.typeMateriel, item.adresse].filter(Boolean).join(' · ')
  }
  if (item.roles) return item.roles.join(', ')
  if (item.chirurgien)
    return `Dr ${item.chirurgien.prenom} ${item.chirurgien.nom}`
  if (item.description) return item.description
  return 'Référentiel ChirOrg'
}
