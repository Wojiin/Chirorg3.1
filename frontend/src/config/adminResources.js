/** Liste déclarative des référentiels administrables et de leur intention métier. */
export const adminResources = [
  {
    slug: 'salles',
    label: 'Salles',
    description: 'Salles opératoires disponibles',
  },
  {
    slug: 'specialites',
    label: 'Spécialités',
    description: 'Domaines chirurgicaux de référence',
  },
  {
    slug: 'chirurgiens',
    label: 'Chirurgiens',
    description: 'Praticiens et spécialités associées',
  },
  {
    slug: 'chirurgie-modeles',
    label: 'Chirurgies modèles',
    description: 'Interventions types du bloc',
  },
  {
    slug: 'materiels',
    label: 'Matériels',
    description: 'Instruments, kits et consommables',
  },
  {
    slug: 'fiches-techniques',
    label: 'Fiches techniques',
    description: 'Consignes opératoires',
  },
  {
    slug: 'listes-materiel',
    label: 'Listes de matériel',
    description: 'Listes par chirurgien et intervention',
  },
  {
    slug: 'users',
    label: 'Utilisateurs',
    description: 'Comptes et rôles applicatifs',
  },
]

const adminResourcesBySlug = new Map(
  adminResources.map((resource) => [resource.slug, resource]),
)

/** Retourne la définition unique d'un référentiel à partir de son segment d'URL. */
export function getAdminResource(slug) {
  return adminResourcesBySlug.get(slug) ?? null
}

/** Produit le libellé d'un référentiel sans dupliquer les titres dans les vues. */
export function getAdminResourceLabel(slug, fallback = 'Référentiel') {
  return getAdminResource(slug)?.label ?? fallback
}
