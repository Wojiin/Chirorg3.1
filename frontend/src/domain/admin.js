export function itemTitle(item) {
  return (
    item.intitule ||
    item.titre ||
    item.email ||
    [item.prenom, item.nom].filter(Boolean).join(' ') ||
    `Élément #${item.id}`
  )
}

export function itemDetails(item) {
  if (item.specialite?.intitule) return item.specialite.intitule
  if (item.chirurgieModele?.intitule) return item.chirurgieModele.intitule
  if (item.chirurgien)
    return `Dr ${item.chirurgien.prenom} ${item.chirurgien.nom}`
  if (item.roles)
    return `${item.roles.includes('ROLE_ADMIN') ? 'Administrateur' : 'Utilisateur'} · ${item.actif ? 'Actif' : 'Inactif'}`
  return (
    [item.typeMateriel, item.adresse, item.description]
      .filter(Boolean)
      .join(' · ') || 'Référentiel ChirOrg'
  )
}

export function itemSpecialityId(item) {
  return (
    item.specialite?.id ??
    item.chirurgieModele?.specialite?.id ??
    item.chirurgien?.specialite?.id ??
    null
  )
}
