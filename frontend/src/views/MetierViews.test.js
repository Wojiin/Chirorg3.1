import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

import { adminApi } from '../services/admin.js'
import { preparationApi } from '../services/preparation.js'
import { programmeApi } from '../services/programme.js'
import PlanificationView from './PlanificationView.vue'
import PreparationView from './PreparationView.vue'
import ProgrammeDetailView from './ProgrammeDetailView.vue'
import ProgrammesView from './ProgrammesView.vue'
import VueFinaleView from './VueFinaleView.vue'

const specialite = { id: 1, intitule: 'Orthopédie' }
const chirurgien = {
  id: 2,
  prenom: 'Claire',
  nom: 'Martin',
  specialite,
}
const modele = { id: 3, intitule: 'Prothèse de hanche', specialite }
const autreSpecialite = { id: 5, intitule: 'Urologie' }
const autreChirurgien = {
  id: 6,
  prenom: 'Paul',
  nom: 'Robert',
  specialite: autreSpecialite,
}
const programme = {
  id: 'programme-1',
  date: '2027-01-12',
  salle: 'Salle A',
  chirurgien,
  nombreChirurgies: 2,
  nombreChirurgiesValidees: 0,
  progressionPreparation: { total: 2, traites: 0 },
  chirurgies: [
    {
      id: 10,
      ordre: 1,
      valide: false,
      etatValidation: 'EN_PREPARATION',
      chirurgieModele: modele,
      progressionPreparation: { total: 1, traites: 0 },
    },
    {
      id: 11,
      ordre: 2,
      valide: false,
      etatValidation: 'EN_PREPARATION',
      chirurgieModele: { ...modele, id: 4, intitule: 'Révision' },
      progressionPreparation: { total: 1, traites: 0 },
    },
  ],
}

function createTestRouter() {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'accueil', component: { template: '<div />' } },
      { path: '/programmes', name: 'programmes', component: ProgrammesView },
      {
        path: '/programmes/planifier',
        name: 'planification',
        component: PlanificationView,
      },
      {
        path: '/programmes/:date/:salle/:chirurgien',
        name: 'programme-detail',
        component: ProgrammeDetailView,
      },
      {
        path: '/chirurgies/:id/preparation',
        name: 'preparation',
        component: PreparationView,
      },
      {
        path: '/chirurgies/:id/vue-finale',
        name: 'vue-finale',
        component: VueFinaleView,
      },
    ],
  })
}

async function render(component, { props = {}, path = '/' } = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createTestRouter()
  await router.push(path)
  await router.isReady()
  const wrapper = mount(component, {
    props,
    global: { plugins: [pinia, router] },
  })
  await flushPromises()
  return { wrapper, router }
}

describe('business workflow views', () => {
  beforeEach(() => vi.restoreAllMocks())
  afterEach(() => vi.useRealTimers())

  it('lists programmes and filters surgeons by speciality', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) =>
      resource === 'specialites'
        ? [specialite, autreSpecialite]
        : [chirurgien, autreChirurgien],
    )
    const list = vi.spyOn(programmeApi, 'list').mockResolvedValue([
      programme,
      {
        ...programme,
        id: 'programme-2',
        chirurgien: autreChirurgien,
      },
    ])
    const { wrapper } = await render(ProgrammesView, { path: '/programmes' })

    expect(wrapper.text()).toContain('Dr Claire Martin')
    expect(wrapper.text()).toContain('Interventions2')
    const selects = wrapper.findAll('select')
    await selects[0].setValue('1')
    expect(wrapper.text()).not.toContain('Dr Paul Robert')
    await selects[1].setValue('2')
    await new Promise((resolve) => window.setTimeout(resolve, 220))
    await flushPromises()
    expect(list).toHaveBeenLastCalledWith(
      expect.objectContaining({ chirurgien: 2 }),
    )
  })

  it('plans several surgeries from matching references', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(
      async (resource) =>
        ({
          specialites: [specialite],
          chirurgiens: [chirurgien],
          'chirurgie-modeles': [modele],
        })[resource],
    )
    const create = vi.spyOn(programmeApi, 'create').mockResolvedValue(programme)
    const { wrapper, router } = await render(PlanificationView, {
      path: '/programmes/planifier',
    })

    const selects = wrapper.findAll('select')
    await selects[0].setValue('1')
    await flushPromises()
    await selects[1].setValue('2')
    await selects[2].setValue('3')
    await wrapper.get('input[type="date"]').setValue('2027-01-12')
    await wrapper.get('input[maxlength="50"]').setValue('Bloc 1')
    await wrapper.get('button.button-link').trigger('click')
    expect(wrapper.findAll('.surgery-picker__row')).toHaveLength(2)
    await wrapper
      .findAll('.surgery-picker__row')[1]
      .find('select')
      .setValue('3')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(create).toHaveBeenCalledWith({
      chirurgienId: 2,
      dateProgrammee: '2027-01-12',
      salle: 'Bloc 1',
      chirurgieModeleIds: [3, 3],
    })
    expect(router.currentRoute.value.name).toBe('programme-detail')
  })

  it('reorders and deletes an unvalidated surgery after confirmation', async () => {
    vi.spyOn(programmeApi, 'get').mockResolvedValue(programme)
    const reorder = vi.spyOn(programmeApi, 'reorder').mockResolvedValue({
      ...programme,
      chirurgies: [programme.chirurgies[1], programme.chirurgies[0]],
    })
    const remove = vi.spyOn(programmeApi, 'removeSurgery').mockResolvedValue()
    vi.spyOn(window, 'confirm')
      .mockReturnValueOnce(false)
      .mockReturnValueOnce(true)
    const { wrapper } = await render(ProgrammeDetailView, {
      props: { date: '2027-01-12', salle: 'Salle A', chirurgien: 2 },
    })

    await wrapper.findAll('button[aria-label="Descendre"]')[0].trigger('click')
    await flushPromises()
    expect(reorder).toHaveBeenCalledWith(
      expect.objectContaining({ chirurgieIds: [11, 10] }),
    )
    const deleteButton = wrapper
      .findAll('button')
      .find((item) => item.text() === 'Supprimer')
    await deleteButton.trigger('click')
    expect(remove).not.toHaveBeenCalled()
    await deleteButton.trigger('click')
    await flushPromises()
    expect(remove).toHaveBeenCalled()
  })

  it('prepares all material and navigates to the final view', async () => {
    vi.spyOn(preparationApi, 'get').mockResolvedValue({
      id: 10,
      dateProgrammee: '2027-01-12',
      salle: 'Salle A',
      valide: false,
      chirurgien,
      chirurgieModele: modele,
      preparationsMateriel: [
        {
          id: 20,
          coche: false,
          absent: false,
          materiel: { id: 30, intitule: 'Boîte', typeMateriel: 'Instrument' },
        },
      ],
      progressionPreparation: { total: 1, coches: 0, absents: 0, traites: 0 },
    })
    vi.spyOn(preparationApi, 'setState').mockResolvedValue({
      coche: true,
      absent: false,
    })
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({ valide: true })
    const { wrapper, router } = await render(PreparationView, {
      props: { id: 10 },
    })

    await wrapper
      .findAll('button')
      .find((item) => item.text() === 'Marquer prêt')
      .trigger('click')
    await flushPromises()
    await wrapper
      .findAll('button')
      .find((item) => item.text() === 'Valider la préparation')
      .trigger('click')
    await flushPromises()
    expect(router.currentRoute.value.name).toBe('vue-finale')
  })

  it('renders validated material and technical sheets', async () => {
    vi.spyOn(preparationApi, 'getFinalView').mockResolvedValue({
      id: 10,
      valide: true,
      chirurgieModele: modele,
      validePar: { email: 'user@chirorg.test' },
      materielsValides: [
        {
          id: 30,
          intitule: 'Boîte',
          typeMateriel: 'Instrument',
          adresse: 'A-1',
        },
      ],
      ficheTechnique: [
        { id: 40, titre: 'Installation', description: 'Installer le patient.' },
      ],
    })
    const { wrapper } = await render(VueFinaleView, { props: { id: 10 } })
    expect(wrapper.text()).toContain('Boîte')
    expect(wrapper.text()).toContain('Installation')
    expect(wrapper.text()).toContain('user@chirorg.test')
  })
})
