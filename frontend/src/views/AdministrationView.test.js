import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import AdministrationView from './AdministrationView.vue'

describe('AdministrationView', () => {
  it('links to all seven administrative resources', () => {
    const wrapper = mount(AdministrationView, {
      global: {
        stubs: {
          RouterLink: {
            props: ['to'],
            template: '<a :data-to="JSON.stringify(to)"><slot /></a>',
          },
        },
      },
    })

    expect(wrapper.findAll('.resource-card')).toHaveLength(7)
    expect(wrapper.text()).toContain('Spécialités')
    expect(wrapper.text()).toContain('Utilisateurs')
  })
})
