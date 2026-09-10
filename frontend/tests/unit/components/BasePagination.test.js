import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import BasePagination from '@/components/ui/BasePagination.vue'

describe('BasePagination', () => {
  it('stays hidden when all results fit on one page', () => {
    const wrapper = mount(BasePagination, {
      props: { page: 1, total: 8, itemsPerPage: 10 },
    })

    expect(wrapper.find('nav').exists()).toBe(false)
  })

  it('emits the next page through its accessible controls', async () => {
    const wrapper = mount(BasePagination, {
      props: {
        page: 1,
        total: 25,
        itemsPerPage: 10,
        'onUpdate:page': (value) => wrapper.setProps({ page: value }),
      },
    })

    await wrapper.get('button[aria-label="Next Page"]').trigger('click')

    expect(wrapper.emitted('update:page')?.[0]).toEqual([2])
  })
})
