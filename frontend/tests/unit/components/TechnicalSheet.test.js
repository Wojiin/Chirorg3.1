import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import TechnicalSheet from '@/components/TechnicalSheet.vue'

describe('TechnicalSheet', () => {
  it('renders image-only and text-only instructions', () => {
    const wrapper = mount(TechnicalSheet, {
      props: {
        sheets: [
          {
            id: 1,
            ordre: 1,
            titre: 'Illustration',
            contenu: '',
            image: '/uploads/fiches-techniques/test.png',
          },
          {
            id: 2,
            ordre: 2,
            titre: 'Texte',
            contenu: 'Préparer le patient.',
            image: null,
          },
        ],
      },
    })

    const image = wrapper.get('img')
    expect(image.attributes('src')).toBe(
      'http://localhost:8080/uploads/fiches-techniques/test.png',
    )
    expect(wrapper.text()).toContain('Préparer le patient.')
    expect(wrapper.text()).not.toContain('Illustration technique')
  })

  it('opens a sheet in a large modal and closes it with Escape', async () => {
    const wrapper = mount(TechnicalSheet, {
      props: {
        sheets: [
          {
            id: 7,
            ordre: 3,
            titre: 'Positionnement du patient',
            contenu: 'Installer le patient en décubitus.',
            image: '/uploads/fiches-techniques/position.png',
          },
        ],
      },
      global: {
        stubs: { Teleport: true },
      },
    })

    await wrapper
      .get('[data-testid="technical-sheet-trigger"]')
      .trigger('click')

    const dialog = wrapper.get('[role="dialog"]')
    expect(dialog.text()).toContain('Positionnement du patient')
    expect(dialog.text()).toContain('Installer le patient en décubitus.')
    expect(document.body.style.overflow).toBe('hidden')

    dialog.element.parentElement.dispatchEvent(
      new KeyboardEvent('keydown', {
        key: 'Escape',
        bubbles: true,
      }),
    )
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
    expect(document.body.style.overflow).toBe('')
    wrapper.unmount()
  })
})
