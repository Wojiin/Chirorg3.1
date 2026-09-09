import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { nextTick } from 'vue'
import { createMemoryHistory, createRouter } from 'vue-router'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { useAuthStore } from '../stores/auth.js'
import AccessDeniedView from './AccessDeniedView.vue'
import HomeView from './HomeView.vue'
import LoginView from './LoginView.vue'

const RouterLinkStub = {
  props: ['to'],
  template:
    '<a :data-to="typeof to === `string` ? to : JSON.stringify(to)"><slot /></a>',
}

describe('core views', () => {
  beforeEach(() => {
    window.localStorage.clear()
    setActivePinia(createPinia())
  })

  it('adapts home and access denied actions to the current role', async () => {
    const auth = useAuthStore()
    auth.profile = { email: 'admin@chirorg.test', roles: ['ROLE_ADMIN'] }
    auth.authenticated = true
    const home = mount(HomeView, {
      global: { stubs: { RouterLink: RouterLinkStub } },
    })
    const denied = mount(AccessDeniedView, {
      global: { stubs: { RouterLink: RouterLinkStub } },
    })

    expect(home.text()).toContain('admin@chirorg.test')
    expect(home.text()).toContain('Administration')
    expect(denied.get('a').attributes('data-to')).toBe('/')

    auth.profile = { email: 'user@chirorg.test', roles: ['ROLE_USER'] }
    auth.authenticated = false
    await nextTick()
    expect(home.text()).not.toContain('Administration')
    expect(denied.get('a').attributes('data-to')).toBe('/connexion')
  })

  it('submits credentials and only follows a safe local redirect', async () => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        { path: '/connexion', component: LoginView },
        { path: '/administration', component: { template: '<div />' } },
      ],
    })
    await router.push('/connexion?redirect=/administration')
    await router.isReady()
    const auth = useAuthStore()
    const login = vi.spyOn(auth, 'login').mockResolvedValue()
    const wrapper = mount(LoginView, { global: { plugins: [router] } })

    await wrapper.get('input[name="email"]').setValue('admin@chirorg.test')
    await wrapper.get('input[name="password"]').setValue('Password-42!')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(login).toHaveBeenCalledWith({
      email: 'admin@chirorg.test',
      password: 'Password-42!',
    })
    expect(router.currentRoute.value.path).toBe('/administration')
  })

  it('stays on login and exposes the store error after rejection', async () => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [{ path: '/connexion', component: LoginView }],
    })
    await router.push('/connexion?redirect=//hostile.test')
    await router.isReady()
    const auth = useAuthStore()
    vi.spyOn(auth, 'login').mockImplementation(async () => {
      auth.error = 'Identifiants invalides'
      throw new Error('invalid')
    })
    const wrapper = mount(LoginView, { global: { plugins: [router] } })
    await wrapper.get('form').trigger('submit')
    await flushPromises()
    expect(wrapper.text()).toContain('Identifiants invalides')
    expect(router.currentRoute.value.path).toBe('/connexion')
  })
})
