<template>
  <nav 
    v-if="breadcrumbs.length > 0" 
    :class="[
      navClasses,
      isScrolled ? 'py-2 shadow-lg' : 'py-4 shadow-md',
      isSticky ? 'sticky z-40 transition-all duration-500 ease-in-out' : 'relative'
    ]"
    aria-label="Breadcrumb"
  >
    <div class="container mx-auto px-8 flex items-center justify-between">
      <!-- Page Title (L'Arc Style) -->
      <div 
        class="flex flex-col transition-all duration-500"
        :class="isScrolled ? 'opacity-80 scale-95 origin-left' : 'opacity-100 scale-100'"
      >
        <h1 class="dashboard-breadcrumb-title">
          {{ currentPageLabel }}
        </h1>
        <div
          class="dashboard-breadcrumb-title-rule"
          :class="isScrolled ? 'w-8' : 'w-12'"
        />
      </div>

      <!-- Breadcrumb Path -->
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
          >/</span>
            
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

    <!-- Signature Accent Lines (Consistency with Header) -->
    <div class="dashboard-breadcrumb-accent" />
  </nav>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTheme } from '@/composables/useTheme';

const route = useRoute();
const { getBreadcrumbs } = useBreadcrumbs();
const { getSetting } = useTheme();

const breadcrumbs = computed(() => getBreadcrumbs(route));
const currentPageLabel = computed(() => {
    if (breadcrumbs.value.length === 0) return '';
    return breadcrumbs.value[breadcrumbs.value.length - 1]?.label || '';
});

// Scroll Tracking for Shrink Animation
const isScrolled = ref(false);
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

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

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
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
