import { logger } from '@/shared/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import frontendRoutes from './frontend';
import { SECURITY_ROUTES } from '@/config/security';
import { handleBeforeEachGuard } from './guards';
import { trackRouteVisit } from './analytics-tracker';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';
import { useSystemError } from '@/shared/composables/useSystemError';

const routes: Array<RouteRecordRaw> = [
    ...frontendRoutes,
    {
        path: SECURITY_ROUTES.login,
        name: 'login',
        component: () => import('@/modules/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: SECURITY_ROUTES.register,
        name: 'register',
        component: () => import('@/modules/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/modules/System/views/auth/ForgotPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('@/modules/System/views/auth/ResetPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/maintenance',
        name: 'maintenance',
        component: () => import('@/shared/views/Maintenance.vue'),
        meta: { public: true, title: 'Under Maintenance' },
    },
    {
        path: '/install',
        name: 'install',
        component: () => import('@/modules/System/views/InstallView.vue'),
        meta: { public: true, title: 'Installation Wizard' },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/modules/System/views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/modules/System/views/errors/ServerError.vue'),
        meta: { public: true },
    },
    {
        path: '/404',
        name: 'not-found',
        component: () => import('@/modules/System/views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/419',
        name: 'session-expired',
        component: () => import('@/modules/System/views/errors/SessionExpired.vue'),
        meta: { public: true },
    },
    {
        path: '/429',
        name: 'too-many-requests',
        component: () => import('@/modules/System/views/errors/RateLimit.vue'),
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
