<template>
  <div
    id="app-container"
    class="min-h-screen bg-background text-foreground font-sans antialiased text-sharp"
  >
    <div class="noise-overlay" />
    <template v-if="isReady">
      <router-view />
      <Toast v-if="deferUiOverlays" />
      <ConfirmModal
        v-if="deferUiOverlays && confirmState.isOpen"
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
      <GlobalErrorModal v-if="deferUiOverlays" />
      <SessionTimeoutModal
        v-if="deferUiOverlays && isWarningVisible"
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
import { onMounted, computed, ref, watch, defineAsyncComponent } from 'vue';
import { useRoute } from 'vue-router';
import { useConfirm } from '@/composables/useConfirm';
import { useSessionTimeout } from '@/composables/useSessionTimeout';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { useTheme } from '@/composables/useTheme';
import { syncDocumentDarkClassForRoute } from '@/composables/useDarkMode';
import { useHead } from '@unhead/vue';
import { applyFavicon, resolveFavicon } from '@/utils/favicon';

const Toast = defineAsyncComponent(() => import('@/components/ui/Toast.vue'));
const ConfirmModal = defineAsyncComponent(() => import('@/components/ui/ConfirmModal.vue'));
const GlobalErrorModal = defineAsyncComponent(() => import('@/components/ui/GlobalErrorModal.vue'));
const SessionTimeoutModal = defineAsyncComponent(() => import('@/components/ui/SessionTimeoutModal.vue'));

const { confirmState } = useConfirm();
const { isWarningVisible, timeRemaining, extendSession, manualLogout } = useSessionTimeout();

const cmsStore = useCmsStore();
const { themeSettings, loadActiveTheme } = useTheme();
const route = useRoute();
const isReady = ref(false);
const deferUiOverlays = ref(false);

watch(
    () => route.path,
    (path) => {
        syncDocumentDarkClassForRoute(path);
    },
    { immediate: true },
);

onMounted(() => {
    // Unblock first render; hydrate settings/theme in background.
    isReady.value = true;
    void Promise.all([
        cmsStore.fetchPublicSettings({ force: true }),
        loadActiveTheme(),
    ]);

    const mountDeferredUi = () => {
        deferUiOverlays.value = true;
    };
    if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(mountDeferredUi, { timeout: 1500 });
    } else {
        setTimeout(mountDeferredUi, 600);
    }
});

const faviconHref = computed(() => resolveFavicon([
    themeSettings.value?.brand_favicon,
    (cmsStore.siteSettings as any)?.site_favicon,
]));

const siteTitle = computed(() => {
    try {
        const title = (cmsStore.siteSettings as any)?.site_name || (cmsStore.siteSettings as any)?.site_title;
        return title && typeof title === 'string' ? title : 'Portal';
    } catch {
        return 'Portal';
    }
});

const siteDescription = computed(() => {
    try {
        const description = (cmsStore.siteSettings as any)?.site_description;
        if (description && typeof description === 'string' && description.trim().length > 0) {
            return description.trim();
        }
    } catch {
        // fallback below
    }
    return 'Portal resmi sekolah untuk informasi akademik, berita, dan layanan pendidikan.';
});

useHead({
    title: siteTitle,
    meta: [
        {
            name: 'description',
            content: siteDescription,
        },
    ],
});

watch(
    faviconHref,
    (href) => {
        applyFavicon(href);
    },
    { immediate: true },
);
</script>
