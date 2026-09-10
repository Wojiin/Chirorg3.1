import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import App from '@/App.vue'
import appRouter from '@/router'
import { useAuthStore } from '@/stores/auth'

describe('application shell and routing', () => {
  it('renders a public route without the authenticated shell', async () => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/public',
          component: { template: '<p>Écran public ChirOrg</p>' },
          meta: { public: true },
        },
      ],
    })
    await router.push('/public')
    await router.isReady()

    const wrapper = mount(App, {
      global: {
        plugins: [createPinia(), router],
        stubs: { Toaster: true },
      },
    })

    expect(wrapper.text()).toContain('Écran public ChirOrg')
    expect(wrapper.text()).not.toContain('Déconnexion')
  })

  it('declares the expected protected and admin routes', () => {
    expect(appRouter.resolve('/programme').meta.requiresAuth).toBe(true)
    expect(appRouter.resolve('/programmes/2030-01-15/Salle%20A/7').name).toBe(
      'programme-detail',
    )
    expect(appRouter.resolve('/chirurgies/42/preparation').name).toBe(
      'preparation',
    )
    expect(appRouter.resolve('/chirurgies/42/validation-partielle').name).toBe(
      'validation-partielle',
    )
    expect(appRouter.resolve('/chirurgies/42/vue-finale').name).toBe(
      'vue-finale',
    )
    expect(appRouter.resolve('/admin/materiels/new').meta.requiresAdmin).toBe(
      true,
    )
    expect(appRouter.resolve('/admin/materiels/7/edit').name).toBe('admin-edit')
    expect(appRouter.resolve('/adresse-inconnue').name).toBe('not-found')
  })

  it('updates the document metadata with the current screen', async () => {
    const description = document.createElement('meta')
    description.name = 'description'
    document.head.append(description)
    const pinia = createPinia()
    setActivePinia(pinia)
    const authStore = useAuthStore()
    authStore.user = {
      id: 1,
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN', 'ROLE_USER'],
    }
    authStore.token = 'test-token'
    authStore.initialized = true
    await appRouter.push('/admin/materiels')
    await appRouter.isReady()

    const wrapper = mount(App, {
      global: {
        plugins: [pinia, appRouter],
        stubs: { Toaster: true },
      },
    })

    expect(document.title).toBe('Matériels | ChirOrg')
    expect(description.content).toContain('référentiel ChirOrg')
    wrapper.unmount()
    description.remove()
  })
})
