import { spawnSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const localHosts = new Set(['localhost', '127.0.0.1', '::1'])

/** Nettoie les données métier temporaires même lorsqu'un scénario Playwright échoue. */
export default function globalTeardown() {
  if (process.env.E2E_SKIP_DATABASE_CLEANUP === '1') return

  const baseUrl = new URL(process.env.E2E_BASE_URL || 'http://localhost:5173')
  if (!localHosts.has(baseUrl.hostname)) return

  const repositoryDirectory = fileURLToPath(new URL('../../', import.meta.url))
  const result = spawnSync(
    'docker',
    [
      'compose',
      '--env-file',
      '.env.example',
      '-f',
      'docker-compose.dev.yaml',
      'exec',
      '-T',
      'backend',
      'php',
      'bin/console',
      'app:e2e:cleanup',
      '--no-interaction',
    ],
    { cwd: repositoryDirectory, encoding: 'utf8' },
  )

  if (result.stdout) process.stdout.write(result.stdout)
  if (result.stderr) process.stderr.write(result.stderr)
  if (result.status !== 0) {
    throw new Error(
      'Le nettoyage automatique des données temporaires E2E a échoué.',
    )
  }
}
