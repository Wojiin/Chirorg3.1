import { expect, test } from '@playwright/test'

const userEmail = process.env.E2E_USER_EMAIL
const userPassword = process.env.E2E_USER_PASSWORD

test.skip(
  !userEmail || !userPassword,
  'Les identifiants E2E utilisateur doivent être fournis.',
)

test('un utilisateur planifie puis prépare une intervention', async ({
  page,
}) => {
  const daysAhead = 60 + Math.floor(Math.random() * 300)
  const date = new Date(Date.now() + daysAhead * 86400000)
    .toISOString()
    .slice(0, 10)

  await page.goto('/login')
  await page.getByLabel('Email').fill(userEmail)
  await page.getByLabel('Mot de passe').fill(userPassword)
  await page.getByRole('button', { name: 'Se connecter' }).click()

  await page
    .getByLabel('Navigation principale')
    .getByRole('link', { name: 'Planifier un programme', exact: true })
    .click()
  await page.getByLabel('Spécialité').selectOption({ label: 'Orthopédie' })
  await page
    .getByLabel('Chirurgien')
    .selectOption({ label: 'Dr Nicolas Bernard' })
  await page.getByLabel('Date programmée').fill(date)
  await page.getByLabel('Salle').selectOption('Salle C')
  await page
    .getByLabel('Chirurgie 1')
    .selectOption({ label: 'Prothèse totale de hanche' })
  await page.getByRole('button', { name: 'Planifier le programme' }).click()

  const programme = page
    .locator('article.programme-summary-card')
    .filter({ hasText: 'Salle C' })
    .filter({ hasText: 'Bernard Nicolas' })
  await expect(programme).toBeVisible()
  await programme
    .getByRole('link', { name: 'Voir le détail du programme' })
    .click()
  await expect(
    page.getByRole('heading', { name: 'Détail du programme' }),
  ).toBeVisible()

  await page
    .getByRole('button', { name: '+ Ajouter une chirurgie', exact: true })
    .click()
  const additionForm = page.locator('form').filter({
    has: page.getByRole('heading', { name: 'Ajouter une chirurgie' }),
  })
  await expect(additionForm).toContainText(date)
  await expect(additionForm).toContainText('Salle C')
  await expect(additionForm).toContainText('Dr Nicolas Bernard')
  await expect(additionForm).toContainText('Orthopédie')
  await additionForm
    .getByLabel('Chirurgie modèle')
    .selectOption({ label: 'Prothèse totale de genou' })
  await additionForm
    .getByRole('button', { name: 'Ajouter la chirurgie' })
    .click()
  await expect(page.locator('article.programme-card')).toHaveCount(2)

  const chirurgie = page
    .locator('article.programme-card')
    .filter({ hasText: 'Prothèse totale de genou' })
    .last()
  await chirurgie.getByRole('link', { name: /parer/ }).click()
  await expect(
    page.getByRole('heading', { name: 'Prothèse totale de genou' }),
  ).toBeVisible()
  const premierMateriel = await page
    .locator('.preparation-item .item-title')
    .first()
    .innerText()
  const casesPret = page.getByLabel('Prêt')
  const casesAbsent = page.getByLabel('Absent')
  const materialCount = await casesPret.count()
  for (let index = 0; index < materialCount - 1; index += 1) {
    await Promise.all([
      page.waitForResponse(
        (response) =>
          response.request().method() === 'PATCH' &&
          response.url().includes('/api/preparations-materiel/'),
      ),
      casesPret.nth(index).check(),
    ])
    await expect(casesPret.nth(index)).toBeChecked()
  }
  await Promise.all([
    page.waitForResponse(
      (response) =>
        response.request().method() === 'PATCH' &&
        response.url().includes('/api/preparations-materiel/'),
    ),
    casesAbsent.last().check(),
  ])
  await page.getByRole('button', { name: 'Valider la chirurgie' }).click()

  await expect(page).toHaveURL(/\/validation-partielle$/)
  await expect(
    page.getByRole('heading', { name: 'Matériel absent' }),
  ).toBeVisible()
  await page.getByRole('button', { name: /Passer à « Prêt »/ }).click()

  await expect(page).toHaveURL(/\/vue-finale$/)
  await expect(page.getByText(/lecture seule/)).toBeVisible()
  await expect(page.getByText(premierMateriel, { exact: true })).toBeVisible()
  await expect(
    page.getByRole('heading', { name: 'Fiche technique' }),
  ).toBeVisible()
})
