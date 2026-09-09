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
  const date = new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10)

  await page.goto('/connexion')
  await page.getByLabel('Adresse email').fill(userEmail)
  await page.getByLabel('Mot de passe').fill(userPassword)
  await page.getByRole('button', { name: 'Se connecter' }).click()

  await page
    .getByLabel('Navigation principale')
    .getByRole('link', { name: 'Planifier', exact: true })
    .click()
  await page.getByLabel('Spécialité').selectOption({ label: 'Orthopédie' })
  await page
    .getByLabel('Chirurgien')
    .selectOption({ label: 'Dr Nicolas Bernard' })
  await page.getByLabel('Date').fill(date)
  await page.getByLabel('Salle').fill('Salle E2E')
  await page
    .getByRole('group', { name: 'Interventions dans l’ordre du programme' })
    .getByRole('combobox')
    .selectOption({ label: 'Prothèse totale de hanche' })
  await page.getByRole('button', { name: 'Planifier', exact: true }).click()

  await expect(page.getByRole('heading', { name: 'Salle E2E' })).toBeVisible()
  await page.getByRole('link', { name: 'Préparer' }).click()
  await expect(
    page.getByRole('heading', { name: 'Prothèse totale de hanche' }),
  ).toBeVisible()
  await page.getByRole('button', { name: 'Marquer prêt' }).click()
  await page.getByRole('button', { name: 'Valider la préparation' }).click()

  await expect(page.getByText('Intervention validée')).toBeVisible()
  await expect(page.getByText('Moteur orthopédique')).toBeVisible()
  await expect(
    page.getByRole('heading', { name: 'Fiches techniques' }),
  ).toBeVisible()
})
