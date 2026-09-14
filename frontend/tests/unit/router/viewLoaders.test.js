import { describe, expect, it } from 'vitest'
import {
  getViewLoaderForPath,
  preloadViewForPath,
  viewLoaders,
} from '@/router/viewLoaders'

describe('route view preloading', () => {
  it.each([
    ['/login', 'login'],
    ['/', 'programme'],
    ['/programme', 'programme'],
    ['/programmes/2026-09-11/Salle%20A/1', 'programmeDetail'],
    ['/planifier', 'planification'],
    ['/chirurgies/7/preparation', 'preparation'],
    ['/chirurgies/8/validation-partielle', 'partialValidation'],
    ['/chirurgies/1/vue-finale', 'finalView'],
    ['/admin', 'adminDashboard'],
    ['/admin/materiels', 'adminList'],
    ['/admin/materiels/new', 'adminForm'],
    ['/admin/materiels/12/edit', 'adminForm'],
    ['/compte', 'account'],
    ['/page-inexistante', 'notFound'],
  ])('maps %s to the %s loader', (path, loaderName) => {
    expect(getViewLoaderForPath(path)).toBe(viewLoaders[loaderName])
  })

  it('normalizes trailing slashes', () => {
    expect(getViewLoaderForPath('/admin/materiels/')).toBe(
      viewLoaders.adminList,
    )
  })

  it('loads every lazily declared view', async () => {
    const modules = await Promise.all(
      Object.keys(viewLoaders).map((name) =>
        preloadViewForPath(
          Object.entries({
            login: '/login',
            programme: '/programme',
            programmeDetail: '/programmes/2026-09-11/Salle-A/1',
            planification: '/planifier',
            preparation: '/chirurgies/1/preparation',
            partialValidation: '/chirurgies/1/validation-partielle',
            finalView: '/chirurgies/1/vue-finale',
            adminDashboard: '/admin',
            adminForm: '/admin/materiels/new',
            adminList: '/admin/materiels',
            account: '/compte',
            notFound: '/inconnue',
          }).find(([loaderName]) => loaderName === name)[1],
        ),
      ),
    )

    expect(modules.every((module) => module.default)).toBe(true)
  }, 15_000)
})
