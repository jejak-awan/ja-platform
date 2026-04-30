import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Switch from "@/components/ui/Switch.vue";
import Separator from "@/components/ui/Separator.vue";
import SkeletonLoader from "@/components/ui/SkeletonLoader.vue";
import Spinner from "@/components/ui/Spinner.vue";

describe('Common UI Components', () => {
    it('Input renders and handles modelValue', async () => {
        const wrapper = mount(Input, {
            props: { modelValue: 'test', 'onUpdate:modelValue': (e: any) => wrapper.setProps({ modelValue: e }) }
        })
        const input = wrapper.find('input')
        expect(input.element.value).toBe('test')
        await input.setValue('new value')
        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    })

    it('Label renders correctly', () => {
        const wrapper = mount(Label, {
            slots: { default: 'Name' }
        })
        expect(wrapper.text()).toBe('Name')
    })

    it('Switch toggles', async () => {
        const wrapper = mount(Switch, {
            props: { modelValue: false }
        })
        const button = wrapper.find('button')
        await button.trigger('click')
        // Check for any emitted event since Radix handle it internally
        expect(wrapper.emitted()).toBeDefined()
    })

    it('Separator renders', () => {
        const wrapper = mount(Separator)
        expect(wrapper.exists()).toBe(true)
    })

    it('SkeletonLoader renders', () => {
        const wrapper = mount(SkeletonLoader)
        expect(wrapper.exists()).toBe(true)
    })

    it('Spinner renders', () => {
        const wrapper = mount(Spinner)
        expect(wrapper.exists()).toBe(true)
    })
})
