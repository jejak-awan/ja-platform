<template>
  <button
    class="relative flex items-center justify-center w-9 h-9 rounded-full text-muted-foreground hover:text-foreground hover:bg-primary/5 hover:ring-4 hover:ring-primary/10 hover:scale-110 active:scale-95 transition-all duration-300 focus:outline-none"
    @click="toggleMode"
  >
    <Sun 
      class="h-[1.2rem] w-[1.2rem] transition-all duration-300" 
      :class="{'rotate-0 scale-100': !isDark, '-rotate-90 scale-0': isDark}"
      stroke-width="1.5" 
    />
    <Moon 
      class="absolute h-[1.2rem] w-[1.2rem] transition-all duration-300" 
      :class="{'rotate-90 scale-0': !isDark, 'rotate-0 scale-100': isDark}"
      stroke-width="1.5" 
    />
    <span class="sr-only">Toggle theme</span>
  </button>
</template>

<script setup lang="ts">
import Sun from 'lucide-vue-next/dist/esm/icons/sun.js';
import Moon from 'lucide-vue-next/dist/esm/icons/moon.js';
import { useDarkMode } from '@/shared/composables/useDarkMode';

const { toggleMode, isDark } = useDarkMode();
</script>
