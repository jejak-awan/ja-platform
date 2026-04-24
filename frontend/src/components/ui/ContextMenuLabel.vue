<template>
  <ContextMenuLabel
    v-bind="forwarded"
    :class="cn('px-2 py-1.5 text-xs font-semibold text-muted-foreground', inset && 'pl-8', props.class)"
  >
    <slot />
  </ContextMenuLabel>
</template>

<script setup lang="ts">
import { ContextMenuLabel, type ContextMenuLabelProps, useForwardProps } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps<ContextMenuLabelProps & { class?: string; inset?: boolean }>();
const forwarded = useForwardProps(props);
</script>
