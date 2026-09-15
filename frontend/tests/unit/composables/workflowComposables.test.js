import { defineComponent, nextTick } from 'vue'
import { flushPromises, mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const router = vi.hoisted(() => ({
  push: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
}))

vi.mock('vue-router', async (importOriginal) => ({
  ...(await importOriginal()),
  useRouter: () => router,
}))

import { useAdminFormView } from '@/composables/useAdminFormView'
import { useLoginView } from '@/composables/useLoginView'
import { usePlanificationView } from '@/composables/usePlanificationView'
import { usePreparationView } from '@/composables/usePreparationView'
import { useProgrammeDetailView } from '@/composables/useProgrammeDetailView'
import { useProgrammeOperatoireView } from '@/composables/useProgrammeOperatoireView'
import { useVueFinaleView } from '@/composables/useVueFinaleView'
import { accountApi } from '@/services/accountApi'
import { adminApi } from '@/services/adminApi'
import { authApi } from '@/services/authApi'
import { preparationApi } from '@/services/preparationApi'
import { programmeApi } from '@/services/programmeApi'
import { technicalSheetApi } from '@/services/technicalSheetApi'

const surgery = {
  id: 42,
  ordre: 1,
  dateProgrammee: '2030-01-15',
  salle: 'Salle A',
  valide: false,
  chirurgien: { id: 7, prenom: 'Ada', nom: 'Lovelace' },
  chirurgieModele: { id: 3, intitule: 'Intervention test' },
  preparationsMateriel: [
    {
      id: 9,
      coche: false,
      absent: false,
      materiel: { id: 5, intitule: 'Scalpel', typeMateriel: 'Instrument' },
    },
  ],
  progressionPreparation: {
    total: 1,
    coches: 0,
    absents: 0,
    traites: 0,
    complete: false,
  },
}

function mountComposable(factory, props = {}) {
  let result
  const component = defineComponent({
    props: Object.fromEntries(
      Object.keys(props).map((key) => [key, { default: props[key] }]),
    ),
    setup(componentProps) {
      result = factory(componentProps)
      return () => null
    },
  })
  const wrapper = mount(component, { props })
  return { result, wrapper }
}

describe('workflow composables', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  it('rejects an unknown administrator resource', async () => {
    const { result } = mountComposable(useAdminFormView, {
      resourceSlug: 'inconnue',
      id: null,
    })
    await flushPromises()
    expect(result.displayedError.value).toContain('n’existe pas')
    expect(result.fields.value).toEqual([])
  })

  it('validates and uploads a technical-sheet illustration before creation', async () => {
    vi.spyOn(adminApi, 'list').mockResolvedValue([
      { id: 3, intitule: 'Intervention test' },
    ])
    vi.spyOn(adminApi, 'create').mockResolvedValue({ id: 12 })
    const upload = vi
      .spyOn(technicalSheetApi, 'uploadImage')
      .mockResolvedValue('/uploads/fiche.webp')
    const { result } = mountComposable(useAdminFormView, {
      resourceSlug: 'fiches-techniques',
      id: null,
    })
    await flushPromises()

    result.submit()
    expect(result.displayedError.value).toContain('Titre')
    Object.assign(result.form, {
      titre: 'Installation',
      ordre: 1,
      chirurgieModele: 3,
    })
    result.submit()
    expect(result.displayedError.value).toContain('consigne écrite')

    const invalidTarget = {
      files: [new File(['x'], 'fiche.gif', { type: 'image/gif' })],
      value: 'selected',
    }
    result.selectImage({ target: invalidTarget })
    expect(result.displayedError.value).toContain('JPEG')
    expect(invalidTarget.value).toBe('')

    const oversizedTarget = {
      files: [
        new File([new Uint8Array(5 * 1024 * 1024 + 1)], 'fiche.png', {
          type: 'image/png',
        }),
      ],
      value: 'selected',
    }
    result.selectImage({ target: oversizedTarget })
    expect(result.displayedError.value).toContain('5 Mo')

    const image = new File(['image'], 'fiche.webp', { type: 'image/webp' })
    result.selectImage({ target: { files: [image], value: 'selected' } })
    result.submit()
    expect(result.confirmationOpen.value).toBe(true)
    result.cancelConfirmation()
    expect(result.confirmationOpen.value).toBe(false)
    result.submit()
    await result.confirmSubmit()

    expect(upload).toHaveBeenCalledWith(image)
    expect(adminApi.create).toHaveBeenCalledWith(
      'fiches-techniques',
      expect.objectContaining({ lienImage: '/uploads/fiche.webp' }),
    )
    expect(router.push).toHaveBeenCalledWith({
      name: 'admin-list',
      params: { resource: 'fiches-techniques' },
    })
    result.removeImage()
    expect(result.form.lienImage).toBeNull()
    expect(result.fileInputKey.value).toBe(1)
  })

  it('keeps the administrator form open when image upload fails', async () => {
    vi.spyOn(adminApi, 'list').mockResolvedValue([
      { id: 3, intitule: 'Intervention test' },
    ])
    vi.spyOn(technicalSheetApi, 'uploadImage').mockRejectedValue(
      new Error('upload'),
    )
    const { result } = mountComposable(useAdminFormView, {
      resourceSlug: 'fiches-techniques',
      id: null,
    })
    await flushPromises()
    Object.assign(result.form, {
      titre: 'Installation',
      ordre: 1,
      chirurgieModele: 3,
      imageFile: new File(['image'], 'fiche.png', { type: 'image/png' }),
    })
    result.submit()
    await result.confirmSubmit()
    expect(result.displayedError.value).toContain('téléversée')
    expect(router.push).not.toHaveBeenCalled()
    await result.goBack()
    expect(router.push).toHaveBeenCalledWith({
      name: 'admin-list',
      params: { resource: 'fiches-techniques' },
    })
  })

  it('submits login and only accepts a local redirect', async () => {
    vi.spyOn(authApi, 'login').mockResolvedValue({ token: 'jwt' })
    vi.spyOn(accountApi, 'getCurrent').mockResolvedValue({
      email: 'user@chirorg.test',
      roles: ['ROLE_USER'],
    })
    let mounted = mountComposable(useLoginView, { redirect: '/compte' })
    mounted.result.form.email = 'user@chirorg.test'
    mounted.result.form.password = 'secret'
    await mounted.result.submit()
    expect(router.replace).toHaveBeenCalledWith('/compte')
    mounted.wrapper.unmount()

    mounted = mountComposable(useLoginView, { redirect: '//evil.test' })
    await mounted.result.submit()
    expect(router.replace).toHaveBeenLastCalledWith({ name: 'programme' })
  })

  it('loads programme summaries, reacts to filters and clears them', async () => {
    const list = vi.spyOn(programmeApi, 'list').mockResolvedValue([])
    const { result } = mountComposable(useProgrammeOperatoireView)
    await flushPromises()
    expect(list).toHaveBeenCalled()

    result.filters.room = 'Salle A'
    await nextTick()
    await flushPromises()
    result.clearFilters()
    await nextTick()
    expect(result.filters).toEqual({ date: '', room: '' })
    await result.loadProgrammes()
  })

  it('validates speciality-dependent planning before creating a programme', async () => {
    vi.spyOn(adminApi, 'list').mockImplementation(
      async (resource) =>
        ({
          specialites: [
            { id: 2, intitule: 'Orthopédie' },
            { id: 1, intitule: 'Cardiologie' },
          ],
          chirurgiens: [
            { id: 7, prenom: 'Ada', nom: 'Lovelace', specialite: { id: 2 } },
          ],
          'chirurgie-modeles': [
            { id: 3, intitule: 'Prothèse', specialite: { id: 2 } },
          ],
        })[resource] ?? [],
    )
    const plan = vi.spyOn(programmeApi, 'planProgram').mockResolvedValue({
      date: '2030-01-15',
      salle: 'Salle A',
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery],
    })
    const { result } = mountComposable(usePlanificationView)
    await flushPromises()

    expect(result.specialties.value.map((item) => item.label)).toEqual([
      'Cardiologie',
      'Orthopédie',
    ])
    await result.submit()
    expect(result.displayedError.value).toContain('obligatoires')

    result.form.specialiteId = 2
    await nextTick()
    expect(result.surgeons.value[0].label).toBe('Dr Ada Lovelace')
    expect(result.surgeries.value[0].label).toBe('Prothèse')
    result.form.chirurgienId = 999
    result.form.chirurgieModeleIds = [3]
    await result.submit()
    expect(result.displayedError.value).toContain('correspondre')

    result.form.chirurgienId = 7
    result.form.dateProgrammee = '2000-01-01'
    await result.submit()
    expect(result.displayedError.value).toContain('minimum')

    result.form.dateProgrammee = result.minimumDate
    result.addSurgery()
    expect(result.form.chirurgieModeleIds).toHaveLength(2)
    result.removeSurgery(1)
    result.removeSurgery(0)
    expect(result.form.chirurgieModeleIds).toEqual([3])
    await result.submit()
    expect(plan).toHaveBeenCalledWith({
      chirurgienId: 7,
      chirurgieModeleIds: [3],
      dateProgrammee: result.minimumDate,
      salle: 'Salle A',
    })
    expect(router.push).toHaveBeenCalledWith({ name: 'programme' })
    result.cancel()
    expect(router.back).toHaveBeenCalled()
  })

  it('loads, reorders and removes surgeries from programme detail', async () => {
    vi.spyOn(programmeApi, 'getProgramme').mockResolvedValue({
      date: surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery],
    })
    vi.spyOn(programmeApi, 'reorder').mockImplementation(async (payload) => ({
      date: payload.date,
      salle: payload.salle,
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery],
    }))
    vi.spyOn(programmeApi, 'deleteSurgery').mockResolvedValue()
    const { result } = mountComposable(useProgrammeDetailView, {
      date: surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgienId: 7,
    })
    await flushPromises()

    expect(result.reorder([42])).toBeInstanceOf(Promise)
    result.requestSurgeryRemoval({ ...surgery, valide: true })
    expect(result.pendingSurgeryRemoval.value).toBeNull()
    result.requestSurgeryRemoval(surgery)
    expect(result.pendingSurgeryRemoval.value.id).toBe(42)
    await result.confirmSurgeryRemoval()
    expect(router.push).toHaveBeenCalledWith({ name: 'programme' })
    expect(await result.confirmSurgeryRemoval()).toBe(false)
  })

  it('adds a surgery from programme detail using its existing context', async () => {
    vi.spyOn(programmeApi, 'getProgramme').mockResolvedValue({
      id: '2030-01-15|Salle A|7',
      date: surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery],
    })
    vi.spyOn(adminApi, 'list').mockImplementation(async (resource) => {
      if (resource === 'chirurgiens') {
        return [
          {
            ...surgery.chirurgien,
            specialite: { id: 2, intitule: 'Orthopédie' },
          },
        ]
      }
      if (resource === 'chirurgie-modeles') {
        return [
          { id: 3, intitule: 'Prothèse', specialite: { id: 2 } },
          { id: 4, intitule: 'Pontage', specialite: { id: 9 } },
        ]
      }
      return []
    })
    const plan = vi.spyOn(programmeApi, 'planProgram').mockResolvedValue({
      id: '2030-01-15|Salle A|7',
      date: surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgien: surgery.chirurgien,
      chirurgies: [surgery, { ...surgery, id: 43, ordre: 2 }],
    })
    const { result } = mountComposable(useProgrammeDetailView, {
      date: surgery.dateProgrammee,
      salle: surgery.salle,
      chirurgienId: 7,
    })
    await flushPromises()

    await result.openAddSurgeryForm()
    expect(result.speciality.value.intitule).toBe('Orthopédie')
    expect(result.surgeryModels.value).toEqual([
      { value: 3, label: 'Prothèse' },
    ])
    expect(await result.submitSurgery()).toBe(false)
    expect(result.displayedAddSurgeryError.value).toContain('Sélectionnez')

    result.chirurgieModeleId.value = 3
    expect(await result.submitSurgery()).toBe(true)
    expect(plan).toHaveBeenCalledWith({
      chirurgienId: 7,
      chirurgieModeleIds: [3],
      dateProgrammee: '2030-01-15',
      salle: 'Salle A',
    })
    expect(result.programme.value.chirurgies).toHaveLength(2)
    expect(result.addSurgeryFormOpen.value).toBe(false)
  })

  it('routes a loaded partial preparation and can return or validate it', async () => {
    vi.spyOn(preparationApi, 'getPreparation').mockResolvedValue({
      ...surgery,
      etatValidation: 'VALIDATION_PARTIELLE',
    })
    vi.spyOn(preparationApi, 'toggle').mockResolvedValue({
      ...surgery.preparationsMateriel[0],
      coche: true,
    })
    vi.spyOn(preparationApi, 'validate').mockResolvedValue({ valide: true })
    const { result } = mountComposable(usePreparationView, { id: 42 })
    await flushPromises()
    expect(router.replace).toHaveBeenCalledWith({
      name: 'validation-partielle',
      params: { id: 42 },
    })

    await result.setMaterialState(
      result.preparation.value.preparations[0],
      'ready',
    )
    await result.validate()
    expect(router.push).toHaveBeenCalledWith({
      name: 'vue-finale',
      params: { id: 42 },
    })
    result.goBack()
    expect(router.back).toHaveBeenCalled()
  })

  it('loads the final view and returns to its programme detail', async () => {
    vi.spyOn(preparationApi, 'getFinalView').mockResolvedValue({
      ...surgery,
      valide: true,
      materielsValides: [],
      ficheTechnique: [],
    })
    const { result } = mountComposable(useVueFinaleView, { id: 42 })
    await flushPromises()
    expect(result.view.value.chirurgie.id).toBe(42)
    await result.goBack()
    expect(router.push).toHaveBeenCalledWith({
      name: 'programme-detail',
      params: { date: '2030-01-15', salle: 'Salle A', chirurgien: 7 },
    })
  })

  it('falls back to the programme list when no final view exists', () => {
    vi.spyOn(preparationApi, 'getFinalView').mockRejectedValue(new Error('API'))
    const { result } = mountComposable(useVueFinaleView, { id: 404 })
    result.goBack()
    expect(router.push).toHaveBeenCalledWith({ name: 'programme' })
  })
})
