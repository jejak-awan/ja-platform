import '../css/base.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createHead } from '@unhead/vue/client';
import lazyLoad from '@/shared/utils/directives/lazyLoad';
import i18n from '@/engine/i18n';
import { resolveIsAdminEntrypoint } from '@/engine/router/entrypoint';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';

// Keep admin/dashboard and public theme styles isolated.
const isAdminRoute = resolveIsAdminEntrypoint(window.location.pathname);
if (isAdminRoute) {
    void import('../css/admin.css');
    void import('../css/editor.css');
} else {
    // Current active public theme stylesheet (Janari for now).
    void import('../css/themes/janari.css');
}

// Initialization Fail-Safe Logger
window.onerror = function(message, source, lineno, colno, error) {
    // Check for chunk load errors during initialization
    if (isChunkLoadError(error || message) && attemptChunkRecoveryReload()) {
        return true; // prevent default handling
    }

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

import { bootstrapApp } from '@/engine/bootstrap';

async function bootstrap() {
    const [{ default: Logger, logger }, { default: RootComponent }] = await Promise.all([
        import('@/shared/utils/logger'),
        isAdminRoute ? import('./ConsoleApp.vue') : import('./PublicApp.vue'),
    ]);

    const app = createApp(RootComponent);

    // 0. Set Global Error Handler
    app.config.errorHandler = (err) => {
        if (isChunkLoadError(err) && attemptChunkRecoveryReload()) return;
        if (import.meta.env.DEV) console.error('[VUE_ERROR]', err);
    };

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);
    const head = createHead();

    app.use(pinia);
    app.use(head);
    app.use(i18n);
    app.directive('lazy', lazyLoad);
    app.use(Logger);

    // 1. RUN DETERMINISTIC BOOTSTRAP (Kernel, Modules, Auth)
    const { registry } = await bootstrapApp();

    // 2. Import Router (Registry is now full)
    const { default: router } = await (isAdminRoute ? import('@/engine/router/console') : import('@/engine/router/public'));
    app.use(router);

    // 3. Sync Navigation & Dashboards from Registry to Global Stores
    const { useNavigationStore } = await import('@/shared/stores/navigation');
    const { useDashboardStore } = await import('@/shared/stores/dashboard');
    
    const navStore = useNavigationStore();
    const dbStore = useDashboardStore();

    // Sync Navigation
    Object.entries(registry.getNavigation()).forEach(([id, navs]) => {
        navStore.registerModuleNavigation(id, navs);
    });

    // Sync Dashboards
    registry.getDashboards().forEach(db => {
        dbStore.registerDashboard(db);
    });

    logger.info(`[App] Mounting ${isAdminRoute ? 'Admin' : 'Public'} Workspace...`);
    app.mount('#app');
    logger.info('App Mounted successfully!');
}

void bootstrap();
