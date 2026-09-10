import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ProgrammeGroup from '@/components/ProgrammeGroup.vue'

function createProgramme() {
  return {
    id: '2030-01-15|Salle A|1',
    date: '2030-01-15',
    salle: 'Salle A',
    chirurgien: { id: 1, prenom: 'Ada', nom: 'Lovelace' },
    chirurgies: [
      {
        id: 10,
        ordre: 1,
        valide: false,
        chirurgien: { id: 1, prenom: 'Ada', nom: 'Lovelace' },
        chirurgieModele: { id: 2, intitule: 'Intervention A' },
        progressionPreparation: { total: 1, coches: 0, complete: false },
      },
      {
        id: 20,
        ordre: 2,
        valide: false,
        chirurgien: { id: 1, prenom: 'Ada', nom: 'Lovelace' },
        chirurgieModele: { id: 3, intitule: 'Intervention B' },
        progressionPreparation: { total: 1, coches: 0, complete: false },
      },
    ],
  }
}

function mountGroup() {
  return mount(ProgrammeGroup, {
    props: { programme: createProgramme() },
    global: {
      stubs: {
        RouterLink: { template: '<a><slot /></a>' },
      },
    },
  })
}

describe('ProgrammeGroup', () => {
  it('emits the full new order from the accessible move controls', async () => {
    const wrapper = mountGroup()

    await wrapper
      .get('button[aria-label="Déplacer Intervention A vers le bas"]')
      .trigger('click')

    expect(wrapper.emitted('reorder')).toEqual([[[20, 10]]])
  })

  it('emits the full new order after a drag and drop', async () => {
    const wrapper = mountGroup()
    let transferredId = ''
    const dataTransfer = {
      effectAllowed: 'none',
      setData: (_type, value) => {
        transferredId = value
      },
      getData: () => transferredId,
    }
    const items = wrapper.findAll('.draggable-programme-item')

    await items[0].trigger('dragstart', { dataTransfer })
    await items[1].trigger('dragover', { clientY: 1, dataTransfer })
    await items[1].trigger('drop', { clientY: 1, dataTransfer })

    expect(wrapper.emitted('reorder')).toEqual([[[20, 10]]])
  })

  it('emits the surgery selected from its trash action', async () => {
    const wrapper = mountGroup()

    await wrapper
      .get('button[aria-label="Supprimer Intervention A"]')
      .trigger('click')

    expect(wrapper.emitted('remove')).toEqual([
      [expect.objectContaining({ id: 10 })],
    ])
  })
})
