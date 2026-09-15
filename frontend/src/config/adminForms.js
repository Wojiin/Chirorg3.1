/** Décrit les champs administratifs et transforme leurs valeurs entre l'UI et l'API. */
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

/** Convertit un référentiel en options compatibles avec les champs select. */
function optionsFrom(items, label) {
  return items
    .map((item) => ({
      value: item.id,
      label: label(item),
    }))
    .sort((left, right) =>
      left.label.localeCompare(right.label, 'fr', { sensitivity: 'base' }),
    )
}

/** Retourne le schéma de formulaire correspondant à une ressource administrative. */
export function getAdminFormFields(resource, collections = {}) {
  const specialityOptions = optionsFrom(
    collections.specialites ?? [],
    (item) => item.intitule,
  )
  const surgeonOptions = optionsFrom(
    collections.chirurgiens ?? [],
    (item) => `Dr ${item.prenom} ${item.nom}`,
  )
  const surgeryOptions = optionsFrom(
    collections['chirurgie-modeles'] ?? [],
    (item) => item.intitule,
  )
  const materialOptions = (collections.materiels ?? [])
    .map((item) => ({
      value: item.id,
      label: item.intitule,
      meta: [item.specialite?.intitule, item.typeMateriel, item.adresse]
        .filter(Boolean)
        .join(' · '),
    }))
    .sort((left, right) =>
      left.label.localeCompare(right.label, 'fr', { sensitivity: 'base' }),
    )

  const schemas = {
    salles: [
      { key: 'intitule', label: 'Intitulé de la salle', required: true },
    ],
    specialites: [
      { key: 'intitule', label: 'Intitulé de la spécialité', required: true },
    ],
    chirurgiens: [
      { key: 'prenom', label: 'Prénom', required: true },
      { key: 'nom', label: 'Nom', required: true },
      {
        key: 'specialite',
        label: 'Spécialité',
        type: 'select',
        options: specialityOptions,
        required: true,
      },
    ],
    'chirurgie-modeles': [
      { key: 'intitule', label: 'Intitulé de la chirurgie', required: true },
      {
        key: 'specialite',
        label: 'Spécialité',
        type: 'select',
        options: specialityOptions,
        required: true,
      },
    ],
    materiels: [
      { key: 'intitule', label: 'Intitulé du matériel', required: true },
      { key: 'adresse', label: 'Adresse de rangement', required: true },
      {
        key: 'typeMateriel',
        label: 'Type de matériel',
        type: 'select',
        options: materialTypes,
        required: true,
      },
      {
        key: 'specialite',
        label: 'Spécialité',
        type: 'select',
        options: specialityOptions,
        required: true,
      },
    ],
    'fiches-techniques': [
      { key: 'titre', label: 'Titre', required: true },
      {
        key: 'description',
        label: 'Consigne technique',
        type: 'textarea',
        required: false,
      },
      {
        key: 'imageFile',
        label: 'Illustration de la consigne',
        type: 'file',
        accept: 'image/jpeg,image/png,image/webp',
        required: false,
      },
      { key: 'ordre', label: 'Ordre', type: 'number', required: true },
      {
        key: 'chirurgieModele',
        label: 'Chirurgie modèle',
        type: 'select',
        options: surgeryOptions,
        required: true,
      },
    ],
    'listes-materiel': [
      { key: 'intitule', label: 'Intitulé de la liste', required: true },
      {
        key: 'chirurgien',
        label: 'Chirurgien',
        type: 'select',
        options: surgeonOptions,
        required: true,
      },
      {
        key: 'chirurgieModele',
        label: 'Chirurgie modèle',
        type: 'select',
        options: surgeryOptions,
        required: true,
      },
      {
        key: 'materiels',
        label: 'Composition de la liste',
        type: 'material-picker',
        options: materialOptions,
        required: true,
      },
    ],
    users: [
      { key: 'email', label: 'Email', type: 'email', required: true },
      {
        key: 'password',
        label: 'Mot de passe',
        type: 'password',
        required: false,
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
    ],
  }

  return schemas[resource] ?? []
}
