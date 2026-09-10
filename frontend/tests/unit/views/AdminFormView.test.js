import { flushPromises, mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, expect, it, vi } from 'vitest'
import AdminFormView from '@/views/AdminFormView.vue'
import { adminApi } from '@/services/adminApi'

describe('AdminFormView', () => {
  it('places a standard-sized return button with the form actions', async () => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/admin/:resource/new',
          name: 'admin-new',
          component: { template: '<div />' },
        },
        {
          path: '/admin/:resource',
          name: 'admin-list',
          component: { template: '<div />' },
        },
      ],
    })
    await router.push('/admin/specialites/new')
    await router.isReady()

    const wrapper = mount(AdminFormView, {
      props: { resourceSlug: 'specialites' },
      global: { plugins: [createPinia(), router] },
    })
    await flushPromises()

    const returnButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Retour')
    expect(returnButton).toBeTruthy()
    expect(returnButton.classes()).toContain('button-md')

    await returnButton.trigger('click')
    await flushPromises()
    expect(router.currentRoute.value.name).toBe('admin-list')
    expect(router.currentRoute.value.params.resource).toBe('specialites')
  })

  it('asks for custom confirmation before creating an admin resource', async () => {
    const create = vi.spyOn(adminApi, 'create').mockResolvedValue({
      id: 12,
      intitule: 'Cardiologie',
    })
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/admin/:resource/new',
          name: 'admin-new',
          component: { template: '<div />' },
        },
        {
          path: '/admin/:resource',
          name: 'admin-list',
          component: { template: '<div />' },
        },
      ],
    })
    await router.push('/admin/specialites/new')
    await router.isReady()

    const wrapper = mount(AdminFormView, {
      props: { resourceSlug: 'specialites' },
      global: {
        plugins: [createPinia(), router],
        stubs: { Teleport: true },
      },
    })
    await flushPromises()
    await wrapper.get('input').setValue('Cardiologie')
    await wrapper.get('form').trigger('submit')

    expect(create).not.toHaveBeenCalled()
    expect(wrapper.get('[role="dialog"]').text()).toContain(
      'Confirmer la création',
    )
    const confirmButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Créer')
    await confirmButton.trigger('click')
    await flushPromises()

    expect(create).toHaveBeenCalledWith('specialites', {
      intitule: 'Cardiologie',
    })
    wrapper.unmount()
  })
})
