export const SECURITY_ROUTES = {
    dashboardBase: '/dash',
    login: '/auth/portal-sign-in',
    register: '/auth/portal-sign-up',
    notFound: '/404',
} as const;

export const PROBE_PATH_PREFIXES = [
    '/admin',
    '/panel',
    '/dashboard',
    '/wp-admin',
    '/wp-login.php',
    '/phpmyadmin',
    '/pma',
    '/cpanel',
    '/administrator',
    '/manager',
    '/manage',
] as const;

export const LEGACY_PUBLIC_AUTH_PATHS = ['/login', '/register'] as const;

export const normalizePath = (rawPath: string): string => {
    try {
        return decodeURIComponent(rawPath).toLowerCase();
    } catch {
        return rawPath.toLowerCase();
    }
};

export const matchesPathPrefix = (path: string, prefixes: readonly string[]): boolean => {
    return prefixes.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
};

export const isProbePath = (path: string): boolean => {
    return matchesPathPrefix(normalizePath(path), PROBE_PATH_PREFIXES);
};

export const isProtectedDashboardPath = (path: string): boolean => {
    const normalized = normalizePath(path);
    return normalized.startsWith(SECURITY_ROUTES.dashboardBase) || isProbePath(normalized);
};
