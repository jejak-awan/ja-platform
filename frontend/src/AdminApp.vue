<template>
  <div
    id="app-container"
    class="min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
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
import { useConfirm } from '@/shared/composables/useConfirm';
import { useSessionTimeout } from '@/shared/composables/useSessionTimeout';
import { syncDocumentDarkClassForRoute } from '@/shared/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useCoreStore } from '@/modules/Core/stores/core';
import { applyFavicon, resolveFavicon } from '@/shared/utils/favicon';

const Toast = defineAsyncComponent(() => import('@/shared/components/ui/Toast.vue'));
const ConfirmModal = defineAsyncComponent(() => import('@/shared/components/ui/ConfirmModal.vue'));
const GlobalErrorModal = defineAsyncComponent(() => import('@/shared/components/ui/GlobalErrorModal.vue'));
const SessionTimeoutModal = defineAsyncComponent(() => import('@/shared/components/ui/SessionTimeoutModal.vue'));

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
    await Promise.all([
        cmsStore.fetchPublicSettings(),
        coreStore.fetchPublicSettings(),
    ]);
});

onUnmounted(() => {
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
