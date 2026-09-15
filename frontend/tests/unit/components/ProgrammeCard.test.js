import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ProgrammeCard from '@/components/ProgrammeCard.vue'

describe('ProgrammeCard', () => {
  it('keeps only truly ready materials green after validation', () => {
    const wrapper = mount(ProgrammeCard, {
      props: {
        chirurgie: {
          id: 42,
          valide: true,
          chirurgien: { prenom: 'Ada', nom: 'Lovelace' },
          chirurgieModele: { intitule: 'Intervention test' },
          progressionPreparation: {
            total: 4,
            coches: 2,
            absents: 2,
            traites: 4,
            complete: true,
          },
        },
      },
      global: {
        stubs: { RouterLink: { template: '<a><slot /></a>' } },
      },
    })

    const progressbar = wrapper.get('[role="progressbar"]')
    expect(progressbar.attributes('aria-valuenow')).toBe('2')
    expect(progressbar.attributes('aria-label')).toContain(
      '2 absents ou non préparés',
    )
    expect(progressbar.classes()).toContain('progress-track-danger')
    expect(wrapper.get('.progress-value').attributes('style')).toContain('50%')
  })
})
