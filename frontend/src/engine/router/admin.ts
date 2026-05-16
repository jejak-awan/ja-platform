import { logger } from '@/shared/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import { SECURITY_ROUTES } from '@/config/security';
import { handleBeforeEachGuard } from './guards';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';
import { useSystemError } from '@/shared/composables/useSystemError';
import { useAuthStore } from '@/modules/System/stores/auth';
import { registry } from '@/engine/registry';
import { useWorkspaceStore } from '@/engine/stores/workspace';

const adminPath = SECURITY_ROUTES.dashboardBase;
const loginPath = SECURITY_ROUTES.login;
const registerPath = SECURITY_ROUTES.register;

// Base system routes (Auth, Errors, Maintenance)
const baseRoutes: Array<RouteRecordRaw> = [
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
        path: loginPath,
        name: 'login',
        component: () => import('@/modules/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/login',
        name: 'login-probe',
        redirect: { name: 'not-found' },
        meta: { public: true },
    },
    {
        path: registerPath,
        name: 'register',
        component: () => import('@/modules/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/register',
        name: 'register-probe',
        redirect: { name: 'not-found' },
        meta: { public: true },
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
        path: '/403',
        name: 'forbidden',
        component: () => import('@/modules/System/views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/404',
        name: 'not-found',
        component: () => import('@/modules/System/views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/modules/System/views/errors/ServerError.vue'),
        meta: { public: true },
    },
];

// Dashboard wrapper with dynamic children
const dashboardRoute: RouteRecordRaw = {
    path: adminPath,
    component: () => import('@/modules/System/layouts/AdminLayout.vue'),
    meta: { auth: true },
    children: [
        // 1. Explicit Core Dashboard (Priority Resolution)
        {
            path: 'dashboard',
            name: 'core.dashboard',
            component: () => import('@/modules/System/views/admin/Dashboard.vue'),
            meta: { permission: 'view dashboard' },
        },
        // 2. Central Redirect Handler (Dynamic Resolution)
        {
            path: '',
            name: 'dashboard',
            redirect: () => {
                const authStore = useAuthStore();
                const workspaceStore = useWorkspaceStore();
                
                const roleRank = authStore.getRoleRank();
                const isSuper = roleRank >= 100;

                logger.info('[Router:Admin] Resolving Landing Page', {
                    isSuper,
                    context: workspaceStore.activeContextType,
                    id: workspaceStore.activeId
                });

                // A. Super Admin in System Mode -> Core Dashboard
                if (isSuper && workspaceStore.isSystem) {
                    return { name: 'core.dashboard' };
                }

                // B. Scoped Landing: Find the best dashboard from registered modules
                const dashboards = registry.getDashboards();
                const availableDashboard = dashboards.find(d => d.condition ? d.condition(authStore.user, authStore) : true);

                if (availableDashboard) {
                    return { name: availableDashboard.routeName || 'core.dashboard' };
                }

                // C. Final Fallback: If no dashboard access, go to landing page
                return '/';
            },
        },
        // 3. Register all module routes from Registry
        ...registry.getAllRoutes().filter(r => r.name !== 'core.dashboard'),
    ],
};

const routes: Array<RouteRecordRaw> = [
    ...baseRoutes,
    dashboardRoute,
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
        if (to.hash) return { el: to.hash, top: 80, behavior: 'smooth' };
        return { top: 0, left: 0, behavior: 'auto' };
    },
});

router.beforeEach(async (to, _from, next) => {
    await handleBeforeEachGuard(to, next, {
        loginPath,
        registerPath,
        adminPath,
    });
});

let isHandlingRouterError = false;
router.onError((error) => {
    if (isHandlingRouterError) return;
    if (isChunkLoadError(error) && attemptChunkRecoveryReload()) return;
    isHandlingRouterError = true;
    logger.error('Admin router error:', error);
    const { showError } = useSystemError();
    showError({
        code: 500,
        title: 'Dashboard Error',
        message: error.message || 'A critical error occurred while navigating the dashboard.',
        description: 'The admin dashboard encountered an unexpected error. Please refresh and try again.',
        reason: 'Router Navigation Error',
        redirect: adminPath,
    });
    setTimeout(() => { isHandlingRouterError = false; }, 1000);
});

export default router;
