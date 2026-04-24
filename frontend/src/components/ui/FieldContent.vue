<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <div
    data-slot="field-content"
    :class="cn('grid gap-1', props.class)"
  >
    <slot />
  </div>
</template>
