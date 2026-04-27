import { SECURITY_ROUTES } from '@/config/security';

const ADMIN_ENTRYPOINT_PATHS = [
    SECURITY_ROUTES.dashboardBase,
    SECURITY_ROUTES.login,
    SECURITY_ROUTES.register,
    '/forgot-password',
    '/reset-password',
    '/verify-email',
] as const;

export const resolveIsAdminEntrypoint = (pathname: string): boolean => {
    return ADMIN_ENTRYPOINT_PATHS.some((prefix) => (
        pathname === prefix || pathname.startsWith(`${prefix}/`)
    ));
};
