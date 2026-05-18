import '../../../css/admin.css';
import '../../../css/editor.css';
import '../../../css/base.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createHead } from '@unhead/vue/client';
import lazyLoad from '@/shared/utils/directives/lazyLoad';
import i18n from '@/engine/i18n';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';

// Initialization Fail-Safe Logger
window.onerror = function(message, source, lineno, colno, error) {
    if (isChunkLoadError(error || message) && attemptChunkRecoveryReload()) return true;
    const errorData = { message, source, lineno, colno, stack: error?.stack, timestamp: new Date().toISOString() };
    fetch('/api/v1/journal/frontend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ level: 'error', message: 'Admin Crash: ' + message, data: errorData })
    }).catch(() => {});
    return false;
};

// Initialize dark mode
const initLayout = () => {
    document.documentElement.classList.add('no-transitions');
    const saved = localStorage.getItem('admin-dark-mode');
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    if (saved === 'dark' || (saved === 'system' && mq.matches) || (!saved && mq.matches)) {
        document.documentElement.classList.add('dark');
    }
    requestAnimationFrame(() => requestAnimationFrame(() => document.documentElement.classList.remove('no-transitions')));
};
initLayout();

import { bootstrapApp } from '@/engine/bootstrap';

import { resolveIsAdminEntrypoint } from '@/engine/router/entrypoint';

async function bootstrap() {
    const pathname = window.location.pathname;

    if (!resolveIsAdminEntrypoint(pathname) && window.__JA_GATE__ !== 'public') {
        // Redirect to public gate
        window.location.href = `/index.html${window.location.search || ''}`;
        return;
    }

    const [{ default: Logger, logger }, { default: ConsoleApp }] = await Promise.all([
        import('@/shared/utils/logger'),
        import('@/ConsoleApp.vue'),
    ]);

    const app = createApp(ConsoleApp);
    app.config.errorHandler = (err) => {
        if (isChunkLoadError(err) && attemptChunkRecoveryReload()) return;
        if (import.meta.env.DEV) console.error('[ADMIN_ERROR]', err);
    };

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);
    const head = createHead();

    app.use(pinia);
    app.use(head);
    app.use(i18n);
    app.directive('lazy', lazyLoad);
    app.use(Logger);

    // 1. RUN DETERMINISTIC BOOTSTRAP
    const { registry } = await bootstrapApp();

    // 2. Import Admin Router
    const { default: router } = await import('@/engine/router/console');
    app.use(router);

    // 3. Sync Navigation & Dashboards
    const { useNavigationStore } = await import('@/shared/stores/navigation');
    const { useDashboardStore } = await import('@/shared/stores/dashboard');
    
    const navStore = useNavigationStore();
    const dbStore = useDashboardStore();

    Object.entries(registry.getNavigation()).forEach(([id, navs]) => navStore.registerModuleNavigation(id, navs));
    registry.getDashboards().forEach(db => dbStore.registerDashboard(db));

    logger.info('[Admin] Mounting Workspace...');
    app.mount('#app');
}

void bootstrap();
