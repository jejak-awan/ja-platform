import './bootstrap';
import '../css/app.css';
import '../css/themes/janari.css';
import '../css/editor.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createHead } from '@unhead/vue/client';
import router from '@/modules/Core/router'; // Assuming router is index.js/ts
import App from './App.vue';
import lazyLoad from '@/utils/directives/lazyLoad';
import i18n from './i18n';
import { useAuthStore } from '@/modules/Core/stores/auth';

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
    const isAdminRoute = window.location.pathname.startsWith('/dash');
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
const app = createApp(App);

// 0. SET ERROR HANDLER IMMEDIATELY (Before any plugins are installed)
app.config.errorHandler = (err, _vm, info) => {
    const error = err as { message?: string } | null;
    const message = error?.message || String(err);

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

// 1. Core Plugins Registration
// We install all plugins FIRST to ensure a complete context is available 
// before any reactive state updates or navigation guards are triggered.
app.use(pinia);
app.use(router);
app.use(head);
app.use(i18n);

// 2. Directives
app.directive('lazy', lazyLoad);

// 3. Logger Plugin (Depends on App Context)
import Logger, { logger } from '@/utils/logger';
app.use(Logger);

// 4. State Initialization
// Now that all plugins are installed and the context is stable,
// it is safe to initialize state and trigger lifecycle actions.
const authStore = useAuthStore();
authStore.initAuth();

logger.info('Mounting App...');

app.mount('#app');
logger.info('App Mounted successfully!');
