import { expect, test } from '@playwright/test'

function requiredEnvironment(name) {
  const value = process.env[name]
  if (!value)
    throw new Error(`Missing required E2E environment variable: ${name}`)
  return value
}

const adminEmail = requiredEnvironment('E2E_ADMIN_EMAIL')
const adminPassword = requiredEnvironment('E2E_ADMIN_PASSWORD')
const userEmail = requiredEnvironment('E2E_USER_EMAIL')
const userPassword = requiredEnvironment('E2E_USER_PASSWORD')

async function login(page, email, password) {
  await page.goto('/connexion')
  await page.getByLabel('Adresse email').fill(email)
  await page.getByLabel('Mot de passe').fill(password)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await expect(page).toHaveURL(/\/$/)
}

test.describe('administration ChirOrg', () => {
  test('creates, updates and deletes a speciality with confirmation', async ({
    page,
  }) => {
    const suffix = Date.now()
    const initialLabel = `Spécialité E2E ${suffix}`
    const updatedLabel = `${initialLabel} modifiée`
    await login(page, adminEmail, adminPassword)
    await page.getByRole('link', { name: 'Administration' }).click()
    await page.getByRole('link', { name: /Spécialités/ }).click()
    await page.getByRole('link', { name: 'Ajouter' }).click()
    await page.getByLabel('Intitulé').fill(initialLabel)
    await page.getByRole('button', { name: 'Enregistrer' }).click()
    await expect(page.getByRole('dialog')).toBeVisible()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Créer' })
      .click()

    let row = page.getByRole('row', { name: new RegExp(initialLabel) })
    await expect(row).toBeVisible()
    await row.getByRole('link', { name: 'Modifier' }).click()
    await page.getByLabel('Intitulé').fill(updatedLabel)
    await page.getByRole('button', { name: 'Enregistrer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Enregistrer' })
      .click()

    row = page.getByRole('row', { name: new RegExp(updatedLabel) })
    await expect(row).toBeVisible()
    await row.getByRole('button', { name: 'Supprimer' }).click()
    await expect(page.getByRole('dialog')).toContainText(updatedLabel)
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Supprimer' })
      .click()
    await expect(row).toHaveCount(0)
  })

  test('manages an active user and its role', async ({ page }) => {
    const email = `managed.${Date.now()}@chirorg.test`
    await login(page, adminEmail, adminPassword)
    await page.goto('/administration/utilisateurs')
    await page.getByRole('link', { name: 'Ajouter' }).click()
    await page.getByLabel('Email').fill(email)
    await page.getByLabel('Mot de passe').fill(`Managed-${Date.now()}-Aa!`)
    await page.getByLabel('Rôle').selectOption('ROLE_ADMIN')
    await page.getByRole('button', { name: 'Enregistrer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Créer' })
      .click()

    let row = page.getByRole('row', { name: new RegExp(email) })
    await expect(row).toContainText('Administrateur · Actif')
    await row.getByRole('link', { name: 'Modifier' }).click()
    await page.getByLabel('Compte actif').uncheck()
    await page.getByRole('button', { name: 'Enregistrer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Enregistrer' })
      .click()

    row = page.getByRole('row', { name: new RegExp(email) })
    await expect(row).toContainText('Inactif')
    await row.getByRole('button', { name: 'Supprimer' }).click()
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Supprimer' })
      .click()
    await expect(row).toHaveCount(0)
  })

  test('refuses administration to ROLE_USER', async ({ page }) => {
    await login(page, userEmail, userPassword)
    await page.goto('/administration')
    await expect(page).toHaveURL(/acces-refuse/)
    await expect(
      page.getByRole('heading', { name: 'Accès refusé' }),
    ).toBeVisible()
  })

  test('renews the session once after an expired access token response', async ({
    page,
  }) => {
    await login(page, adminEmail, adminPassword)
    let rejected = false
    let refreshRequests = 0
    page.on('request', (request) => {
      if (request.url().endsWith('/api/auth/refresh')) refreshRequests += 1
    })
    await page.route('**/api/specialites', async (route) => {
      if (!rejected && route.request().method() === 'GET') {
        rejected = true
        await route.fulfill({
          status: 401,
          contentType: 'application/problem+json',
          body: '{}',
        })
        return
      }
      await route.fallback()
    })

    await page.getByRole('link', { name: 'Administration' }).click()
    await page.getByRole('link', { name: /Spécialités/ }).click()
    await expect(
      page.getByRole('heading', { name: 'Spécialités' }),
    ).toBeVisible()
    await expect.poll(() => refreshRequests).toBe(1)
    await expect(page).toHaveURL(/administration\/specialites/)
  })
})
