import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  login: {
    error: '',
    form: { email: '', password: '' },
    loading: false,
    submit: vi.fn(),
  },
  programmeList: {
    clearFilters: vi.fn(),
    error: '',
    filteredProgrammes: [],
    filters: { date: '', room: '' },
    loading: false,
    loadProgrammes: vi.fn(),
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    rooms: ['Salle A'],
  },
  detail: {
    deletingSurgeryId: null,
    pendingSurgeryRemoval: null,
    requestSurgeryRemoval: vi.fn(),
    cancelSurgeryRemoval: vi.fn(),
    confirmSurgeryRemoval: vi.fn(),
    error: '',
    loading: false,
    programme: null,
    reorder: vi.fn(),
    savingProgrammeId: null,
  },
  preparation: {
    error: '',
    goBack: vi.fn(),
    isPartial: false,
    isResolved: true,
    loading: false,
    preparation: null,
    progress: { total: 1, coches: 1, traites: 1 },
    savingId: null,
    setMaterialState: vi.fn(),
    surgery: null,
    validate: vi.fn(),
  },
  final: {
    error: '',
    goBack: vi.fn(),
    loading: false,
    view: null,
  },
}))

vi.mock('@/composables/useLoginView', () => ({
  useLoginView: () => mocks.login,
}))
vi.mock('@/composables/useProgrammeOperatoireView', () => ({
  useProgrammeOperatoireView: () => mocks.programmeList,
}))
vi.mock('@/composables/useProgrammeDetailView', () => ({
  useProgrammeDetailView: () => mocks.detail,
}))
vi.mock('@/composables/usePreparationView', () => ({
  usePreparationView: () => mocks.preparation,
}))
vi.mock('@/composables/useVueFinaleView', () => ({
  useVueFinaleView: () => mocks.final,
}))

import LoginView from '@/views/LoginView.vue'
import AdminDashboardView from '@/views/AdminDashboardView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
import ProgrammeOperatoireView from '@/views/ProgrammeOperatoireView.vue'
import ProgrammeDetailView from '@/views/ProgrammeDetailView.vue'
import PreparationView from '@/views/PreparationView.vue'
import VueFinaleView from '@/views/VueFinaleView.vue'

const routerLink = {
  props: ['to'],
  template: '<a href="#"><slot /></a>',
}

const surgery = {
  id: 42,
  date: '2030-01-15',
  dateProgrammee: '2030-01-15',
  salle: 'Salle A',
  ordre: 1,
  nombreChirurgies: 5,
  valide: false,
  chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
  chirurgieModele: { id: 3, intitule: 'Intervention test' },
}

const mountView = (component, props = {}) =>
  mount(component, {
    props,
    global: { stubs: { RouterLink: routerLink } },
  })

describe('reference workflow views', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mocks.login.error = ''
    mocks.programmeList.error = ''
    mocks.programmeList.loading = false
    mocks.programmeList.filters.date = ''
    mocks.programmeList.filters.room = ''
    mocks.programmeList.filteredProgrammes = []
    mocks.detail.error = ''
    mocks.detail.loading = false
    mocks.detail.programme = null
    mocks.detail.pendingSurgeryRemoval = null
    mocks.preparation.error = ''
    mocks.preparation.loading = false
    mocks.preparation.isPartial = false
    mocks.preparation.isResolved = true
    mocks.preparation.surgery = null
    mocks.preparation.preparation = null
    mocks.final.error = ''
    mocks.final.loading = false
    mocks.final.view = null
  })

  it('renders and submits the reference login form', async () => {
    mocks.login.error = 'Identifiants invalides.'
    const wrapper = mountView(LoginView, { redirect: '/programme' })
    await wrapper.get('input[type="email"]').setValue('user@chirorg.test')
    await wrapper.get('input[type="password"]').setValue('secret')
    await wrapper.get('form').trigger('submit')

    expect(wrapper.text()).toContain('Connexion')
    expect(wrapper.text()).toContain('Identifiants invalides.')
    expect(mocks.login.submit).toHaveBeenCalledOnce()
  })

  it('renders every administrator resource card', () => {
    const wrapper = mountView(AdminDashboardView)
    expect(wrapper.text()).toContain('Administration')
    expect(wrapper.findAll('.resource-card')).toHaveLength(7)
  })

  it('renders the not-found page and its return navigation', () => {
    const wrapper = mountView(NotFoundView)
    expect(wrapper.text()).toContain('Cette page n’existe pas')
    expect(wrapper.get('a').text()).toContain('programme')
  })

  it('renders empty, loading, error and populated programme states', async () => {
    let wrapper = mountView(ProgrammeOperatoireView)
    expect(wrapper.text()).toContain('Aucun programme planifié')

    mocks.programmeList.loading = true
    wrapper = mountView(ProgrammeOperatoireView)
    expect(wrapper.text()).toContain('Chargement du programme opératoire')

    mocks.programmeList.loading = false
    mocks.programmeList.error = 'API indisponible'
    wrapper = mountView(ProgrammeOperatoireView)
    expect(wrapper.text()).toContain('API indisponible')

    mocks.programmeList.error = ''
    mocks.programmeList.filters.room = 'Salle A'
    mocks.programmeList.filteredProgrammes = [
      { id: 'p1', ...surgery, chirurgies: [surgery] },
    ]
    wrapper = mountView(ProgrammeOperatoireView)
    expect(wrapper.text()).toContain('Lovelace Ada')
    await wrapper.get('button').trigger('click')
    expect(mocks.programmeList.clearFilters).toHaveBeenCalledOnce()
  })

  it('renders programme detail and delegates confirmation actions', async () => {
    mocks.detail.programme = {
      id: 'p1',
      date: surgery.date,
      salle: surgery.salle,
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery],
    }
    mocks.detail.pendingSurgeryRemoval = surgery
    const wrapper = mountView(ProgrammeDetailView, {
      date: surgery.date,
      salle: surgery.salle,
      chirurgienId: 7,
    })
    await flushPromises()

    expect(wrapper.text()).toContain('Détail du programme')
    expect(wrapper.text()).toContain('Intervention test')
    const cancelButton = [
      ...document.body.querySelectorAll('[role="dialog"] button'),
    ].find((button) => button.textContent.includes('Annuler'))
    cancelButton.click()
    expect(mocks.detail.cancelSurgeryRemoval).toHaveBeenCalledOnce()
    wrapper.unmount()
  })

  it('renders the preparation checklist and delegates state changes', async () => {
    mocks.preparation.surgery = surgery
    mocks.preparation.preparation = {
      chirurgie: surgery,
      preparations: [
        {
          id: 9,
          coche: false,
          absent: false,
          materiel: { intitule: 'Scalpel', adresse: 'A-1', type: 'Instrument' },
        },
      ],
    }
    const wrapper = mountView(PreparationView, { id: 42 })

    expect(wrapper.text()).toContain('Matériel à préparer')
    expect(wrapper.text()).toContain('Intervention 1 sur 5')
    await wrapper.findAll('input')[0].setValue(true)
    await wrapper.findAll('button')[0].trigger('click')
    expect(mocks.preparation.setMaterialState).toHaveBeenCalled()
  })

  it('renders the validated read-only final view', async () => {
    mocks.final.view = {
      chirurgie: { ...surgery, valide: true, valideLe: '2030-01-15T10:00:00Z' },
      materiels: [
        {
          id: 9,
          materiel: { intitule: 'Scalpel', adresse: 'A-1', type: 'Instrument' },
        },
      ],
      fichesTechniques: [
        {
          id: 3,
          ordre: 1,
          titre: 'Installation',
          contenu: 'Installer le patient.',
        },
      ],
    }
    const wrapper = mountView(VueFinaleView, { id: 42 })

    expect(wrapper.text()).toContain('lecture seule')
    expect(wrapper.text()).toContain('Matériel validé')
    expect(wrapper.text()).toContain('Fiche technique')
    await wrapper.get('button').trigger('click')
    expect(mocks.final.goBack).toHaveBeenCalledOnce()
  })
})
