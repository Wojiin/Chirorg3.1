import { readFileSync, readdirSync } from 'node:fs'
import { resolve } from 'node:path'
import { describe, expect, it } from 'vitest'

const sourceRoot = resolve(process.cwd(), 'src')

function sourceFiles(directory, extension) {
  return readdirSync(resolve(sourceRoot, directory))
    .filter((file) => file.endsWith(extension))
    .map((file) => ({
      file,
      source: readFileSync(resolve(sourceRoot, directory, file), 'utf8'),
    }))
}

describe('frontend architecture boundaries', () => {
  it('keeps functional orchestration out of page components', () => {
    for (const { file, source } of sourceFiles('views', '.vue')) {
      expect(source, file).not.toMatch(
        /from ['"](?:pinia|vue-router|@\/api\/|@\/services\/|@\/stores\/)/,
      )

      if (file !== 'NotFoundView.vue') {
        const composable = `@/composables/use${file.replace('.vue', '')}`
        expect(source, file).toContain(composable)
      }
    }
  })

  it('keeps Pinia stores independent from the Axios implementation', () => {
    for (const { file, source } of sourceFiles('stores', '.js')) {
      expect(source, file).not.toContain('@/api/axios')
      expect(source, file).not.toMatch(/\baxios\b/)
    }
  })

  it('limits direct HTTP client access to API services and bootstrap infrastructure', () => {
    const directClientImports = []

    for (const directory of [
      'composables',
      'config',
      'domain',
      'mappers',
      'presenters',
      'stores',
      'utils',
    ]) {
      for (const { file, source } of sourceFiles(directory, '.js')) {
        if (source.includes('@/api/axios'))
          directClientImports.push(`${directory}/${file}`)
      }
    }

    expect(directClientImports).toEqual([])
  })
})
