import { beforeEach, describe, expect, it } from 'vitest'
import { useTheme } from '@/composables/useTheme'

describe('application theme', () => {
  beforeEach(() => {
    window.localStorage.clear()
    document.documentElement.classList.remove('dark')
    document.documentElement.style.colorScheme = 'light'
  })

  it('switches to a persisted dark theme and back to the contrasted light theme', () => {
    const { isDark, toggleTheme } = useTheme()

    if (isDark.value) toggleTheme()
    expect(document.documentElement.classList.contains('dark')).toBe(false)
    expect(document.documentElement.style.colorScheme).toBe('light')

    toggleTheme()
    expect(isDark.value).toBe(true)
    expect(document.documentElement.classList.contains('dark')).toBe(true)
    expect(window.localStorage.getItem('chirorg-theme')).toBe('dark')

    toggleTheme()
    expect(document.documentElement.classList.contains('dark')).toBe(false)
    expect(window.localStorage.getItem('chirorg-theme')).toBe('light')
  })
})
