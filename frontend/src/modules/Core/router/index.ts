import { logger } from '@/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/modules/Core/stores/auth';
import api from '@/services/api';
import cmsRoutes from '@/modules/Cms/router';
import schoolRoutes from '@/modules/School/router/index';
import frontendRoutes from './frontend';
import { LEGACY_PUBLIC_AUTH_PATHS, SECURITY_ROUTES, isProbePath } from '@/config/security';
// import { useSystemError } from '@/composables/useSystemError';

const adminPath = SECURITY_ROUTES.dashboardBase;
const loginPath = SECURITY_ROUTES.login;
const registerPath = SECURITY_ROUTES.register;

const routes: Array<RouteRecordRaw> = [
    // Frontend routes (public)
    ...frontendRoutes,

    {
        path: '/maintenance',
        name: 'maintenance',
        component: () => import('@/views/Maintenance.vue'),
        meta: { public: true, title: 'Under Maintenance' },
    },

    // Auth routes
    {
        path: loginPath,
        name: 'login',
        component: () => import('@/modules/Core/views/auth/Login.vue'),
        meta: { guestOnly: true },
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
        component: () => import('@/modules/Core/views/auth/Register.vue'),
        meta: { guestOnly: true },
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
        component: () => import('@/modules/Core/views/auth/ForgotPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('@/modules/Core/views/auth/ResetPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/verify-email',
        name: 'verify-email',
        component: () => import('@/modules/Core/views/auth/VerifyEmail.vue'),
        meta: { guestOnly: true },
    },
    {
        path: adminPath,
        component: () => import('@/layouts/core/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/modules/Core/views/admin/Dashboard.vue'),
            },

            // CMS Module Routes
            ...cmsRoutes,

            // School Module Routes
            ...schoolRoutes,

            {
                path: 'users',
                name: 'users.index',
                component: () => import('@/modules/Core/views/admin/team/users/Index.vue'),
                meta: { permission: 'manage users' },
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: () => import('@/modules/Core/views/admin/team/users/Create.vue'),
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: () => import('@/modules/Core/views/admin/team/users/Edit.vue'),
            },
            {
                path: 'roles',
                name: 'roles',
                component: () => import('@/modules/Core/views/admin/team/roles/Index.vue'),
                meta: { permission: 'view roles' },
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: () => import('@/modules/Core/views/admin/team/roles/Index.vue'),
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: () => import('@/modules/Core/views/admin/team/roles/Index.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/modules/Core/views/admin/settings/general/Index.vue'),
                meta: { permission: 'manage settings' },
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('@/modules/Core/views/admin/Profile.vue'),
            },

            {
                path: 'cache',
                name: 'cache',
                component: () => import('@/modules/Core/views/admin/settings/cache/Index.vue'),
            },
            {
                path: 'backups',
                name: 'backups',
                component: () => import('@/modules/Core/views/admin/settings/backups/Index.vue'),
                meta: { permission: 'manage backups' },
            },
            {
                path: 'security-journal',
                name: 'security-journal',
                component: () => import('@/modules/Core/views/admin/pulse/security/Index.vue'),
            },
            {
                path: 'system',
                name: 'system',
                component: () => import('@/modules/Core/views/admin/settings/system/Index.vue'),
                meta: { permission: 'manage system' },
            },
            {
                path: 'redis',
                name: 'redis',
                component: () => import('@/modules/Core/views/admin/settings/system/Redis.vue'),
                meta: { title: 'Redis Management', permission: 'manage settings' },
            },
            {
                path: 'system/notifications',
                name: 'system-notifications',
                component: () => import('@/modules/Core/views/admin/settings/system/NotificationManager.vue'),
                meta: { title: 'Notification Manager', permission: 'manage system' },
            },
            {
                path: 'activity-journal',
                name: 'activity-journal',
                component: () => import('@/modules/Core/views/admin/pulse/activity/Index.vue'),
            },
            {
                path: 'access-journal',
                name: 'access-journal',
                component: () => import('@/modules/Core/views/admin/pulse/access/Index.vue'),
            },
            {
                path: 'journal-dashboard',
                name: 'journal-dashboard',
                component: () => import('@/modules/Core/views/admin/pulse/Index.vue'),
            },
            {
                path: 'notifications',
                name: 'notifications',
                component: () => import('@/modules/Core/views/admin/settings/notifications/Index.vue'),
            },
            {
                path: 'scheduled-tasks',
                name: 'scheduled-tasks',
                component: () => import('@/modules/Core/views/admin/settings/system/ScheduledTasks.vue'),
                meta: { permission: 'manage scheduled tasks' },
            },
            {
                path: 'languages',
                name: 'languages',
                component: () => import('@/modules/Core/views/admin/settings/languages/Index.vue'),
                meta: { permission: 'manage settings' },
            },

            {
                path: 'system-journal',
                name: 'system-journal',
                component: () => import('@/modules/Core/views/admin/pulse/system/Index.vue'),
            },
            {
                path: 'webhooks',
                name: 'webhooks',
                component: () => import('@/modules/Core/views/admin/dev/webhooks/Index.vue'),
            },
            {
                path: 'plugins',
                name: 'plugins',
                component: () => import('@/modules/Core/views/admin/dev/plugins/Index.vue'),
                meta: { permission: 'manage plugins' },
            },

        ],
    },

    // Error pages
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

    // Catch-all route (must be last)
    {
        path: '/:pathMatch(.*)*',
        name: 'catch-all',
        redirect: { name: 'not-found' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

import { useCmsStore } from '@/modules/Cms/stores/cms';


// Navigation guard
router.beforeEach(async (to, _from, next) => {
    const authStore = useAuthStore();
    const cmsStore = useCmsStore();

    if (!authStore.isAuthenticated && (isProbePath(to.path) || LEGACY_PUBLIC_AUTH_PATHS.includes(to.path as '/login' | '/register'))) {
        return next({ name: 'not-found' });
    }

    // 1. GUEST ACCESS GUARD
    // Redirect unauthenticated users to login if they attempt to access protected routes
    if (!authStore.isAuthenticated) {
        const isPublicRoute = to.matched.some(record => record.meta.public);
        const isAuthRoute = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'maintenance'].includes(to.name as string);
        
        if (!isPublicRoute && !isAuthRoute) {
            next({ name: 'not-found' });
            return;
        }
    }

    /**
     * MAINTENANCE MODE CHECK
     * Ensures we proactively check for global maintenance before allowing public page access
     */

    // 2. Fetch public settings if they aren't loaded yet
    // Skip for maintenance/login routes to break recursion loops if the API is down
    const isSpecialRoute =
        to.name === 'maintenance' ||
        to.name === 'login' ||
        to.name === 'register' ||
        to.name === 'forgot-password' ||
        to.name === 'reset-password' ||
        to.name === 'verify-email' ||
        to.path === '/maintenance' ||
        to.path === loginPath ||
        to.path === registerPath;
    
    // ATOMIC BOOT GUARD: Prevent router from triggering background calls during early setup phase
    if (!cmsStore.publicSettingsLoaded && !isSpecialRoute) {
        // If we are on the root domain, we don't necessarily NEED public settings for basic routing 
        // until we reach a page that uses them. But for safety, we try to fetch.
        try {
            // Stability: Avoid "request bombing" if called multiple times rapidly
            if (!cmsStore.publicSettingsPromise) {
                await cmsStore.fetchPublicSettings();
            } else {
                await cmsStore.publicSettingsPromise;
            }
        } catch (e) {
            logger.error('Failed to preload settings in router guard:', e);
        }
    }

    const isMaintenance = !!cmsStore.siteSettings?.maintenance_mode;
    const canBypassMaintenance = authStore.isAuthenticated || authStore.isAdmin;
    const isMaintenanceRoute = to.name === 'maintenance' || to.path === '/maintenance';
    const isLoginRoute = to.name === 'login' || to.path === loginPath;

    // If maintenance mode is ON, allow:
    // - Authenticated Users (Bypass)
    // - The Maintenance page itself
    // - The Login page (so admins can login to disable maintenance)
    if (isMaintenance && !canBypassMaintenance && !isMaintenanceRoute && !isLoginRoute) {
        next({ name: 'maintenance' });
        return;
    }

    // 3. AUTHENTICATION GUARDS
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresGuest = to.matched.some(record => record.meta.guestOnly);

    if (requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'not-found' });
    }

    if (requiresGuest && authStore.isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    // 4. PERMISSION GUARDS
    const permissionMeta = to.meta.permission as string | undefined;
    const permissionsMeta = to.meta.permissions as string[] | undefined;

    // Most routes use `meta.permission` (single string). Support both shapes.
    const requiredPermissions: string[] = Array.isArray(permissionsMeta)
        ? permissionsMeta
        : (permissionMeta ? [permissionMeta] : []);

    if (requiredPermissions.length > 0) {
        const hasAll = requiredPermissions.every((p) => authStore.hasPermission(p));
        if (!hasAll) {
            return next({ name: 'forbidden' });
        }
    }

    // Check for super admin requirement
    if (to.meta.requiresSuperAdmin && !authStore.isAtLeastRole('super-admin')) {
        next({ name: 'forbidden' });
        return;
    }

    // Allow navigation
    next();
});

// Global error handler
let isHandlingRouterError = false;
router.onError((error) => {
    // Re-entrancy guard: prevent infinite loop when GlobalErrorModal renders
    // (it uses useRouter() which can re-trigger error during error state)
    if (isHandlingRouterError) return;
    isHandlingRouterError = true;

    logger.error('Router error:', error);

    // For runtime router errors, use the modal to avoid full page fallback if possible
    import('@/composables/useSystemError').then(({ useSystemError }) => {
        const { showError } = useSystemError();
        showError({
            code: 500,
            title: 'Application Error',
            message: error.message || 'A critical error occurred while navigating.',
            description: 'The application encountered an unexpected error. Please try refreshing or contact support if the issue persists.',
            reason: 'Router Navigation Error',
            redirect: '/' // Fallback to home
        });
    });

    // Reset guard after a tick to allow future error handling
    setTimeout(() => { isHandlingRouterError = false; }, 1000);
});

// Analytics tracking
let initialNavigationDone = false;
let lastTrackedPath = '';
let lastTrackedAt = 0;

router.afterEach((to, from) => {
    // 1. SILENCE INITIAL BOOT: Prevent the first navigation from triggering analytics
    // which can cause a loop if the API is down during boot.
    if (!initialNavigationDone) {
        initialNavigationDone = true;
        return;
    }

    // Skip tracking for admin (dash) routes or maintenance page
    if (to.path.startsWith(SECURITY_ROUTES.dashboardBase) || to.name === 'maintenance' || to.path === '/maintenance') {
        return;
    }

    // Search typing often updates query params via router.replace().
    // Treat analytics visit as a page-path navigation, not query churn.
    if (to.path === from.path) {
        return;
    }

    // Guard against accidental duplicate tracking on rapid redirect chains.
    const now = Date.now();
    if (to.path === lastTrackedPath && now - lastTrackedAt < 5000) {
        return;
    }
    lastTrackedPath = to.path;
    lastTrackedAt = now;

    const payload = {
        url: window.location.href,
        path: to.path,
        title: document.title,
    };
    const send = () => {
        api.post('/analytics/track-visit', payload).catch((err) => {
            logger.debug('Analytics tracking failed:', err);
        });
    };
    if (typeof requestIdleCallback === 'function') {
        requestIdleCallback(send, { timeout: 2500 });
    } else {
        requestAnimationFrame(send);
    }
});

export default router;
