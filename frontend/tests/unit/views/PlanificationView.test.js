import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createMemoryHistory, createRouter } from 'vue-router'
import PlanificationView from '@/views/PlanificationView.vue'
import { useReferenceStore } from '@/stores/references'

async function mountView() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const referenceStore = useReferenceStore()
  referenceStore.collections = {
    specialites: [
      { id: 1, intitule: 'Orthopédie' },
      { id: 2, intitule: 'Urologie' },
    ],
    chirurgiens: [
      { id: 10, prenom: 'Alice', nom: 'Ortho', specialite: { id: 1 } },
      { id: 20, prenom: 'Bob', nom: 'Uro', specialite: { id: 2 } },
    ],
    'chirurgie-modeles': [
      { id: 100, intitule: 'Prothèse', specialite: { id: 1 } },
      { id: 200, intitule: 'Néphrectomie', specialite: { id: 2 } },
    ],
  }

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/', component: PlanificationView }],
  })
  await router.push('/')
  await router.isReady()

  return mount(PlanificationView, { global: { plugins: [pinia, router] } })
}

describe('PlanificationView', () => {
  it('filtre les chirurgiens et les interventions selon la spécialité', async () => {
    const wrapper = await mountView()
    let selects = wrapper.findAll('select')

    expect(selects[1].attributes('disabled')).toBeDefined()
    expect(selects[3].attributes('disabled')).toBeDefined()

    await selects[0].setValue('1')
    selects = wrapper.findAll('select')
    expect(selects[1].text()).toContain('Dr Alice Ortho')
    expect(selects[1].text()).not.toContain('Dr Bob Uro')
    expect(selects[3].text()).toContain('Prothèse')
    expect(selects[3].text()).not.toContain('Néphrectomie')

    await selects[1].setValue('10')
    await selects[3].setValue('100')
    await selects[0].setValue('2')
    selects = wrapper.findAll('select')

    expect(selects[1].element.value).toBe('')
    expect(selects[3].element.value).toBe('')
    expect(selects[1].text()).toContain('Dr Bob Uro')
    expect(selects[3].text()).toContain('Néphrectomie')
  })
})
