import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import BaseCombobox from '@/components/ui/BaseCombobox.vue'

describe('BaseCombobox', () => {
  it('associates its label and exposes validation errors', () => {
    const wrapper = mount(BaseCombobox, {
      props: {
        modelValue: '',
        label: 'Spécialité',
        options: [{ value: 1, label: 'Orthopédie' }],
        error: 'Ce champ est obligatoire.',
        'onUpdate:modelValue': () => {},
      },
      global: {
        stubs: { ComboboxPortal: { template: '<div><slot /></div>' } },
      },
    })

    const input = wrapper.get('input')
    expect(wrapper.get('label').attributes('for')).toBe(input.attributes('id'))
    expect(input.attributes('aria-invalid')).toBe('true')
    expect(wrapper.text()).toContain('Ce champ est obligatoire.')
  })

  it('updates the model when an option is selected', async () => {
    const wrapper = mount(BaseCombobox, {
      props: {
        modelValue: '',
        label: 'Spécialité',
        options: [
          { value: 1, label: 'Orthopédie' },
          { value: 2, label: 'Cardiologie' },
        ],
        'onUpdate:modelValue': (value) =>
          wrapper.setProps({ modelValue: value }),
      },
      global: {
        stubs: { ComboboxPortal: { template: '<div><slot /></div>' } },
      },
    })

    await wrapper.get('input').trigger('focus')
    await wrapper.findAll('[role="option"]')[1].trigger('click')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([2])
  })

  it('clears an optional filter without rendering an empty option', async () => {
    const wrapper = mount(BaseCombobox, {
      props: {
        modelValue: 1,
        label: 'Spécialité',
        options: [{ value: 1, label: 'Orthopédie' }],
        allowEmpty: true,
        'onUpdate:modelValue': (value) =>
          wrapper.setProps({ modelValue: value }),
      },
      global: {
        stubs: { ComboboxPortal: { template: '<div><slot /></div>' } },
      },
    })

    expect(
      wrapper
        .findAll('[role="option"]')
        .every((option) => option.attributes('data-value') !== ''),
    ).toBe(true)
    await wrapper
      .get('button[aria-label="Effacer le filtre Spécialité"]')
      .trigger('click')

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([''])
  })
})
