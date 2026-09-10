import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ProgrammeSummaryCard from '@/components/ProgrammeSummaryCard.vue'

describe('ProgrammeSummaryCard', () => {
  it('affiche la progression agrégée du programme avec son pourcentage', () => {
    const wrapper = mount(ProgrammeSummaryCard, {
      props: {
        programme: {
          date: '2030-01-15',
          salle: 'Salle A',
          chirurgien: { id: 1, prenom: 'Ada', nom: 'Lovelace' },
          creePar: 'user@chirorg.test',
          progressionPreparation: { total: 8, coches: 6, complete: false },
        },
      },
      global: {
        stubs: { RouterLink: { template: '<a><slot /></a>' } },
      },
    })

    expect(wrapper.text()).toContain('Avancement du programme')
    expect(wrapper.text()).toContain('75 %')
    expect(
      wrapper.get('[role="progressbar"]').attributes('aria-valuenow'),
    ).toBe('6')
  })
})
