import { logger } from '@/shared/utils/logger';
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/modules/System/stores/auth';
import { useSystemStore } from '@/modules/System/stores/system';
import { LEGACY_PUBLIC_AUTH_PATHS, isProbePath } from '@/config/security';

interface GuardPaths {
    loginPath: string;
    registerPath: string;
    adminPath?: string;
}

export const handleBeforeEachGuard = async (
    to: RouteLocationNormalized,
    next: NavigationGuardNext,
    paths: GuardPaths,
): Promise<void> => {
    const authStore = useAuthStore();
    const coreStore = useSystemStore();

    // 1. Workspace Context Interceptor
    if (to.query.unit_context !== undefined) {
        const unitId = to.query.unit_context as string;
        sessionStorage.setItem('active_workspace_id', unitId);
        
        const newQuery = { ...to.query };
        delete newQuery.unit_context;
        
        if (to.name) {
            next({ name: to.name, params: to.params, query: newQuery, hash: to.hash, replace: true });
        } else {
            next({ path: to.path, query: newQuery, hash: to.hash, replace: true });
        }
        return;
    }

    if (!authStore.isAuthenticated && (isProbePath(to.path) || LEGACY_PUBLIC_AUTH_PATHS.includes(to.path as '/login' | '/register'))) {
        next({ name: 'not-found' });
        return;
    }

    if (!authStore.isAuthenticated) {
        const isPublicRoute = to.matched.some(record => record.meta.public);
        const isAuthRoute = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'maintenance', 'session-expired'].includes(to.name as string);

        if (!isPublicRoute && !isAuthRoute) {
            if (to.matched.some(record => record.meta.requiresAuth || record.meta.auth)) {
                next({ name: 'not-found' });
                return;
            }
        }
    }

    const isSpecialRoute =
        to.name === 'maintenance' ||
        to.name === 'login' ||
        to.name === 'register' ||
        to.name === 'forgot-password' ||
        to.name === 'reset-password' ||
        to.name === 'verify-email' ||
        to.name === 'session-expired' ||
        to.path === '/maintenance' ||
        to.path === paths.loginPath ||
        to.path === paths.registerPath;

    if (!coreStore.publicSettingsLoaded && !isSpecialRoute) {
        try {
            if (!coreStore.publicSettingsPromise) {
                await coreStore.fetchPublicSettings();
            } else {
                await coreStore.publicSettingsPromise;
            }
        } catch (e) {
            logger.error('Failed to preload settings in router guard:', e);
        }
    }

    // Use CoreStore for maintenance mode (Unified Infrastructure)
    const isMaintenance = !!coreStore.maintenance.mode;
    const canBypassMaintenance = authStore.isAuthenticated && authStore.getRoleRank() >= 90;
    const isMaintenanceRoute = to.name === 'maintenance' || to.path === '/maintenance';
    const isLoginRoute = to.name === 'login' || to.path === paths.loginPath;

    if (isMaintenance && !canBypassMaintenance && !isMaintenanceRoute && !isLoginRoute) {
        next({ name: 'maintenance' });
        return;
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth || record.meta.auth);
    const requiresGuest = to.matched.some(record => record.meta.guestOnly);

    if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'not-found' });
        return;
    }

    if (requiresGuest && authStore.isAuthenticated) {
        if (to.name === 'login' || to.name === 'register') {
            next({ name: 'dashboard' });
            return;
        }
    }

    const permissionMeta = to.meta.permission as string | undefined;
    const permissionsMeta = to.meta.permissions as string[] | undefined;
    const requiredPermissions: string[] = Array.isArray(permissionsMeta)
        ? permissionsMeta
        : (permissionMeta ? [permissionMeta] : []);

    if (requiredPermissions.length > 0) {
        const hasAll = requiredPermissions.every((p) => authStore.hasPermission(p));
        if (!hasAll) {
            next({ name: 'forbidden' });
            return;
        }
    }

    if (to.meta.requiresSuperAdmin && authStore.getRoleRank() < 100) {
        next({ name: 'forbidden' });
        return;
    }

    next();
};
