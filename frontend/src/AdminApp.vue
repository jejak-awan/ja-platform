<template>
  <div
    id="app-container"
    class="admin-no-motion min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
  >
    <div class="noise-overlay" />
    <router-view />
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
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, onUnmounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useConfirm } from '@/composables/useConfirm';
import { useSessionTimeout } from '@/composables/useSessionTimeout';
import { syncDocumentDarkClassForRoute } from '@/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useCoreStore } from '@/modules/Core/stores/core';
import { applyFavicon, resolveFavicon } from '@/utils/favicon';

const Toast = defineAsyncComponent(() => import('@/components/ui/Toast.vue'));
const ConfirmModal = defineAsyncComponent(() => import('@/components/ui/ConfirmModal.vue'));
const GlobalErrorModal = defineAsyncComponent(() => import('@/components/ui/GlobalErrorModal.vue'));
const SessionTimeoutModal = defineAsyncComponent(() => import('@/components/ui/SessionTimeoutModal.vue'));

const { confirmState } = useConfirm();
const { isWarningVisible, timeRemaining, extendSession, manualLogout } = useSessionTimeout();
const route = useRoute();
const cmsStore = useCmsStore();
const coreStore = useCoreStore();

watch(
    () => route.path,
    (path) => {
        syncDocumentDarkClassForRoute(path);
    },
    { immediate: true },
);

useHead({
    title: 'JA-Platform Admin',
});

onMounted(async () => {
    document.body.classList.add('admin-no-motion-global');
    await Promise.all([
        cmsStore.fetchPublicSettings(),
        coreStore.fetchPublicSettings(),
    ]);
});

onUnmounted(() => {
    document.body.classList.remove('admin-no-motion-global');
});

const faviconHref = computed(() => resolveFavicon([
    (cmsStore.siteSettings as any)?.site_favicon,
]));

watch(
    faviconHref,
    (href) => {
        applyFavicon(href);
    },
    { immediate: true },
);
</script>

<style>
/*
  Admin UX preference: keep dashboard responsive and calm by disabling
  non-essential CSS animations/transitions globally inside admin shell.
*/
#app-container.admin-no-motion *,
#app-container.admin-no-motion *::before,
#app-container.admin-no-motion *::after {
  animation: none !important;
  transition: none !important;
  scroll-behavior: auto !important;
}
</style>
<style>
/*
  Also disable motion for teleported overlays (Dialog/Popover/Dropdown)
  that are mounted outside #app-container, usually under <body>.
*/
body.admin-no-motion-global *,
body.admin-no-motion-global *::before,
body.admin-no-motion-global *::after {
  animation: none !important;
  transition: none !important;
  scroll-behavior: auto !important;
}
</style>
