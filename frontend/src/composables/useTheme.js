import { computed, ref } from 'vue'

const STORAGE_KEY = 'chirorg-theme'
const theme = ref('light')
let initialized = false

function applyTheme(value) {
  if (typeof document === 'undefined') return
  const isDark = value === 'dark'
  document.documentElement.classList.toggle('dark', isDark)
  document.documentElement.style.colorScheme = isDark ? 'dark' : 'light'
}

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

/** Expose un interrupteur clair/sombre accessible à tout le shell. */
export function useTheme() {
  initializeTheme()
  const isDark = computed(() => theme.value === 'dark')

  function toggleTheme() {
    theme.value = isDark.value ? 'light' : 'dark'
    window.localStorage.setItem(STORAGE_KEY, theme.value)
    applyTheme(theme.value)
  }

  return { isDark, theme, toggleTheme }
}
