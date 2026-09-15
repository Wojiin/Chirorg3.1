/** Catalogue unique des messages d'erreur, validations et replis affichés par la SPA. */
export const ERROR_MESSAGES = Object.freeze({
  generic: 'Une erreur est survenue.',
  invalidRequest: 'La requête envoyée est invalide.',
  sessionExpired: 'Votre session a expiré. Veuillez vous reconnecter.',
  accessDenied: 'Vous n’avez pas les droits nécessaires pour cette action.',
  resourceNotFound: 'La ressource demandée est introuvable.',
  methodNotAllowed: 'Cette action n’est pas autorisée.',
  serviceUnavailable: 'Le service est momentanément indisponible.',
  refreshTokenMissing: 'La réponse de renouvellement ne contient aucun token.',
  invalidCredentials: 'Email ou mot de passe incorrect.',
  referencesLoad: 'Impossible de charger les référentiels.',
  adminListLoad: 'Impossible de charger ce référentiel.',
  adminItemLoad: 'Impossible de charger cette ressource.',
  adminSave: 'L’enregistrement a échoué.',
  adminDelete: 'Cette ressource ne peut pas être supprimée.',
  unknownAdminResource: 'Ce référentiel n’existe pas.',
  programmeListLoad: 'Impossible de charger le programme opératoire.',
  programmeDetailLoad: 'Impossible de charger le détail du programme.',
  programmePlan: 'Le programme n’a pas pu être planifié.',
  programmeReorder: 'Le nouvel ordre n’a pas pu être enregistré.',
  surgeryAdd: 'La chirurgie n’a pas pu être ajoutée au programme.',
  surgeryModelRequired: 'Sélectionnez une chirurgie modèle.',
  surgeryDelete: 'La chirurgie n’a pas pu être supprimée.',
  validatedSurgeryDelete: 'Une chirurgie validée ne peut pas être supprimée.',
  preparationLoad: 'Impossible de charger la préparation.',
  materialUpdate: 'La mise à jour du matériel a échoué.',
  surgeryValidation: 'La validation de la chirurgie a échoué.',
  finalViewLoad: 'Impossible de charger la vue finale.',
  technicalSheetsLoad: 'Impossible de charger les fiches techniques.',
  passwordChange: 'Le mot de passe n’a pas pu être modifié.',
  currentPasswordRequired: 'Le mot de passe actuel est obligatoire.',
  passwordConfirmationRequired: 'La confirmation est obligatoire.',
  passwordConfirmationMismatch:
    'La confirmation ne correspond pas au nouveau mot de passe.',
  imageType: 'Seules les images JPEG, PNG et WebP sont acceptées.',
  imageSize: 'L’image ne peut pas dépasser 5 Mo.',
  imageUpload: 'L’image n’a pas pu être téléversée.',
  technicalSheetContent: 'Ajoutez une consigne écrite, une image ou les deux.',
  planningRequired:
    'La spécialité, le chirurgien, la date, la salle et chaque modèle de chirurgie sont obligatoires.',
  planningSpecialiteMismatch:
    'Le chirurgien et les chirurgies doivent correspondre à la spécialité sélectionnée.',
  planningDate: 'La date du programme doit être au minimum celle de demain.',
  preparationUnresolved:
    'Tout le matériel doit être déclaré prêt ou absent avant de valider.',
  notFoundCode: 'Erreur 404',
  notFoundPageTitle: 'Page introuvable',
  notFoundTitle: 'Cette page n’existe pas',
  notFoundDescription:
    'L’adresse demandée est incorrecte ou l’écran a été déplacé.',
  notFoundPageDescription:
    'La page demandée est introuvable dans l’intranet ChirOrg.',
  passwordChangeNotification: 'Modification impossible',
  uploadNotification: 'Téléversement impossible',
})

/** Messages techniques connus des bibliothèques, remplacés avant affichage. */
export const TECHNICAL_API_MESSAGES = Object.freeze([
  'Access Denied.',
  'Invalid credentials.',
  'JWT Token not found',
  'Not Found',
  'Syntax error',
])

export const TECHNICAL_API_MESSAGE_PREFIXES = Object.freeze([
  'No route found for',
])

export function requiredFieldMessage(label) {
  return `Le champ « ${label} » est obligatoire.`
}
