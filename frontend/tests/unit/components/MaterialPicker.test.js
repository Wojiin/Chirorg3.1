import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import MaterialPicker from '@/components/MaterialPicker.vue'

describe('MaterialPicker', () => {
  it('adds and removes materials by name without displaying their ids', async () => {
    const wrapper = mount(MaterialPicker, {
      props: {
        label: 'Composition de la liste',
        modelValue: [],
        options: [
          {
            value: 14752,
            label: 'Pince de préhension',
            meta: 'Digestif · Instrument',
          },
          {
            value: 29861,
            label: 'Champ opératoire',
            meta: 'Orthopédie · Consommable',
          },
        ],
        'onUpdate:modelValue': (value) =>
          wrapper.setProps({ modelValue: value }),
      },
    })

    expect(wrapper.text()).toContain('Pince de préhension')
    expect(wrapper.text()).not.toContain('14752')

    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Ajouter')
      .trigger('click')
    expect(wrapper.props('modelValue')).toEqual([14752])
    expect(wrapper.text()).toContain('Matériels ajoutés (1)')

    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Retirer')
      .trigger('click')
    expect(wrapper.props('modelValue')).toEqual([])
  })
})
