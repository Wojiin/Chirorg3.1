import { flushPromises, mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import AdminListView from '@/views/AdminListView.vue'
import { adminApi } from '@/services/adminApi'

const BaseComboboxStub = {
  props: ['modelValue', 'options'],
  emits: ['update:modelValue'],
  template: `
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option value="">Toutes</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  `,
}

describe('AdminListView speciality filter', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  it('shows only surgeons belonging to the selected speciality', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(
      async (resource, params = {}) => {
        if (resource === 'specialites') {
          return [
            { id: 12, intitule: 'Orthopédie' },
            { id: 13, intitule: 'Cardiologie' },
          ]
        }
        const surgeons = [
          {
            id: 1,
            prenom: 'Jean',
            nom: 'Dupont',
            specialite: { id: 12, intitule: 'Orthopédie' },
          },
          {
            id: 2,
            prenom: 'Alice',
            nom: 'Martin',
            specialite: { id: 13, intitule: 'Cardiologie' },
          },
        ]
        return params.specialite
          ? surgeons.filter(
              (item) =>
                String(item.specialite.id) === String(params.specialite),
            )
          : surgeons
      },
    )

    const wrapper = mount(AdminListView, {
      props: { resourceSlug: 'chirurgiens' },
      global: {
        plugins: [createPinia()],
        stubs: {
          RouterLink: { template: '<a><slot /></a>' },
          BaseCombobox: BaseComboboxStub,
        },
      },
    })
    await flushPromises()

    expect(wrapper.text()).toContain('Retour aux référentiels')
    await wrapper.get('select').setValue('12')

    expect(wrapper.text()).toContain('Jean Dupont')
    expect(wrapper.text()).not.toContain('Alice Martin')
  })

  it('filters surgery models using their speciality', async () => {
    const list = vi
      .spyOn(adminApi, 'list')
      .mockImplementation(async (resource) => {
        if (resource === 'specialites') {
          return [{ id: 12, intitule: 'Orthopédie' }]
        }
        return []
      })

    const wrapper = mount(AdminListView, {
      props: { resourceSlug: 'chirurgie-modeles' },
      global: {
        plugins: [createPinia()],
        stubs: {
          RouterLink: { template: '<a><slot /></a>' },
          BaseCombobox: BaseComboboxStub,
        },
      },
    })
    await flushPromises()

    await wrapper.get('select').setValue('12')
    await flushPromises()

    expect(list).toHaveBeenCalledWith('chirurgie-modeles', {
      page: 1,
      itemsPerPage: 10,
      q: undefined,
      specialite: '12',
      chirurgien: undefined,
    })
  })

  it('renders technical sheets grouped by surgery', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) => {
      if (resource === 'specialites') {
        return [{ id: 12, intitule: 'Orthopédie' }]
      }
      return [
        {
          id: 21,
          titre: 'Installation du patient',
          description: 'Installer le patient en décubitus dorsal.',
          ordre: 1,
          chirurgieModele: {
            id: 7,
            intitule: 'Arthroscopie',
            specialite: { id: 12, intitule: 'Orthopédie' },
          },
        },
      ]
    })

    const wrapper = mount(AdminListView, {
      props: { resourceSlug: 'fiches-techniques' },
      global: {
        plugins: [createPinia()],
        stubs: {
          RouterLink: { template: '<a><slot /></a>' },
          BaseCombobox: BaseComboboxStub,
        },
      },
    })
    await flushPromises()

    expect(wrapper.text()).toContain('Arthroscopie')
    expect(wrapper.text()).toContain('Installation du patient')
    expect(wrapper.text()).toContain(
      'Installer le patient en décubitus dorsal.',
    )
  })

  it('requests the speciality CRUD without the technical fallback', async () => {
    const list = vi.spyOn(adminApi, 'list').mockResolvedValue([])

    mount(AdminListView, {
      props: { resourceSlug: 'specialites' },
      global: {
        plugins: [createPinia()],
        stubs: {
          RouterLink: { template: '<a><slot /></a>' },
          BaseCombobox: BaseComboboxStub,
        },
      },
    })
    await flushPromises()

    expect(list).toHaveBeenCalledWith('specialites', {
      page: 1,
      itemsPerPage: 10,
      q: undefined,
      specialite: undefined,
      chirurgien: undefined,
      masquerSansSpecialite: true,
    })
  })

  it('filters material lists using the surgery speciality', async () => {
    const list = vi
      .spyOn(adminApi, 'list')
      .mockImplementation(async (resource, params = {}) => {
        if (resource === 'specialites') {
          return [
            { id: 12, intitule: 'Orthopédie' },
            { id: 13, intitule: 'Cardiologie' },
          ]
        }
        const lists = [
          {
            id: 31,
            intitule: 'Liste orthopédique',
            chirurgien: {
              id: 1,
              specialite: { id: 12, intitule: 'Orthopédie' },
            },
            chirurgieModele: {
              id: 5,
              specialite: { id: 13, intitule: 'Cardiologie' },
            },
          },
          {
            id: 32,
            intitule: 'Liste cardiaque',
            chirurgien: {
              id: 2,
              specialite: { id: 13, intitule: 'Cardiologie' },
            },
            chirurgieModele: {
              id: 6,
              specialite: { id: 12, intitule: 'Orthopédie' },
            },
          },
        ]
        return params.specialite
          ? lists.filter(
              (item) =>
                String(item.chirurgieModele.specialite.id) ===
                String(params.specialite),
            )
          : lists
      })

    const wrapper = mount(AdminListView, {
      props: { resourceSlug: 'listes-materiel' },
      global: {
        plugins: [createPinia()],
        stubs: {
          RouterLink: { template: '<a><slot /></a>' },
          BaseCombobox: BaseComboboxStub,
        },
      },
    })
    await flushPromises()

    await wrapper.get('select').setValue('12')
    await flushPromises()

    expect(wrapper.text()).toContain('Liste cardiaque')
    expect(wrapper.text()).not.toContain('Liste orthopédique')
    expect(list).toHaveBeenCalledWith('listes-materiel', {
      page: 1,
      itemsPerPage: 10,
      q: undefined,
      specialite: '12',
      chirurgien: undefined,
    })
  })
})
