const frenchCollator = new Intl.Collator('fr', { sensitivity: 'base' })

/** Filtre par spécialité puis regroupe et ordonne les fiches par chirurgie. */
export function groupTechnicalSheets(items, specialityId = '') {
  const groups = new Map()

  for (const item of items) {
    const surgery = item.chirurgieModele
    if (!surgery) continue
    if (specialityId && String(surgery.specialite?.id) !== String(specialityId))
      continue

    const key = String(surgery.id ?? surgery.intitule)
    if (!groups.has(key)) {
      groups.set(key, {
        id: key,
        title: surgery.intitule ?? 'Chirurgie sans intitulé',
        speciality: surgery.specialite?.intitule ?? 'Sans spécialité',
        items: [],
      })
    }
    groups.get(key).items.push(item)
  }

  return [...groups.values()]
    .map((group) => ({
      ...group,
      items: group.items.sort(
        (left, right) =>
          (left.ordre ?? 0) - (right.ordre ?? 0) ||
          frenchCollator.compare(left.titre ?? '', right.titre ?? ''),
      ),
    }))
    .sort(
      (left, right) =>
        frenchCollator.compare(left.speciality, right.speciality) ||
        frenchCollator.compare(left.title, right.title),
    )
}
