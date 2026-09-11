try {
  const savedTheme = globalThis.localStorage.getItem('chirorg-theme')
  const dark = savedTheme
    ? savedTheme === 'dark'
    : globalThis.matchMedia('(prefers-color-scheme: dark)').matches
  document.documentElement.classList.toggle('dark', dark)
  document.documentElement.style.colorScheme = dark ? 'dark' : 'light'
} catch {
  document.documentElement.style.colorScheme = 'light'
}
