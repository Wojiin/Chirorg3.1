import { flushPromises, mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { adminApi } from '../services/admin.js'
import AdminFormView from './AdminFormView.vue'

async function mountForm(props) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: '/administration/:resource',
        name: 'admin-list',
        component: { template: '<div />' },
      },
      { path: '/test', name: 'test', component: { template: '<div />' } },
    ],
  })
  await router.push('/test')
  await router.isReady()
  const wrapper = mount(AdminFormView, {
    props,
    global: { plugins: [createPinia(), router], stubs: { Teleport: true } },
  })
  await flushPromises()
  return { wrapper, router }
}

describe('AdminFormView', () => {
  beforeEach(() => vi.restoreAllMocks())

  it('confirms then creates a speciality', async () => {
    const create = vi
      .spyOn(adminApi, 'create')
      .mockResolvedValue({ id: 1, intitule: 'Cardiologie' })
    const { wrapper, router } = await mountForm({ resource: 'specialites' })
    await wrapper.get('input').setValue('Cardiologie')
    await wrapper.get('form').trigger('submit')

    expect(create).not.toHaveBeenCalled()
    expect(wrapper.get('[role="dialog"]').text()).toContain(
      'Confirmer la création',
    )
    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Créer')
      .trigger('click')
    await flushPromises()

    expect(create).toHaveBeenCalledWith('specialites', {
      intitule: 'Cardiologie',
    })
    expect(router.currentRoute.value.name).toBe('admin-list')
  })

  it('shows local and API Platform validation errors', async () => {
    const create = vi.spyOn(adminApi, 'create').mockRejectedValue({
      response: {
        data: {
          violations: [{ propertyPath: 'intitule', message: 'Existe déjà' }],
        },
      },
    })
    const { wrapper } = await mountForm({ resource: 'specialites' })
    await wrapper.get('form').trigger('submit')
    expect(wrapper.text()).toContain('Le champ « Intitulé » est obligatoire')

    await wrapper.get('input').setValue('Cardiologie')
    await wrapper.get('form').trigger('submit')
    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Créer')
      .trigger('click')
    await flushPromises()
    expect(create).toHaveBeenCalled()
    expect(wrapper.text()).toContain('intitule : Existe déjà')
  })

  it('edits active state and role without resending an empty password', async () => {
    vi.spyOn(adminApi, 'get').mockResolvedValue({
      id: 5,
      email: 'admin@chirorg.test',
      roles: ['ROLE_USER', 'ROLE_ADMIN'],
      actif: false,
    })
    const update = vi.spyOn(adminApi, 'update').mockResolvedValue({ id: 5 })
    const { wrapper } = await mountForm({ resource: 'utilisateurs', id: 5 })

    expect(wrapper.get('select').element.value).toBe('ROLE_ADMIN')
    expect(wrapper.get('input[type="checkbox"]').element.checked).toBe(false)
    await wrapper.get('input[type="checkbox"]').setValue(true)
    await wrapper.get('form').trigger('submit')
    await wrapper
      .findAll('button')
      .filter((button) => button.text() === 'Enregistrer')
      .at(-1)
      .trigger('click')
    await flushPromises()

    expect(update).toHaveBeenCalledWith('utilisateurs', 5, {
      email: 'admin@chirorg.test',
      roles: ['ROLE_ADMIN'],
      actif: true,
    })
  })

  it('offers only surgeries and materials from the selected surgeon speciality', async () => {
    const cardio = { id: 1, intitule: 'Cardiologie' }
    const uro = { id: 2, intitule: 'Urologie' }
    vi.spyOn(adminApi, 'list').mockImplementation(
      async (resource) =>
        ({
          chirurgiens: [
            { id: 10, prenom: 'Claire', nom: 'Martin', specialite: cardio },
          ],
          'chirurgie-modeles': [
            { id: 20, intitule: 'Pontage', specialite: cardio },
            { id: 21, intitule: 'Urétéroscopie', specialite: uro },
          ],
          materiels: [
            { id: 30, intitule: 'Sternotome', specialite: cardio },
            { id: 31, intitule: 'Urétéroscope', specialite: uro },
          ],
        })[resource],
    )
    const { wrapper } = await mountForm({ resource: 'listes-materiel' })
    await wrapper.findAll('select')[0].setValue('10')
    await flushPromises()

    expect(wrapper.text()).toContain('Pontage')
    expect(wrapper.text()).toContain('Sternotome')
    expect(wrapper.text()).not.toContain('Urétéroscopie')
    expect(wrapper.text()).not.toContain('Urétéroscope')
  })
})
