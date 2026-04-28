import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createHead } from '@unhead/vue/client';
import lazyLoad from '@/utils/directives/lazyLoad';
import i18n from './i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { resolveIsAdminEntrypoint } from '@/modules/Core/router/entrypoint';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/utils/chunkRecovery';

// Keep admin/dashboard and public theme styles isolated.
const isAdminRoute = resolveIsAdminEntrypoint(window.location.pathname);
if (isAdminRoute) {
    void import('../css/editor.css');
} else {
    // Current active public theme stylesheet (Janari for now).
    void import('../css/themes/janari.css');
}

// Initialization Fail-Safe Logger
window.onerror = function(message, source, lineno, colno, error) {
    const safeUrl = (() => {
        try {
            const parsed = new URL(window.location.href);
            return parsed.origin + parsed.pathname;
        } catch {
            return window.location.pathname || '/';
        }
    })();

    const errorData = {
        message, source, lineno, colno,
        stack: error?.stack,
        url: safeUrl,
        timestamp: new Date().toISOString()
    };
    // Use native fetch to avoid dependency on the potentially broken axios/api service
    fetch('/api/v1/journal/frontend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            level: 'error',
            message: 'Startup Crash: ' + message,
            data: errorData
        })
    }).catch(() => {});
    return false;
};

// Initialize dark mode and layout properties safely
const initLayout = () => {
    // 1. Suppress transitions initially
    document.documentElement.classList.add('no-transitions');

    // 2. Init Dark Mode
    const THEME_KEY = isAdminRoute ? 'admin-dark-mode' : 'frontend-dark-mode';
    const saved = localStorage.getItem(THEME_KEY);
    
    // Use matchMedia safely
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const isDark = saved === 'dark' || 
                  (saved === 'system' && mq.matches) || 
                  (!saved && mq.matches);

    if (isDark) {
        document.documentElement.classList.add('dark');
    } else if (saved === 'light') {
        document.documentElement.classList.remove('dark');
    }

    // 3. Cleanup transitions after a frame
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('no-transitions');
        });
    });
};

// Run initialization
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLayout);
} else {
    initLayout();
}

// @ts-expect-error: JANARI_VERSION is a global set for audit purposes
window.JANARI_VERSION = '2026.04.22.fix.v1';

async function bootstrap() {
    const [{ default: Logger, logger }, { default: RootComponent }, { default: router }] = await Promise.all([
        import('@/utils/logger'),
        isAdminRoute ? import('./AdminApp.vue') : import('./PublicApp.vue'),
        isAdminRoute ? import('@/modules/Core/router/admin') : import('@/modules/Core/router/public'),
    ]);

    const app = createApp(RootComponent);

    // 0. SET ERROR HANDLER IMMEDIATELY (Before any plugins are installed)
    app.config.errorHandler = (err, _vm, info) => {
        const error = err as { message?: string } | null;
        const message = error?.message || String(err);

        if (isChunkLoadError(err) && attemptChunkRecoveryReload()) {
            return;
        }

        if (import.meta.env.DEV) {
            console.error('[VUE_ERROR_DETECTED]', message);
            console.error('[RAW_ERROR_OBJECT]', err);
            console.log('[ERROR_INFO]', info);

            if (message.includes('Failed to fetch dynamically imported module')) {
                console.warn('Chunk loading failed. Likely cache mismatch or missing chunk.');
            }
        }
    };

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);
    const head = createHead();

    app.use(pinia);
    app.use(router);
    app.use(head);
    app.use(i18n);
    app.directive('lazy', lazyLoad);
    app.use(Logger);

    const authStore = useAuthStore();
    if (isAdminRoute) {
        authStore.initAuth();
    } else if (typeof requestIdleCallback === 'function') {
        requestIdleCallback(() => authStore.initAuth(), { timeout: 2000 });
    } else {
        setTimeout(() => authStore.initAuth(), 0);
    }

    logger.info(`Mounting ${isAdminRoute ? 'Admin' : 'Public'} App...`);
    app.mount('#app');
    logger.info('App Mounted successfully!');

}

void bootstrap();
