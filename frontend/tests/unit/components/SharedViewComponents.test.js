import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import PageHeading from '@/components/ui/PageHeading.vue'
import SurgeryOverview from '@/components/SurgeryOverview.vue'

describe('shared view components', () => {
  it('renders a page heading and its optional action', () => {
    const wrapper = mount(PageHeading, {
      props: {
        eyebrow: 'Administration',
        title: 'Matériels',
        description: 'Gérer les matériels.',
      },
      slots: { action: '<a href="/admin">Retour</a>' },
    })

    expect(wrapper.text()).toContain('Administration')
    expect(wrapper.text()).toContain('Matériels')
    expect(wrapper.get('a').text()).toBe('Retour')
  })

  it('renders the common surgery identity and metadata', () => {
    const wrapper = mount(SurgeryOverview, {
      props: {
        status: 'Validation partielle',
        surgery: {
          date: '2030-01-15',
          salle: 'Salle B',
          chirurgien: { prenom: 'Ada', nom: 'Lovelace' },
          chirurgieModele: { intitule: 'Intervention' },
        },
      },
    })

    expect(wrapper.text()).toContain('Validation partielle')
    expect(wrapper.text()).toContain('Intervention')
    expect(wrapper.text()).toContain('Dr Ada Lovelace')
    expect(wrapper.text()).toContain('Salle B')
    expect(wrapper.text().indexOf('Dr Ada Lovelace')).toBeLessThan(
      wrapper.text().indexOf('Validation partielle'),
    )
  })
})
