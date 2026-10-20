// Ajoute un zéro devant une composante de date inférieure à dix. Dépendances utilisées : aucune dépendance importée directe.
/** Complète un nombre de date pour produire un segment ISO à deux chiffres. */
function pad(value) {
  return String(value).padStart(2, '0')
}

// Convertit une date vers le format attendu par un champ HTML de type date. Dépendances utilisées : aucune dépendance importée directe.
/** Convertit une date locale en valeur compatible avec un champ input[type=date]. */
function toDateInputValue(date) {
  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate()),
  ].join('-')
}

// Calcule la date minimale proposée lors de la planification d'une chirurgie, soit le lendemain en heure locale. Dépendances utilisées : Date, toDateInputValue.
/** Calcule la première date autorisée pour planifier : le lendemain local. */
export function getTomorrowDateValue(now = new Date()) {
  const tomorrow = new Date(now)
  tomorrow.setDate(tomorrow.getDate() + 1)
  return toDateInputValue(tomorrow)
}

// Formate la date d'un programme opératoire sans introduire de décalage de fuseau horaire. Dépendances utilisées : Date, Intl.DateTimeFormat.
/** Affiche une date sans heure, en évitant le décalage de fuseau des dates ISO simples. */
export function formatDate(value, fallback = 'Non renseignée') {
  if (!value) return fallback

  const date =
    typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)
      ? new Date(`${value}T00:00:00`)
      : new Date(value)

  if (Number.isNaN(date.getTime())) return fallback
  return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' }).format(date)
}

// Formate la date et l'heure d'une validation ou d'un contrôle de matériel selon la locale française. Dépendances utilisées : Date, Intl.DateTimeFormat.
/** Affiche un horodatage avec la date et l'heure locales. */
export function formatDateTime(value, fallback = 'Non renseignée') {
  if (!value) return fallback

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return fallback

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'long',
    timeStyle: 'short',
  }).format(date)
}
