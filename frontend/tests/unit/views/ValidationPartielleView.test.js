import { flushPromises, mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import ValidationPartielleView from '@/views/ValidationPartielleView.vue'
import { preparationApi } from '@/services/preparationApi'
import { technicalSheetApi } from '@/services/technicalSheetApi'

const preparation = {
  id: 42,
  dateProgrammee: '2030-01-15',
  salle: 'Salle B',
  ordre: 1,
  valide: false,
  etatValidation: 'VALIDATION_PARTIELLE',
  chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
  chirurgieModele: { id: 2, intitule: 'Intervention' },
  preparationsMateriel: [
    {
      id: 10,
      coche: false,
      absent: true,
      materiel: { id: 3, intitule: 'Scalpel', typeMateriel: 'Instrument' },
    },
  ],
  progressionPreparation: { total: 1, coches: 0, absents: 1, traites: 1 },
}

async function mountView() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: '/chirurgies/:id/validation-partielle',
        name: 'validation-partielle',
        component: { template: '<div />' },
      },
      {
        path: '/chirurgies/:id/preparation',
        name: 'preparation',
        component: { template: '<div />' },
      },
      {
        path: '/chirurgies/:id/vue-finale',
        name: 'vue-finale',
        component: { template: '<div />' },
      },
      {
        path: '/programmes/:date/:salle/:chirurgien',
        name: 'programme-detail',
        component: { template: '<div />' },
      },
    ],
  })
  await router.push('/chirurgies/42/validation-partielle')
  await router.isReady()

  return mount(ValidationPartielleView, {
    props: { id: 42 },
    global: { plugins: [createPinia(), router] },
  })
}

describe('ValidationPartielleView', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.spyOn(preparationApi, 'getPreparation').mockResolvedValue(preparation)
  })

  it('loads and displays the technical sheets of the surgery model', async () => {
    const listTechnicalSheets = vi
      .spyOn(technicalSheetApi, 'listForSurgeryModel')
      .mockResolvedValue([
        {
          id: 8,
          titre: 'Installation',
          description: 'Installer le patient.',
          lienImage: null,
          ordre: 1,
        },
      ])

    const wrapper = await mountView()
    await flushPromises()

    expect(listTechnicalSheets).toHaveBeenCalledWith(2)
    expect(wrapper.text()).toContain('Fiche technique')
    expect(wrapper.text()).toContain('Installation')
    expect(wrapper.text()).toContain('Installer le patient.')
    expect(
      wrapper.findAll('button').some((button) => button.text() === 'Retour'),
    ).toBe(true)
  })
})
