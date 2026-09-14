import { getAdminResourceLabel } from '@/config/adminResources'
import { ERROR_MESSAGES } from '@/config/errorMessages'

/** Centralise les descriptions SEO des écrans sans charger le routeur de logique d'affichage. */
const routeDescriptions = {
  login: 'Connexion sécurisée à l’intranet ChirOrg.',
  programme:
    'Consulter les programmes opératoires et leur état de préparation.',
  'programme-detail':
    'Consulter les chirurgies et l’ordre de passage d’un programme opératoire.',
  planification:
    'Créer un programme opératoire composé de plusieurs chirurgies.',
  preparation:
    'Préparer et contrôler le matériel nécessaire à une chirurgie planifiée.',
  'validation-partielle':
    'Régulariser le matériel absent avant la validation finale d’une chirurgie.',
  'vue-finale':
    'Consulter la synthèse validée d’une chirurgie et sa fiche technique.',
  admin: 'Administrer les référentiels et les utilisateurs de ChirOrg.',
  'admin-new': 'Ajouter une ressource au référentiel ChirOrg.',
  'admin-edit': 'Modifier une ressource du référentiel ChirOrg.',
  'admin-list': 'Consulter et gérer un référentiel ChirOrg.',
  account: 'Consulter le compte ChirOrg connecté et modifier son mot de passe.',
  'not-found': ERROR_MESSAGES.notFoundPageDescription,
}

const defaultDescription =
  'ChirOrg : planification des programmes opératoires et préparation du matériel chirurgical.'

/** Produit le titre métier d'une route, y compris pour les référentiels paramétrés. */
function resolvePageTitle(route) {
  const resourceLabel = getAdminResourceLabel(route.params.resource)

  if (route.name === 'admin-list') return resourceLabel
  if (route.name === 'admin-new') return `Ajouter ${resourceLabel}`
  if (route.name === 'admin-edit') return `Modifier ${resourceLabel}`

  return route.meta.title ?? 'ChirOrg'
}

/** Retourne les métadonnées HTML complètes de l'écran courant. */
export function resolvePageMetadata(route) {
  return {
    title: `${resolvePageTitle(route)} | ChirOrg`,
    description: routeDescriptions[route.name] ?? defaultDescription,
  }
}

/** Applique les métadonnées de la route après chaque navigation réussie. */
export function applyPageMetadata(route) {
  const metadata = resolvePageMetadata(route)
  document.title = metadata.title
  document
    .querySelector('meta[name="description"]')
    ?.setAttribute('content', metadata.description)
}
