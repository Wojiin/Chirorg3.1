const materialTypes = [
  'Instrument',
  'Kit',
  'Consommable',
  'Équipement',
  'Implant',
]

export const referencesByResource = {
  chirurgiens: ['specialites'],
  'chirurgie-modeles': ['specialites'],
  materiels: ['specialites'],
  'fiches-techniques': ['chirurgie-modeles'],
  'listes-materiel': ['chirurgiens', 'chirurgie-modeles', 'materiels'],
}

function optionsFrom(items, label) {
  return items
    .map((item) => ({ value: item.id, label: label(item) }))
    .sort((left, right) => left.label.localeCompare(right.label, 'fr'))
}

export function getAdminFormFields(
  resource,
  collections = {},
  editing = false,
) {
  const specialites = optionsFrom(
    collections.specialites ?? [],
    (item) => item.intitule,
  )
  const chirurgiens = optionsFrom(
    collections.chirurgiens ?? [],
    (item) => `Dr ${item.prenom} ${item.nom}`,
  )
  const chirurgies = optionsFrom(
    collections['chirurgie-modeles'] ?? [],
    (item) => item.intitule,
  )
  const materiels = optionsFrom(
    collections.materiels ?? [],
    (item) => item.intitule,
  )

  return (
    {
      specialites: [{ key: 'intitule', label: 'Intitulé', required: true }],
      chirurgiens: [
        { key: 'prenom', label: 'Prénom', required: true },
        { key: 'nom', label: 'Nom', required: true },
        {
          key: 'specialite',
          label: 'Spécialité',
          type: 'select',
          options: specialites,
          required: true,
        },
      ],
      'chirurgie-modeles': [
        { key: 'intitule', label: 'Intitulé', required: true },
        {
          key: 'specialite',
          label: 'Spécialité',
          type: 'select',
          options: specialites,
          required: true,
        },
      ],
      materiels: [
        { key: 'intitule', label: 'Intitulé', required: true },
        { key: 'adresse', label: 'Adresse de rangement' },
        {
          key: 'typeMateriel',
          label: 'Type',
          type: 'select',
          options: materialTypes,
        },
        {
          key: 'specialite',
          label: 'Spécialité',
          type: 'select',
          options: specialites,
          required: true,
        },
      ],
      'fiches-techniques': [
        { key: 'titre', label: 'Titre', required: true },
        {
          key: 'description',
          label: 'Consigne technique',
          type: 'textarea',
          required: true,
        },
        { key: 'ordre', label: 'Ordre', type: 'number', required: true },
        {
          key: 'chirurgieModele',
          label: 'Chirurgie modèle',
          type: 'select',
          options: chirurgies,
          required: true,
        },
      ],
      'listes-materiel': [
        { key: 'intitule', label: 'Intitulé', required: true },
        {
          key: 'chirurgien',
          label: 'Chirurgien',
          type: 'select',
          options: chirurgiens,
          required: true,
        },
        {
          key: 'chirurgieModele',
          label: 'Chirurgie modèle',
          type: 'select',
          options: chirurgies,
          required: true,
        },
        {
          key: 'materiels',
          label: 'Composition',
          type: 'multiselect',
          options: materiels,
          required: true,
        },
      ],
      utilisateurs: [
        { key: 'email', label: 'Email', type: 'email', required: true },
        {
          key: 'motDePasse',
          label: editing ? 'Nouveau mot de passe' : 'Mot de passe',
          type: 'password',
          required: !editing,
        },
        {
          key: 'role',
          label: 'Rôle',
          type: 'select',
          options: [
            { value: 'ROLE_USER', label: 'Utilisateur' },
            { value: 'ROLE_ADMIN', label: 'Administrateur' },
          ],
          required: true,
        },
        { key: 'actif', label: 'Compte actif', type: 'checkbox' },
      ],
    }[resource] ?? []
  )
}
