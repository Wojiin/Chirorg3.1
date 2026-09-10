/** Source unique des destinations affichées dans les navigations desktop et mobile. */
export const navigationItems = [
  {
    label: 'Programme',
    shortLabel: 'Programme',
    to: '/programme',
    symbol: 'P',
  },
  {
    label: 'Planifier un programme',
    shortLabel: 'Planifier',
    to: '/planifier',
    symbol: '+',
  },
  {
    label: 'Administration',
    shortLabel: 'Admin',
    to: '/admin',
    symbol: 'A',
    requiresAdmin: true,
  },
  {
    label: 'Compte',
    shortLabel: 'Compte',
    to: '/compte',
    symbol: 'C',
  },
]

/** Construit la destination stable du programme auquel appartient une chirurgie. */
export function getProgrammeDetailRoute(surgery) {
  return {
    name: 'programme-detail',
    params: {
      date: surgery.date ?? surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgien: surgery.chirurgien.id,
    },
  }
}
