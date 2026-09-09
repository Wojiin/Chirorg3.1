import { createPinia, setActivePinia } from 'pinia'
import { mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import { beforeEach, describe, expect, it } from 'vitest'

import { useAuthStore } from '../stores/auth.js'
import AppShell from './AppShell.vue'

function testRouter() {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'accueil', component: { template: '<div />' } },
      {
        path: '/administration',
        name: 'administration',
        component: { template: '<div />' },
      },
      {
        path: '/connexion',
        name: 'connexion',
        component: { template: '<div />' },
      },
    ],
  })
}

async function renderShell(roles) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const auth = useAuthStore()
  auth.profile = { email: 'user@chirorg.local', roles }
  auth.authenticated = true
  auth.initialized = true
  const router = testRouter()
  await router.push('/')
  await router.isReady()

  return mount(AppShell, {
    global: { plugins: [pinia, router] },
    slots: { default: '<p>Contenu protégé</p>' },
  })
}

describe('AppShell', () => {
  beforeEach(() => window.localStorage.clear())

  it('shows the profile but hides administration from ROLE_USER', async () => {
    const wrapper = await renderShell(['ROLE_USER'])

    expect(wrapper.text()).toContain('user@chirorg.local')
    expect(wrapper.text()).toContain('Contenu protégé')
    expect(wrapper.text()).not.toContain('Administration')
  })

  it('shows administration to ROLE_ADMIN', async () => {
    const wrapper = await renderShell(['ROLE_USER', 'ROLE_ADMIN'])

    expect(wrapper.text()).toContain('Administration')
    expect(wrapper.text()).toContain('Administrateur')
  })
})
