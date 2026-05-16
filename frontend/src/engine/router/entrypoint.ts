import { SECURITY_ROUTES } from '@/config/security';

declare global {
    interface Window {
        __JA_GATE__?: 'admin' | 'public';
    }
}

const ADMIN_ENTRYPOINT_PATHS = [
    SECURITY_ROUTES.dashboardBase,
    SECURITY_ROUTES.login,
    SECURITY_ROUTES.register,
    '/login',
    '/register',
    '/public/system/auth/forgot-password',
    '/public/system/auth/reset-password',
    '/verify-email',
] as const;

const SHARED_SYSTEM_PATHS = [
    '/403',
    '/404',
    '/500',
    '/419',
    '/429',
    '/maintenance',
    '/install',
] as const;

export const resolveIsAdminEntrypoint = (pathname: string): boolean => {
    // Standardize path: remove trailing slash for comparison
    const cleanPath = pathname === '/' ? '/' : pathname.replace(/\/$/, '');

    // Root is ALWAYS public
    if (cleanPath === '/' || cleanPath === '') return false;

    // Shared system paths are NEUTRAL - they don't trigger redirection
    if (SHARED_SYSTEM_PATHS.some(p => cleanPath === p || cleanPath.startsWith(`${p}/`))) {
        // We are already on a gate, and it's a neutral path, so it's fine.
        return typeof window !== 'undefined' && window.__JA_GATE__ === 'admin';
    }

    return ADMIN_ENTRYPOINT_PATHS.some((prefix) => (
        cleanPath === prefix || cleanPath.startsWith(`${prefix}/`)
    ));
};
