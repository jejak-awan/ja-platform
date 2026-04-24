<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <span
    data-slot="field-title"
    :class="cn('text-sm font-medium leading-none', props.class)"
  >
    <slot />
  </span>
</template>
