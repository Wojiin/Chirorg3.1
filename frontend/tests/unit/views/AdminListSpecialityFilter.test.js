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
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) => {
      if (resource === 'specialites') {
        return [
          { id: 12, intitule: 'Orthopédie' },
          { id: 13, intitule: 'Cardiologie' },
        ]
      }
      return [
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
    })

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

    await wrapper.get('select').setValue('12')

    expect(wrapper.text()).toContain('Jean Dupont')
    expect(wrapper.text()).not.toContain('Alice Martin')
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
      specialite: '12',
      chirurgien: undefined,
    })
  })
})
