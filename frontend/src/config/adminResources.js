export const adminResources = [
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
    description: 'Consignes opératoires ordonnées',
  },
  {
    slug: 'listes-materiel',
    label: 'Listes de matériel',
    description: 'Listes par chirurgien et intervention',
  },
  {
    slug: 'utilisateurs',
    label: 'Utilisateurs',
    description: 'Comptes, rôles et états d’accès',
  },
]

const resourcesBySlug = new Map(
  adminResources.map((resource) => [resource.slug, resource]),
)

export function getAdminResource(slug) {
  return resourcesBySlug.get(slug) ?? null
}
