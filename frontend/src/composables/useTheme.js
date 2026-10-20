import { computed, ref } from 'vue'

const STORAGE_KEY = 'chirorg-theme'
const theme = ref('light')
let initialized = false

// Applique le thème clair ou sombre au document HTML. Dépendances utilisées : aucune dépendance importée directe.
function applyTheme(value) {
  if (typeof document === 'undefined') return
  const isDark = value === 'dark'
  document.documentElement.classList.toggle('dark', isDark)
  document.documentElement.style.colorScheme = isDark ? 'dark' : 'light'
}

// Restaure le thème enregistré ou la préférence du système. Dépendances utilisées : aucune dépendance importée directe.
/** Applique la préférence persistée, ou celle du système lors de la première visite. */
export function initializeTheme() {
  if (initialized || typeof window === 'undefined') return
  const savedTheme = window.localStorage.getItem(STORAGE_KEY)
  theme.value = ['light', 'dark'].includes(savedTheme)
    ? savedTheme
    : window.matchMedia?.('(prefers-color-scheme: dark)').matches
      ? 'dark'
      : 'light'
  applyTheme(theme.value)
  initialized = true
}

// Regroupe l'état réactif et les actions de la page consacrée au thème visuel. Dépendances utilisées : computed.
/** Expose un interrupteur clair/sombre accessible à tout le shell. */
export function useTheme() {
  initializeTheme()
  const isDark = computed(() => theme.value === 'dark')

  // Bascule entre le thème clair et le thème sombre. Dépendances utilisées : aucune dépendance importée directe.
  function toggleTheme() {
    theme.value = isDark.value ? 'light' : 'dark'
    window.localStorage.setItem(STORAGE_KEY, theme.value)
    applyTheme(theme.value)
  }

  return { isDark, theme, toggleTheme }
}
