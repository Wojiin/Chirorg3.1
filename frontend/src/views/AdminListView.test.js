import { flushPromises, mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

import { adminApi } from '../services/admin.js'
import AdminListView from './AdminListView.vue'

const specialities = [
  { id: 1, intitule: 'Cardiologie' },
  { id: 2, intitule: 'Urologie' },
]
const surgeons = [
  { id: 10, prenom: 'Claire', nom: 'Martin', specialite: specialities[0] },
  { id: 11, prenom: 'Sophie', nom: 'Robert', specialite: specialities[1] },
]

function mountList(resource = 'chirurgiens') {
  return mount(AdminListView, {
    props: { resource },
    global: {
      plugins: [createPinia()],
      stubs: {
        RouterLink: { props: ['to'], template: '<a><slot /></a>' },
        Teleport: true,
      },
    },
  })
}

describe('AdminListView', () => {
  beforeEach(() => vi.restoreAllMocks())

  it('loads, searches and filters a reference by speciality', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) =>
      resource === 'specialites' ? specialities : surgeons,
    )
    const wrapper = mountList()
    await flushPromises()

    expect(wrapper.text()).toContain('Claire Martin')
    expect(wrapper.text()).toContain('Sophie Robert')
    await wrapper.get('input[type="search"]').setValue('Claire')
    expect(wrapper.text()).not.toContain('Sophie Robert')
    await wrapper.get('input[type="search"]').setValue('')
    await wrapper.get('select').setValue('2')
    expect(wrapper.text()).not.toContain('Claire Martin')
    expect(wrapper.text()).toContain('Sophie Robert')
  })

  it('requires confirmation before deleting and updates the list', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) =>
      resource === 'specialites' ? specialities : surgeons,
    )
    const remove = vi.spyOn(adminApi, 'remove').mockResolvedValue()
    const wrapper = mountList()
    await flushPromises()

    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Supprimer')
      .trigger('click')
    expect(wrapper.get('[role="dialog"]').text()).toContain('Claire Martin')
    const confirm = wrapper
      .findAll('button')
      .filter((button) => button.text() === 'Supprimer')
      .at(-1)
    await confirm.trigger('click')
    await flushPromises()

    expect(remove).toHaveBeenCalledWith('chirurgiens', 10)
    expect(wrapper.text()).not.toContain('Claire Martin')
  })

  it('displays an API Platform error and rejects unknown resources', async () => {
    vi.spyOn(adminApi, 'list').mockRejectedValue({
      response: { data: { detail: 'Service indisponible' } },
    })
    const wrapper = mountList('utilisateurs')
    await flushPromises()
    expect(wrapper.text()).toContain('Service indisponible')

    await wrapper.setProps({ resource: 'inconnu' })
    expect(wrapper.text()).toContain('Ce référentiel n’existe pas')
  })
})
