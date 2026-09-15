import { expect, test } from '@playwright/test'

function requiredEnvironment(name) {
  const value = process.env[name]
  if (!value)
    throw new Error(`La variable d’environnement E2E ${name} est obligatoire.`)
  return value
}

const adminEmail = requiredEnvironment('E2E_ADMIN_EMAIL')
const adminPassword = requiredEnvironment('E2E_ADMIN_PASSWORD')

const apiResources = {
  specialites: 'specialites',
  chirurgiens: 'chirurgiens',
  'chirurgie-modeles': 'chirurgie-modeles',
  materiels: 'materiels',
  'listes-materiel': 'listes-materiel',
}

async function login(page) {
  await page.goto('/login')
  await page.getByLabel('Email').fill(adminEmail)
  await page.getByLabel('Mot de passe').fill(adminPassword)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await expect(page).toHaveURL(/\/programme$/)
}

async function createAdminResource(page, resource, fillForm) {
  await page.goto(`/admin/${resource}/new`)
  await fillForm()
  await page.getByRole('button', { name: 'Enregistrer' }).click()

  const responsePromise = page.waitForResponse((response) => {
    const path = new URL(response.url()).pathname
    return (
      response.request().method() === 'POST' &&
      path === `/api/${apiResources[resource]}`
    )
  })
  await page.getByRole('dialog').getByRole('button', { name: 'Créer' }).click()
  const response = await responsePromise

  expect(response.ok()).toBe(true)
  await expect(page).toHaveURL(new RegExp(`/admin/${resource}$`))
  return response.json()
}

async function findAdminRow(page, resource, query, expectedText = query) {
  await page.goto(`/admin/${resource}`)
  await page.getByLabel('Rechercher').fill(query)
  const row = page.getByRole('row').filter({ hasText: expectedText })
  await expect(row).toBeVisible()
  return row
}

async function setMaterialState(page, materialName, state) {
  const item = page.locator('.preparation-item').filter({
    hasText: materialName,
  })
  await expect(item).toBeVisible()
  await Promise.all([
    page.waitForResponse(
      (response) =>
        response.request().method() === 'PATCH' &&
        response.url().includes('/api/preparations-materiel/'),
    ),
    item.getByLabel(state).check(),
  ])
  await expect(item.getByLabel(state)).toBeChecked()
}

test.describe('parcours administrateur et programme opératoire', () => {
  test.setTimeout(180_000)

  test('propage les modifications puis la suppression d’une spécialité dans tout le parcours métier', async ({
    page,
  }) => {
    const suffix = Date.now()
    const speciality = `Spécialité parcours ${suffix}`
    const renamedSpeciality = `${speciality} renommée`
    const surgeonFirstName = 'Élodie'
    const surgeonLastName = `Parcours-${suffix}`
    const surgeonLabel = `Dr ${surgeonFirstName} ${surgeonLastName}`
    const surgeryModel = `Intervention parcours ${suffix}`
    const materialOne = `Boîte parcours ${suffix}`
    const materialTwo = `Implant parcours ${suffix}`
    const materialList = `Liste parcours ${suffix}`
    const programmeDate = new Date(Date.now() + 90 * 86_400_000)
      .toISOString()
      .slice(0, 10)
    const room = 'Salle B'
    let programmeDetailUrl = ''

    await login(page)

    await test.step('créer la spécialité', async () => {
      await createAdminResource(page, 'specialites', async () => {
        await page.getByLabel('Intitulé de la spécialité').fill(speciality)
      })
      await findAdminRow(page, 'specialites', speciality)
    })

    await test.step('créer le chirurgien et la chirurgie modèle associés', async () => {
      await createAdminResource(page, 'chirurgiens', async () => {
        await page.getByLabel('Prénom').fill(surgeonFirstName)
        await page
          .getByRole('textbox', { name: 'Nom', exact: true })
          .fill(surgeonLastName)
        await page.getByLabel('Spécialité').selectOption({ label: speciality })
      })

      await createAdminResource(page, 'chirurgie-modeles', async () => {
        await page.getByLabel('Intitulé de la chirurgie').fill(surgeryModel)
        await page.getByLabel('Spécialité').selectOption({ label: speciality })
      })

      await page.getByRole('combobox', { name: 'Spécialité' }).click()
      await page.getByRole('option', { name: speciality }).click()
      await expect(
        page.getByRole('row').filter({ hasText: surgeryModel }),
      ).toBeVisible()
      await page.getByRole('link', { name: 'Retour aux référentiels' }).click()
      await expect(page).toHaveURL(/\/admin$/)
    })

    await test.step('créer deux matériels associés à la spécialité', async () => {
      await createAdminResource(page, 'materiels', async () => {
        await page.getByLabel('Intitulé du matériel').fill(materialOne)
        await page.getByLabel('Adresse de rangement').fill('Arsenal E2E A-01')
        await page.getByLabel('Type de matériel').selectOption('Instrument')
        await page.getByLabel('Spécialité').selectOption({ label: speciality })
      })

      await createAdminResource(page, 'materiels', async () => {
        await page.getByLabel('Intitulé du matériel').fill(materialTwo)
        await page.getByLabel('Adresse de rangement').fill('Arsenal E2E A-02')
        await page.getByLabel('Type de matériel').selectOption('Implant')
        await page.getByLabel('Spécialité').selectOption({ label: speciality })
      })
    })

    await test.step('composer la liste de matériel du chirurgien', async () => {
      await createAdminResource(page, 'listes-materiel', async () => {
        await page.getByLabel('Intitulé de la liste').fill(materialList)
        await page
          .getByLabel('Chirurgien')
          .selectOption({ label: surgeonLabel })
        await page
          .getByLabel('Chirurgie modèle')
          .selectOption({ label: surgeryModel })

        for (const material of [materialOne, materialTwo]) {
          const availableMaterial = page
            .getByRole('listitem')
            .filter({ hasText: material })
          await expect(availableMaterial).toBeVisible()
          await availableMaterial
            .getByRole('button', { name: 'Ajouter' })
            .click()
        }

        await expect(
          page.getByRole('heading', { name: 'Matériels ajoutés (2)' }),
        ).toBeVisible()
      })
      await findAdminRow(page, 'listes-materiel', materialList)
    })

    await test.step('planifier deux interventions avec les nouvelles références', async () => {
      await page.goto('/planifier')
      await page.getByLabel('Spécialité').selectOption({ label: speciality })
      await page.getByLabel('Chirurgien').selectOption({ label: surgeonLabel })
      await page.getByLabel('Date programmée').fill(programmeDate)
      await page.getByLabel('Salle').selectOption(room)
      await page.getByLabel('Chirurgie 1').selectOption({ label: surgeryModel })
      await page.getByRole('button', { name: 'Ajouter une chirurgie' }).click()
      await page.getByLabel('Chirurgie 2').selectOption({ label: surgeryModel })
      await page.getByRole('button', { name: 'Planifier le programme' }).click()

      await expect(page).toHaveURL(/\/programme$/)
      await page.getByLabel('Date').fill(programmeDate)
      await page.getByLabel('Salle').selectOption(room)
      const programme = page
        .locator('article.programme-summary-card')
        .filter({ hasText: surgeonLastName })
        .filter({ hasText: room })
      await expect(programme).toBeVisible()
      await programme
        .getByRole('link', { name: 'Voir le détail du programme' })
        .click()

      await expect(
        page.getByRole('heading', { name: 'Détail du programme' }),
      ).toBeVisible()
      programmeDetailUrl = page.url()
      await expect(
        page
          .locator('article.programme-card')
          .filter({ hasText: surgeryModel }),
      ).toHaveCount(2)
    })

    await test.step('tester la validation partielle puis sa régularisation totale', async () => {
      const firstSurgery = page
        .locator('article.programme-card')
        .filter({ hasText: surgeryModel })
        .first()
      await firstSurgery
        .getByRole('link', { name: 'Préparer', exact: true })
        .click()

      await setMaterialState(page, materialOne, 'Prêt')
      await setMaterialState(page, materialTwo, 'Absent')
      await page.getByRole('button', { name: 'Valider la chirurgie' }).click()

      await expect(page).toHaveURL(/\/validation-partielle$/)
      await expect(
        page.getByRole('heading', { name: 'Matériel absent' }),
      ).toBeVisible()
      const absentMaterial = page.getByRole('listitem').filter({
        hasText: materialTwo,
      })
      await expect(absentMaterial).toBeVisible()
      await absentMaterial
        .getByRole('button', { name: 'Passer à « Prêt »' })
        .click()

      await expect(page).toHaveURL(/\/vue-finale$/)
      await expect(page.getByText(/lecture seule/)).toBeVisible()
      await expect(page.getByText(materialOne, { exact: true })).toBeVisible()
      await expect(page.getByText(materialTwo, { exact: true })).toBeVisible()
    })

    await test.step('tester la validation totale directe de la seconde intervention', async () => {
      await page.goto(programmeDetailUrl)
      const pendingSurgery = page
        .locator('article.programme-card')
        .filter({ hasText: surgeryModel })
        .filter({
          has: page.getByRole('link', { name: 'Préparer', exact: true }),
        })
      await expect(pendingSurgery).toHaveCount(1)
      await pendingSurgery
        .getByRole('link', { name: 'Préparer', exact: true })
        .click()

      await setMaterialState(page, materialOne, 'Prêt')
      await setMaterialState(page, materialTwo, 'Prêt')
      await page.getByRole('button', { name: 'Valider la chirurgie' }).click()

      await expect(page).toHaveURL(/\/vue-finale$/)
      await expect(page.getByText(/lecture seule/)).toBeVisible()
      await page.goto(programmeDetailUrl)
      await expect(page.getByText('Validée', { exact: true })).toHaveCount(2)
    })

    await test.step('renommer la spécialité et vérifier la propagation', async () => {
      const specialityRow = await findAdminRow(page, 'specialites', speciality)
      await specialityRow.getByRole('link', { name: 'Modifier' }).click()
      await page.getByLabel('Intitulé de la spécialité').fill(renamedSpeciality)
      await page.getByRole('button', { name: 'Enregistrer' }).click()
      await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Enregistrer' })
        .click()

      await expect(
        await findAdminRow(page, 'chirurgiens', surgeonLastName),
      ).toContainText(renamedSpeciality)
      await expect(
        await findAdminRow(page, 'chirurgie-modeles', surgeryModel),
      ).toContainText(renamedSpeciality)
      await expect(
        await findAdminRow(page, 'materiels', materialOne),
      ).toContainText(renamedSpeciality)
      await expect(
        await findAdminRow(page, 'materiels', materialTwo),
      ).toContainText(renamedSpeciality)
      await expect(
        await findAdminRow(page, 'listes-materiel', materialList),
      ).toContainText(`${surgeonFirstName} ${surgeonLastName}`)
    })

    await test.step('supprimer la spécialité et vérifier le repli Sans spécialité', async () => {
      const specialityRow = await findAdminRow(
        page,
        'specialites',
        renamedSpeciality,
      )
      await specialityRow.getByRole('button', { name: 'Supprimer' }).click()
      await expect(page.getByRole('dialog')).toContainText(renamedSpeciality)
      await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Supprimer' })
        .click()
      await expect(specialityRow).toHaveCount(0)

      await expect(
        await findAdminRow(page, 'chirurgiens', surgeonLastName),
      ).toContainText('Sans spécialité')
      await expect(
        await findAdminRow(page, 'chirurgie-modeles', surgeryModel),
      ).toContainText('Sans spécialité')
      await expect(
        await findAdminRow(page, 'materiels', materialOne),
      ).toContainText('Sans spécialité')
      await expect(
        await findAdminRow(page, 'materiels', materialTwo),
      ).toContainText('Sans spécialité')
      await expect(
        await findAdminRow(page, 'listes-materiel', materialList),
      ).toContainText(`${surgeonFirstName} ${surgeonLastName}`)

      await page.goto(programmeDetailUrl)
      await expect(
        page
          .locator('article.programme-card')
          .filter({ hasText: surgeryModel }),
      ).toHaveCount(2)
      await expect(page.getByText('Validée', { exact: true })).toHaveCount(2)
    })
  })
})
