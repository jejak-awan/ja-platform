<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { PopoverContent, type PopoverContentEmits, type PopoverContentProps, PopoverPortal, useForwardPropsEmits } from 'radix-vue'
import { cn } from '@/lib/utils'

const props = withDefaults(defineProps<PopoverContentProps & { class?: HTMLAttributes['class'] }>(), {
  sideOffset: 4,
  align: 'center',
  class: undefined,
})
const emits = defineEmits<PopoverContentEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <PopoverPortal>
    <PopoverContent
      v-bind="forwarded"
      :class="
        cn(
          '!z-[100001] w-72 rounded-xl border border-border/60 bg-popover p-4 text-popover-foreground outline-none shadow-xl',
          props.class,
        )
      "
    >
      <slot />
    </PopoverContent>
  </PopoverPortal>
</template>
