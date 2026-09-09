import { createMemoryHistory } from 'vue-router'
import { describe, expect, it } from 'vitest'

import { createAppRouter } from './index.js'

function auth(roles) {
  return {
    initialized: true,
    isAuthenticated: true,
    roles,
    initialize: async () => {},
  }
}

describe('application router', () => {
  it('protects every administrative route from ROLE_USER', async () => {
    const router = createAppRouter(auth(['ROLE_USER']), createMemoryHistory())
    await router.push('/administration/specialites')
    expect(router.currentRoute.value.name).toBe('acces-refuse')
  })

  it.each([
    ['/administration', 'administration'],
    ['/administration/specialites', 'admin-list'],
    ['/administration/specialites/ajouter', 'admin-create'],
    ['/administration/specialites/12/modifier', 'admin-edit'],
  ])('allows ROLE_ADMIN to open %s', async (path, name) => {
    const router = createAppRouter(
      auth(['ROLE_USER', 'ROLE_ADMIN']),
      createMemoryHistory(),
    )
    await router.push(path)
    expect(router.currentRoute.value.name).toBe(name)
    expect(document.title).toContain('ChirOrg')
  })

  it.each([
    ['/programmes', 'programmes'],
    ['/programmes/planifier', 'planification'],
    ['/programmes/2027-01-12/Salle%20A/2', 'programme-detail'],
    ['/chirurgies/10/preparation', 'preparation'],
    ['/chirurgies/10/vue-finale', 'vue-finale'],
  ])('allows ROLE_USER to open the business route %s', async (path, name) => {
    const router = createAppRouter(auth(['ROLE_USER']), createMemoryHistory())
    await router.push(path)
    expect(router.currentRoute.value.name).toBe(name)
  })
})
