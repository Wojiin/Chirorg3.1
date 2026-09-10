import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue'

describe('ConfirmationModal', () => {
  it('applies the action style and emits confirmation', async () => {
    const wrapper = mount(ConfirmationModal, {
      props: {
        open: true,
        variant: 'danger',
        title: 'Confirmer la suppression',
        message: 'Cette action est irréversible.',
        confirmLabel: 'Supprimer',
      },
      global: {
        stubs: { DialogPortal: { template: '<div><slot /></div>' } },
      },
    })

    expect(wrapper.get('[role="dialog"]').text()).toContain(
      'Confirmer la suppression',
    )
    expect(wrapper.get('.confirmation-content').classes()).toContain(
      'confirmation-danger',
    )

    const confirmButton = wrapper
      .findAll('button')
      .find((button) => button.text() === 'Supprimer')
    await confirmButton.trigger('click')
    expect(wrapper.emitted('confirm')).toHaveLength(1)
    wrapper.unmount()
  })

  it('uses a warning treatment for sensitive account actions', () => {
    const wrapper = mount(ConfirmationModal, {
      props: {
        open: true,
        variant: 'warning',
        title: 'Changer le mot de passe',
        message: 'Confirmer cette action sensible.',
      },
      global: {
        stubs: { DialogPortal: { template: '<div><slot /></div>' } },
      },
    })

    expect(wrapper.get('.confirmation-content').classes()).toContain(
      'confirmation-warning',
    )
    expect(wrapper.get('.button-warning').exists()).toBe(true)
    wrapper.unmount()
  })
})
