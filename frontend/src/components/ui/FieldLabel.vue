<template>
  <Label
    v-bind="delegatedProps"
    data-slot="field-label"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-50 group-data-[disabled=true]/field:opacity-50 group-data-[disabled=true]/field:cursor-not-allowed bg-transparent',
      props.class
    )"
  >
    <slot />
  </Label>
</template>

<script setup lang="ts">
import { Label, type LabelProps } from 'radix-vue';
import { cn } from '@/lib/utils';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<LabelProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;
  return delegated;
});
</script>
