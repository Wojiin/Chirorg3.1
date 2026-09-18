import { expect, test } from '@playwright/test'

function requiredEnvironment(name) {
  const value = process.env[name]
  if (!value)
    throw new Error(`La variable d’environnement E2E ${name} est obligatoire.`)
  return value
}

const adminEmail = requiredEnvironment('E2E_ADMIN_EMAIL')
const adminPassword = requiredEnvironment('E2E_ADMIN_PASSWORD')

async function login(page) {
  await page.goto('/login')
  await page.getByLabel('Email').fill(adminEmail)
  await page.getByLabel('Mot de passe').fill(adminPassword)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await expect(page).toHaveURL(/\/programme$/)
}

test.describe('sécurité du rendu Vue', () => {
  test('affiche une charge XSS persistée comme du texte sans l’exécuter', async ({
    page,
  }) => {
    const suffix = Date.now()
    const payload = `Spécialité E2E <img src=x onerror="globalThis.__chirorgXssExecuted=true"> ${suffix}`

    await page.addInitScript(() => {
      globalThis.__chirorgXssExecuted = false
    })
    await login(page)
    await page.goto('/admin/specialites/new')
    await page.getByLabel('Intitulé de la spécialité').fill(payload)
    await page.getByRole('button', { name: 'Enregistrer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Créer' })
      .click()

    const row = page.getByRole('row').filter({ hasText: payload })
    await expect(row).toBeVisible()
    await expect(row.locator('img')).toHaveCount(0)
    await expect
      .poll(() => page.evaluate(() => globalThis.__chirorgXssExecuted))
      .toBe(false)

    await row.getByRole('button', { name: 'Supprimer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Supprimer' })
      .click()
    await expect(row).toHaveCount(0)
  })
})
