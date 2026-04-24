<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  orientation?: 'vertical' | 'horizontal';
  class?: HTMLAttributes['class'];
}>(), {
  orientation: 'vertical',
  class: undefined,
});
</script>

<template>
  <div
    data-slot="field"
    :class="cn(
      'group/field grid gap-2 data-[orientation=horizontal]:grid-cols-[auto_1fr] data-[orientation=horizontal]:items-center data-[orientation=horizontal]:gap-3 bg-transparent',
      props.class
    )"
    :data-orientation="orientation"
  >
    <slot />
  </div>
</template>
