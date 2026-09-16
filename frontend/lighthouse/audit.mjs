/* global console */
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs'
import { createServer } from 'node:net'
import { tmpdir } from 'node:os'
import { join, resolve } from 'node:path'
import lighthouse from 'lighthouse'
import { chromium } from '@playwright/test'
import { evaluateQualityGate, qualityGateError } from './qualityGate.mjs'

const baseUrl = process.env.LH_BASE_URL ?? 'http://localhost:5173'
const apiBaseUrl = process.env.LH_API_BASE_URL ?? 'http://localhost:8080/api'
const runs = Number.parseInt(process.env.LH_RUNS ?? '3', 10)
const reportDirectory = resolve(
  process.env.LH_REPORT_DIR ?? 'lighthouse-reports',
)
const categories = ['performance', 'accessibility', 'best-practices', 'seo']

const staticProtectedRoutes = [
  '/programme',
  '/planifier',
  '/compte',
  '/admin',
  '/admin/materiels',
  '/admin/materiels/new',
]

function collectionMembers(data) {
  return data?.member ?? data?.['hydra:member'] ?? []
}

function getFreePort() {
  return new Promise((resolvePort, reject) => {
    const server = createServer()
    server.unref()
    server.on('error', reject)
    server.listen(0, '127.0.0.1', () => {
      const address = server.address()
      const port = typeof address === 'object' && address ? address.port : null
      server.close(() =>
        port ? resolvePort(port) : reject(new Error('Port CDP introuvable.')),
      )
    })
  })
}

function routeName(route) {
  return route === '/'
    ? 'accueil'
    : route.replace(/^\//, '').replaceAll('/', '-').replaceAll('%20', '-')
}

function scoresFrom(result) {
  return Object.fromEntries(
    categories.map((category) => [
      category,
      Math.round(result.lhr.categories[category].score * 100),
    ]),
  )
}

function median(values) {
  const ordered = [...values].sort((left, right) => left - right)
  return ordered[Math.floor(ordered.length / 2)]
}

async function authenticate(context) {
  const email = process.env.LH_EMAIL
  const password = process.env.LH_PASSWORD

  if (!email || !password) {
    throw new Error(
      'LH_EMAIL et LH_PASSWORD doivent être définis pour auditer les routes protégées.',
    )
  }

  const response = await context.request.post(`${apiBaseUrl}/auth/login`, {
    data: { email, password },
    headers: { Origin: baseUrl },
  })

  if (!response.ok()) {
    throw new Error(
      `La connexion Lighthouse a échoué avec le statut ${response.status()}.`,
    )
  }

  const payload = await response.json()
  if (!payload?.token) {
    throw new Error("La connexion Lighthouse n'a retourné aucun jeton JWT.")
  }

  // La requête API partage le stockage de cookies du contexte persistant.
  // Chromium doit ensuite gérer lui-même la rotation du refresh token à usage unique.
  await sessionCookieHeader(context)

  return payload.token
}

async function sessionCookieHeader(context) {
  const cookies = await context.cookies(`${apiBaseUrl}/auth/refresh`)
  const cookieHeader = cookies
    .filter(({ path }) => '/api/auth/refresh'.startsWith(path))
    .map(({ name, value }) => `${name}=${value}`)
    .join('; ')

  if (!cookieHeader) {
    throw new Error(
      "La connexion Lighthouse n'a retourné aucun cookie de session.",
    )
  }

  return cookieHeader
}

async function apiGet(context, path, token) {
  const response = await context.request.get(`${apiBaseUrl}${path}`, {
    headers: { Authorization: `Bearer ${token}`, Origin: baseUrl },
  })

  if (!response.ok()) {
    throw new Error(
      `La préparation des routes Lighthouse a échoué pour ${path} avec le statut ${response.status()}.`,
    )
  }

  return response.json()
}

async function apiWrite(context, method, path, token, data) {
  const response = await context.request.fetch(`${apiBaseUrl}${path}`, {
    method,
    data,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/merge-patch+json',
      Origin: baseUrl,
    },
  })

  if (!response.ok()) {
    throw new Error(
      `La préparation de l'état métier Lighthouse a échoué pour ${path} avec le statut ${response.status()}.`,
    )
  }

  return response.json()
}

/** Crée un état partiel dans la base éphémère afin d'auditer la vue sans redirection. */
async function preparePartialValidation(context, surgeryId, token) {
  const surgery = await apiGet(
    context,
    `/chirurgies-planifiees/${surgeryId}/preparation`,
    token,
  )
  const preparations = surgery.preparationsMateriel ?? []

  if (preparations.length === 0) {
    throw new Error(
      `La chirurgie ${surgeryId} ne contient aucun matériel à préparer.`,
    )
  }

  for (const [index, preparation] of preparations.entries()) {
    await apiWrite(
      context,
      'PATCH',
      `/preparations-materiel/${preparation.id}/cocher`,
      token,
      index === 0
        ? { coche: false, absent: true }
        : { coche: true, absent: false },
    )
  }

  await apiWrite(
    context,
    'POST',
    `/chirurgies-planifiees/${surgeryId}/validation`,
    token,
  )
  const result = await apiGet(
    context,
    `/chirurgies-planifiees/${surgeryId}/preparation`,
    token,
  )

  if (result.etatValidation !== 'VALIDATION_PARTIELLE') {
    throw new Error(
      `La chirurgie ${surgeryId} n'a pas atteint l'état VALIDATION_PARTIELLE.`,
    )
  }
}

/** Construit les routes métier depuis les données réelles chargées par les fixtures. */
async function discoverWorkflowRoutes(context, token) {
  const collection = await apiGet(
    context,
    '/programmes-operatoires?itemsPerPage=100',
    token,
  )
  const programmes = collectionMembers(collection)
  let programmeRoute = null
  const pendingSurgeries = []
  let validatedSurgery = null

  for (const programme of programmes) {
    const surgeonId = programme.chirurgien?.id
    if (!programme.date || !programme.salle || !surgeonId) continue

    const encodedRoom = encodeURIComponent(programme.salle)
    const route = `/programmes/${programme.date}/${encodedRoom}/${surgeonId}`
    programmeRoute ??= route
    const detail = await apiGet(
      context,
      `/programmes-operatoires/${programme.date}/${encodedRoom}/${surgeonId}`,
      token,
    )

    for (const surgery of detail.chirurgies ?? []) {
      if (surgery.valide) validatedSurgery ??= surgery
      else if (!pendingSurgeries.some(({ id }) => id === surgery.id)) {
        pendingSurgeries.push(surgery)
      }
    }

    if (programmeRoute && pendingSurgeries.length >= 2 && validatedSurgery)
      break
  }

  if (!programmeRoute || pendingSurgeries.length < 2 || !validatedSurgery?.id) {
    throw new Error(
      'Les fixtures doivent fournir un programme, deux chirurgies à préparer et une chirurgie validée.',
    )
  }

  const [pendingSurgery, partialSurgery] = pendingSurgeries
  await preparePartialValidation(context, partialSurgery.id, token)

  return [
    ...staticProtectedRoutes,
    programmeRoute,
    `/chirurgies/${pendingSurgery.id}/preparation`,
    `/chirurgies/${partialSurgery.id}/validation-partielle`,
    `/chirurgies/${validatedSurgery.id}/vue-finale`,
    '/page-inexistante',
  ]
}

async function auditRoute(route, port) {
  const results = []

  for (let run = 1; run <= runs; run += 1) {
    const url = new URL(route, baseUrl).href
    console.log(`[${run}/${runs}] ${url}`)
    const flags = {
      port,
      output: 'html',
      logLevel: 'error',
      disableStorageReset: true,
    }
    const result = await lighthouse(url, flags)

    if (!result)
      throw new Error(`Lighthouse n'a produit aucun résultat pour ${url}.`)

    const expectedUrl = new URL(url)
    const finalUrl = new URL(result.lhr.finalDisplayedUrl)
    if (
      finalUrl.pathname !== expectedUrl.pathname ||
      finalUrl.search !== expectedUrl.search
    ) {
      throw new Error(
        `Lighthouse a été redirigé de ${expectedUrl.pathname} vers ${finalUrl.pathname}${finalUrl.search}.`,
      )
    }

    const name = `${routeName(route)}-${run}`
    writeFileSync(join(reportDirectory, `${name}.html`), result.report)
    writeFileSync(
      join(reportDirectory, `${name}.json`),
      JSON.stringify(result.lhr, null, 2),
    )
    results.push(scoresFrom(result))
  }

  return Object.fromEntries(
    categories.map((category) => [
      category,
      median(results.map((result) => result[category])),
    ]),
  )
}

async function main() {
  if (!Number.isInteger(runs) || runs < 1)
    throw new Error('LH_RUNS doit être un entier positif.')

  mkdirSync(reportDirectory, { recursive: true })
  const profileDirectory = mkdtempSync(join(tmpdir(), 'chirorg-lighthouse-'))
  const port = await getFreePort()
  let context

  try {
    context = await chromium.launchPersistentContext(profileDirectory, {
      headless: true,
      args: [`--remote-debugging-port=${port}`, '--disable-gpu'],
    })

    const configuredRoutes = process.env.LH_ROUTES
      ? process.env.LH_ROUTES.split(',')
          .map((route) => route.trim())
          .filter(Boolean)
      : null
    const summary = {}
    const auditLogin = !configuredRoutes || configuredRoutes.includes('/login')

    if (auditLogin) {
      summary['/login'] = await auditRoute('/login', port)
    }

    let protectedRoutes = configuredRoutes
      ? configuredRoutes.filter((route) => route !== '/login')
      : null

    if (!protectedRoutes || protectedRoutes.length > 0) {
      const token = await authenticate(context)
      protectedRoutes ??= await discoverWorkflowRoutes(context, token)
    }

    if (!auditLogin && protectedRoutes.length === 0) {
      throw new Error('Aucune route Lighthouse configurée.')
    }

    for (const route of protectedRoutes) {
      summary[route] = await auditRoute(route, port)
    }

    const global = Object.fromEntries(
      categories.map((category) => {
        const values = Object.values(summary).map((scores) => scores[category])
        return [
          category,
          { median: median(values), minimum: Math.min(...values) },
        ]
      }),
    )

    const report = {
      generatedAt: new Date().toISOString(),
      runs,
      pages: summary,
      global,
      qualityGate: evaluateQualityGate(global, process.env),
    }
    writeFileSync(
      join(reportDirectory, 'summary.json'),
      JSON.stringify(report, null, 2),
    )

    console.table(summary)
    console.table(global)
    console.table(report.qualityGate.thresholds)
    console.log(`Rapports générés dans ${reportDirectory}`)

    if (!report.qualityGate.passed) {
      throw new Error(
        `Budgets Lighthouse non respectés — ${qualityGateError(report.qualityGate.failures)}.`,
      )
    }
  } finally {
    await context?.close()
    try {
      rmSync(profileDirectory, { recursive: true, force: true })
    } catch {
      console.warn(
        `Le profil Chrome temporaire n'a pas pu être supprimé : ${profileDirectory}`,
      )
    }
  }
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
