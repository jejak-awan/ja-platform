<script setup lang="ts">
import { DropdownMenuGroup as RekaDropdownMenuGroup, type DropdownMenuGroupProps } from "reka-ui"

const props = defineProps<DropdownMenuGroupProps>()
</script>

<template>
  <RekaDropdownMenuGroup v-bind="props">
    <slot />
  </RekaDropdownMenuGroup>
</template>
