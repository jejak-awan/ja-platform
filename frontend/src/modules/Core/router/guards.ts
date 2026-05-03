import { logger } from '@/utils/logger';
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import { LEGACY_PUBLIC_AUTH_PATHS, isProbePath } from '@/config/security';

interface GuardPaths {
    loginPath: string;
    registerPath: string;
}

export const handleBeforeEachGuard = async (
    to: RouteLocationNormalized,
    next: NavigationGuardNext,
    paths: GuardPaths,
): Promise<void> => {
    const authStore = useAuthStore();
    const cmsStore = useCmsStore();

    if (!authStore.isAuthenticated && (isProbePath(to.path) || LEGACY_PUBLIC_AUTH_PATHS.includes(to.path as '/login' | '/register'))) {
        next({ name: 'not-found' });
        return;
    }

    if (!authStore.isAuthenticated) {
        const isPublicRoute = to.matched.some(record => record.meta.public);
        const isAuthRoute = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'maintenance'].includes(to.name as string);

        if (!isPublicRoute && !isAuthRoute) {
            next({ name: 'not-found' });
            return;
        }
    }

    const isSpecialRoute =
        to.name === 'maintenance' ||
        to.name === 'login' ||
        to.name === 'register' ||
        to.name === 'forgot-password' ||
        to.name === 'reset-password' ||
        to.name === 'verify-email' ||
        to.path === '/maintenance' ||
        to.path === paths.loginPath ||
        to.path === paths.registerPath;

    if (!cmsStore.publicSettingsLoaded && !isSpecialRoute) {
        try {
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
    const isLoginRoute = to.name === 'login' || to.path === paths.loginPath;

    if (isMaintenance && !canBypassMaintenance && !isMaintenanceRoute && !isLoginRoute) {
        next({ name: 'maintenance' });
        return;
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresGuest = to.matched.some(record => record.meta.guestOnly);

    if (requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'not-found' });
        return;
    }

    if (requiresGuest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
        return;
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

    if (to.meta.requiresSuperAdmin && !authStore.isAtLeastRole('super')) {
        next({ name: 'forbidden' });
        return;
    }

    next();
};
