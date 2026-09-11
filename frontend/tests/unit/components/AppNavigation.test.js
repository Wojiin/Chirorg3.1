import { beforeEach, describe, expect, it, vi } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import AppBottomNav from '@/components/ui/AppBottomNav.vue'
import AppHeader from '@/components/ui/AppHeader.vue'
import AppSidebar from '@/components/ui/AppSidebar.vue'
import { useAuthStore } from '@/stores/auth'

function createTestRouter() {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/login', name: 'login', component: { template: '<p />' } },
      {
        path: '/programme',
        name: 'programme',
        component: { template: '<p />' },
        meta: { title: 'Programme opératoire' },
      },
      { path: '/planifier', component: { template: '<p />' } },
      { path: '/admin', component: { template: '<p />' } },
      { path: '/compte', component: { template: '<p />' } },
    ],
  })
}

describe('application navigation', () => {
  let pinia
  let authStore
  let router

  beforeEach(async () => {
    window.localStorage.clear()
    document.documentElement.classList.remove('dark')
    pinia = createPinia()
    setActivePinia(pinia)
    authStore = useAuthStore()
    router = createTestRouter()
    await router.push('/programme')
    await router.isReady()
  })

  it('shows French navigation labels and reserves administration for admins', async () => {
    authStore.token = 'token'
    authStore.user = { email: 'user@chirorg.test', roles: ['ROLE_USER'] }

    const sidebar = mount(AppSidebar, { global: { plugins: [pinia, router] } })
    const bottomNav = mount(AppBottomNav, {
      global: { plugins: [pinia, router] },
    })

    expect(sidebar.text()).toContain('Planifier un programme')
    expect(sidebar.text()).not.toContain('Administration')
    expect(bottomNav.get('nav').attributes('aria-label')).toBe(
      'Navigation mobile',
    )
    expect(bottomNav.text()).not.toContain('Admin')

    authStore.user = {
      ...authStore.user,
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    }
    await sidebar.vm.$nextTick()
    expect(sidebar.text()).toContain('Administration')
    expect(bottomNav.text()).toContain('Admin')
  })

  it('displays the account, changes theme and logs out', async () => {
    authStore.token = 'token'
    authStore.user = {
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    }
    const logout = vi.spyOn(authStore, 'logout').mockResolvedValue()
    const wrapper = mount(AppHeader, { global: { plugins: [pinia, router] } })

    expect(wrapper.text()).toContain('Programme opératoire')
    expect(wrapper.text()).toContain('admin')
    expect(wrapper.text()).toContain('Administrateur')
    expect(wrapper.find('.theme-toggle').text()).toBe('')
    expect(wrapper.get('.theme-toggle-icon').attributes('aria-hidden')).toBe(
      'true',
    )

    await wrapper.get('[aria-label="Activer le thème sombre"]').trigger('click')
    expect(wrapper.get('[aria-label="Activer le thème clair"]')).toBeTruthy()

    await wrapper
      .get('button.header-action:not(.theme-toggle)')
      .trigger('click')
    await flushPromises()
    expect(logout).toHaveBeenCalledOnce()
    expect(router.currentRoute.value.name).toBe('login')
  })
})
