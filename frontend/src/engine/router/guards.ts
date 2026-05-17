import { logger } from '@/shared/utils/logger';
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';
import { useAuthStore } from '@/modules/System/stores/auth';
import { useSystemStore } from '@/modules/System/stores/system';
import { useWorkspaceStore } from '@/engine/stores/workspace';
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
    const systemStore = useSystemStore();

    // 0. Preload Settings Instantly to Resolve Dynamic Dashboard Slug
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

    if (!systemStore.publicSettingsLoaded && !isSpecialRoute) {
        try {
            if (!systemStore.publicSettingsPromise) {
                await systemStore.fetchPublicSettings();
            } else {
                await systemStore.publicSettingsPromise;
            }
        } catch (e) {
            logger.error('Failed to preload settings in router guard:', e);
        }
    }

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

    // 2. Dynamic URL Workspace UUID Redirect & Sync
    const dashboardSlug = systemStore.adminDashboardSlug || 'dash';
    const activePrefix = `/${dashboardSlug}`;
    const startsWithDashboard = to.path.startsWith(activePrefix) || to.path.startsWith('/dash') || to.path.startsWith('/undefined');
    
    if (startsWithDashboard) {
        const segments = to.path.split('/');
        const visitedPrefix = `/${segments[1]}`;
        const workspaceSegment = segments[2];
        
        const isWorkspaceIdentifier = (seg?: string): boolean => {
            if (!seg) return false;
            if (seg === 'system') return true;
            const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
            return uuidRegex.test(seg);
        };
        
        const workspaceStore = useWorkspaceStore();
        
        if (visitedPrefix !== activePrefix || !isWorkspaceIdentifier(workspaceSegment)) {
            let activeWorkspace = 'system';
            if (workspaceStore.activeWorkspaceId && workspaceStore.activeWorkspaceId !== '0') {
                activeWorkspace = workspaceStore.activeWorkspaceId.toString();
            }
            
            const startIdx = isWorkspaceIdentifier(workspaceSegment) ? 3 : 2;
            const remainingPath = segments.slice(startIdx).join('/');
            
            const newPath = remainingPath
                ? `${activePrefix}/${activeWorkspace}/${remainingPath}`
                : `${activePrefix}/${activeWorkspace}`;
                
            logger.info('[Router:Guard] Scoping internal route to active workspace with custom slug', { from: to.path, to: newPath });
            next({
                path: newPath,
                query: to.query,
                hash: to.hash,
                replace: true
            });
            return;
        } else {
            if (workspaceSegment === 'system') {
                if (workspaceStore.activeContextType !== 'system') {
                    logger.info('[Router:Guard] Syncing store to System context');
                    await workspaceStore.setWorkspaceContext('0', 'system', 'System', true);
                }
            } else {
                const unitId = workspaceSegment as string;
                if (workspaceStore.activeWorkspaceId !== unitId) {
                    logger.info('[Router:Guard] Syncing store to Scoped Unit context', { unitId });
                    await workspaceStore.setWorkspaceContext(unitId, 'unit', null, true);
                }
            }
        }
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

    // 3. Authentication & Guest Only Gatekeeping

    // Use CoreStore for maintenance mode (Unified Infrastructure)
    const isMaintenance = !!systemStore.maintenance.mode;
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
