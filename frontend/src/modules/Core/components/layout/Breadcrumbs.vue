<template>
  <nav 
    v-if="breadcrumbs.length > 0" 
    :class="[ navClasses, isSticky ? 'sticky z-40' : 'relative' ]"
    aria-label="Breadcrumb"
  >
    <div class="container mx-auto px-6 flex items-center">
      <!-- Breadcrumb Path Only (Clean & Flat) -->
      <ol class="dashboard-breadcrumb-path">
        <li
          v-for="(crumb, index) in breadcrumbs"
          :key="index"
          class="flex items-center"
        >
          <!-- Separator -->
          <span
            v-if="index > 0"
            class="dashboard-breadcrumb-separator"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
          </span>
            
          <router-link
            v-if="index < breadcrumbs.length - 1"
            :to="crumb.path"
            class="dashboard-breadcrumb-link"
          >
            {{ crumb.label }}
          </router-link>
          <span
            v-else
            class="dashboard-breadcrumb-current"
            aria-current="page"
          >
            {{ crumb.label }}
          </span>
        </li>
      </ol>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTheme } from '@/composables/useTheme';

const route = useRoute();
const { getBreadcrumbs } = useBreadcrumbs();
const { getSetting } = useTheme();

const breadcrumbs = computed(() => getBreadcrumbs(route));

// Theme settings
const isSticky = computed(() => getSetting('breadcrumb_sticky', true));
const isHeaderSticky = computed(() => getSetting('header_sticky', true));

const navClasses = computed(() => {
  const classes = [
    'dashboard-breadcrumb'
  ];

  if (isSticky.value) {
    // Adjust top position based on header
    if (isHeaderSticky.value) {
      classes.push('top-[84px]'); // Header py-6 + branding height ~ 84px
    } else {
      classes.push('top-0');
    }
  }

  return classes.join(' ');
});

</script>

<style scoped>
nav {
    will-change: padding, box-shadow;
}
h1, .h-\[1px\] {
    will-change: transform, width, opacity;
}
</style>
