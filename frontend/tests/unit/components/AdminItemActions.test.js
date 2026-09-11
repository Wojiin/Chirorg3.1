import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import AdminItemActions from '@/components/AdminItemActions.vue'

function mountActions(item) {
  return mount(AdminItemActions, {
    props: {
      resourceSlug: 'specialites',
      item,
      title: item.intitule,
    },
    global: {
      stubs: { RouterLink: { template: '<a><slot /></a>' } },
    },
  })
}

describe('admin item actions', () => {
  it('prevents deletion of the reserved speciality', async () => {
    const wrapper = mountActions({ id: 1, intitule: 'Sans spécialité' })
    const removeButton = wrapper.get('button')

    expect(removeButton.attributes()).toHaveProperty('disabled')
    expect(removeButton.attributes('aria-label')).toBe(
      'Suppression impossible pour Sans spécialité',
    )
    expect(removeButton.attributes('title')).toBe(
      'Cette spécialité système ne peut pas être supprimée.',
    )

    await removeButton.trigger('click')
    expect(wrapper.emitted('remove')).toBeUndefined()
  })

  it('keeps deletion available for another speciality', async () => {
    const wrapper = mountActions({ id: 2, intitule: 'Cardiologie' })
    const removeButton = wrapper.get('button')

    expect(removeButton.attributes()).not.toHaveProperty('disabled')
    await removeButton.trigger('click')
    expect(wrapper.emitted('remove')).toHaveLength(1)
  })
})
