import { describe, expect, it } from 'vitest';
import { resolveIsAdminEntrypoint } from '@/core/router/entrypoint';

describe('resolveIsAdminEntrypoint', () => {
    it('returns true for dashboard route and descendants', () => {
        expect(resolveIsAdminEntrypoint('/dash')).toBe(true);
        expect(resolveIsAdminEntrypoint('/dash/users')).toBe(true);
    });

    it('returns true for auth/admin-related routes', () => {
        expect(resolveIsAdminEntrypoint('/auth/portal-sign-in')).toBe(true);
        expect(resolveIsAdminEntrypoint('/auth/portal-sign-up')).toBe(true);
        expect(resolveIsAdminEntrypoint('/forgot-password')).toBe(true);
        expect(resolveIsAdminEntrypoint('/reset-password')).toBe(true);
        expect(resolveIsAdminEntrypoint('/verify-email')).toBe(true);
    });

    it('returns false for public routes', () => {
        expect(resolveIsAdminEntrypoint('/')).toBe(false);
        expect(resolveIsAdminEntrypoint('/about')).toBe(false);
        expect(resolveIsAdminEntrypoint('/blog/slug')).toBe(false);
    });
});
