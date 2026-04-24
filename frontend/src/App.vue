<template>
  <div
    id="app-container"
    class="min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
  >
    <div class="noise-overlay" />
    <template v-if="isReady">
      <router-view />
      
      <!-- Global UI Components -->
      <Toast />
      
      <ConfirmModal 
        :is-open="confirmState.isOpen"
        :title="confirmState.title"
        :message="confirmState.message"
        :description="confirmState.description"
        :variant="confirmState.variant"
        :confirm-text="confirmState.confirmText"
        :cancel-text="confirmState.cancelText"
        :input="confirmState.input"
        :input-placeholder="confirmState.inputPlaceholder"
        @update:is-open="confirmState.isOpen = $event"
        @confirm="confirmState.onConfirm"
        @cancel="confirmState.onCancel"
      />
      
      <GlobalErrorModal />
      
      <SessionTimeoutModal 
        :is-visible="isWarningVisible" 
        :time-remaining="timeRemaining"
        @extend="extendSession"
        @logout="manualLogout"
      />
    </template>
    <div v-else class="fixed inset-0 flex items-center justify-center bg-background">
      <div class="h-10 w-10 border-4 border-primary border-t-transparent rounded-full animate-spin" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useConfirm } from '@/composables/useConfirm';
import { useSessionTimeout } from '@/composables/useSessionTimeout';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useTheme } from '@/composables/useTheme';
import { syncDocumentDarkClassForRoute } from '@/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import Toast from '@/components/ui/Toast.vue';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import GlobalErrorModal from '@/components/ui/GlobalErrorModal.vue';
import SessionTimeoutModal from '@/components/ui/SessionTimeoutModal.vue';

const { confirmState } = useConfirm();
const { isWarningVisible, timeRemaining, extendSession, manualLogout } = useSessionTimeout();

const cmsStore = useCmsStore();
const { themeSettings, loadActiveTheme } = useTheme();
const route = useRoute();

const isReady = ref(false);

watch(
    () => route.path,
    (path) => {
        syncDocumentDarkClassForRoute(path);
    },
    { immediate: true },
);

// Fetch global public settings and theme on startup
onMounted(async () => {
    try {
        // Load settings and theme in parallel
        await Promise.all([
            cmsStore.fetchPublicSettings(),
            loadActiveTheme()
        ]);
    } finally {
        isReady.value = true;
    }
});

// Global Reactive Favicon
const faviconHref = computed(() => {
    try {
        // 1. Priority: Theme-specific favicon
        const themeIcon = themeSettings.value?.brand_favicon;
        if (themeIcon && typeof themeIcon === 'string' && themeIcon.trim() !== '') {
            return themeIcon;
        }
        
        // 2. Fallback: Site-wide settings
        const siteIcon = (cmsStore.siteSettings as any)?.site_favicon;
        if (siteIcon && typeof siteIcon === 'string' && siteIcon.trim() !== '') {
            return siteIcon;
        }
    } catch (e) {
        // Silent recovery to prevent loop
    }
    
    // 3. Absolute Fallback
    return '/favicon.svg';
});

// Title computed for safety
const siteTitle = computed(() => {
    try {
        const title = (cmsStore.siteSettings as any)?.site_name || (cmsStore.siteSettings as any)?.site_title;
        return title && typeof title === 'string' ? title : 'Portal';
    } catch (e) {
        return 'Portal';
    }
});

useHead({
    title: siteTitle,
    link: [
        {
            rel: 'icon',
            href: faviconHref
        }
    ]
});
</script>

<style>
/* Global styles if needed */
</style>
