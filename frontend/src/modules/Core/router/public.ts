import { logger } from '@/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import frontendRoutes from './frontend';
import { SECURITY_ROUTES } from '@/config/security';
import { handleBeforeEachGuard } from './guards';
import { trackRouteVisit } from './analytics-tracker';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/utils/chunkRecovery';
import { useSystemError } from '@/composables/useSystemError';

const routes: Array<RouteRecordRaw> = [
    ...frontendRoutes,
    {
        path: '/maintenance',
        name: 'maintenance',
        component: () => import('@/views/Maintenance.vue'),
        meta: { public: true, title: 'Under Maintenance' },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/modules/Core/views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/modules/Core/views/errors/ServerError.vue'),
        meta: { public: true },
    },
    {
        path: '/404',
        name: 'not-found',
        component: () => import('@/modules/Core/views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/419',
        name: 'session-expired',
        component: () => import('@/modules/Core/views/errors/SessionExpired.vue'),
        meta: { public: true },
    },
    {
        path: '/429',
        name: 'too-many-requests',
        component: () => import('@/modules/Core/views/errors/RateLimit.vue'),
        meta: { public: true },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'catch-all',
        redirect: { name: 'not-found' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, _from, savedPosition) {
        if (savedPosition) return savedPosition;
        if (to.hash) {
            return { el: to.hash, top: 80, behavior: 'smooth' };
        }
        return { top: 0, left: 0, behavior: 'auto' };
    },
});

// Keep existing behavior for maintenance checks and probe path hardening.
router.beforeEach(async (to, _from, next) => {
    await handleBeforeEachGuard(to, next, {
        loginPath: SECURITY_ROUTES.login,
        registerPath: SECURITY_ROUTES.register,
    });
});

let isHandlingRouterError = false;
router.onError((error) => {
    if (isHandlingRouterError) return;
    if (isChunkLoadError(error) && attemptChunkRecoveryReload()) return;
    isHandlingRouterError = true;
    logger.error('Public router error:', error);
    const { showError } = useSystemError();
    showError({
        code: 500,
        title: 'Application Error',
        message: error.message || 'A critical error occurred while navigating.',
        description: 'The application encountered an unexpected error. Please refresh and try again.',
        reason: 'Router Navigation Error',
        redirect: '/',
    });
    setTimeout(() => { isHandlingRouterError = false; }, 1000);
});

router.afterEach((to, from) => {
    trackRouteVisit(to, from);
});

export default router;
