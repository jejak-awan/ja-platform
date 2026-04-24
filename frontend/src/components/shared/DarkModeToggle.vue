<template>
  <Button
    variant="ghost"
    size="icon"
    class="h-9 w-9 px-0 border-0 ring-0 focus-visible:ring-0 focus-visible:ring-offset-0 transition-none"
    @click="toggleMode"
  >
    <Sun 
      class="h-[1.2rem] w-[1.2rem]" 
      :class="{'rotate-0 scale-100': !isDark, '-rotate-90 scale-0': isDark}"
      stroke-width="1.5" 
    />
    <Moon 
      class="absolute h-[1.2rem] w-[1.2rem]" 
      :class="{'rotate-90 scale-0': !isDark, 'rotate-0 scale-100': isDark}"
      stroke-width="1.5" 
    />
    <span class="sr-only">Toggle theme</span>
  </Button>
</template>

<script setup lang="ts">
import Sun from 'lucide-vue-next/dist/esm/icons/sun.js';
import Moon from 'lucide-vue-next/dist/esm/icons/moon.js';
import { useDarkMode } from '@/composables/useDarkMode';
import { Button } from '@/components/ui';

const { toggleMode, isDark } = useDarkMode();
</script>
