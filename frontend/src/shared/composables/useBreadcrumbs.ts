import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type { RouteLocationNormalizedLoaded } from 'vue-router';

// Breadcrumb configuration for custom labels
const breadcrumbConfig: Record<string, string> = {
    // Admin routes
    '/admin': 'modules.core.navigation.menu.dashboard',
    '/admin/contents': 'modules.cms.navigation.menu.studio',
    '/admin/media': 'modules.cms.navigation.menu.mediaLibrary',
    '/admin/categories': 'modules.cms.navigation.menu.categories',
    '/admin/tags': 'modules.cms.navigation.menu.tags',
    '/admin/users': 'modules.core.navigation.menu.users',
    '/admin/comments': 'modules.cms.navigation.menu.comments',
    '/admin/forms': 'modules.cms.navigation.menu.forms',
    '/admin/newsletter': 'modules.cms.navigation.menu.newsletter',
    '/admin/roles': 'modules.core.navigation.menu.roles',
    '/admin/journal-dashboard': 'modules.core.navigation.menu.journalDashboard',
    '/admin/activity-journal': 'modules.core.navigation.menu.activityJournal',
    '/admin/access-journal': 'modules.core.navigation.menu.accessJournal',
    '/admin/system-journal': 'modules.core.navigation.menu.systemJournal',
    '/admin/themes': 'modules.cms.navigation.menu.themes',
    '/admin/menus': 'modules.cms.navigation.menu.menus',
    '/admin/widgets': 'modules.cms.navigation.menu.widgets',
    '/admin/languages': 'modules.core.navigation.menu.languages',
    '/admin/analytics': 'modules.cms.navigation.menu.analytics',
    '/admin/seo': 'modules.cms.navigation.menu.seoTools',
    '/admin/redirects': 'modules.cms.navigation.menu.redirects',
    '/admin/settings': 'modules.core.navigation.menu.settings',
    '/admin/backups': 'modules.core.navigation.menu.backups',
    '/admin/security-journal': 'modules.core.navigation.menu.securityJournal',

    '/admin/security-journal/csp-reports': 'features.security.tabs.cspReports',
    '/admin/security-journal/slow-queries': 'features.security.tabs.slowQueries',
    '/admin/security-journal/dependency-vulnerabilities': 'features.security.tabs.vulnerabilities',
    '/admin/redis': 'modules.core.navigation.menu.redis',
    '/admin/system': 'modules.core.navigation.menu.systemInfo',
    '/admin/scheduled-tasks': 'modules.core.navigation.menu.scheduledTasks',
    '/admin/command-runner': 'features.command_runner.title',
    '/admin/webhooks': 'modules.core.navigation.menu.webhooks',
    '/admin/plugins': 'modules.core.navigation.menu.plugins',
    '/admin/custom-fields': 'modules.cms.navigation.menu.customFields',
    '/admin/file-manager': 'modules.core.navigation.menu.fileManager',

    // Auth routes
    '/login': 'modules.core.auth.login.title',
    '/register': 'modules.core.auth.register.title',
    '/forgot-password': 'modules.core.auth.forgotPassword.title',
};

export interface BreadcrumbItem {
    label: string;
    path: string;
}

export function useBreadcrumbs() {
    const { t, te } = useI18n();
    const customBreadcrumbs = ref<Record<string, string>>({});

    /**
     * Get breadcrumb label for a path
     */
    const getLabel = (path: string, route: RouteLocationNormalizedLoaded | null): string => {
        // Check custom breadcrumbs first
        if (customBreadcrumbs.value[path]) {
            return customBreadcrumbs.value[path];
        }

        // Check route meta
        if (route?.meta?.breadcrumb) {
            return route.meta.breadcrumb as string;
        }

        // Check configuration
        if (breadcrumbConfig[path]) {
            return t(breadcrumbConfig[path]);
        }

        // Try to match dynamic routes (e.g., /admin/contents/:id/edit)
        for (const [pattern, labelKey] of Object.entries(breadcrumbConfig)) {
            if (pattern.includes(':')) {
                const regex = new RegExp('^' + pattern.replace(/:\w+/g, '[^/]+') + '$');
                if (regex.test(path)) {
                    return t(labelKey);
                }
            }
        }

        // Fallback: capitalize and clean path segment
        const segment = path.split('/').pop();
        if (!segment) return t('common.navigation.breadcrumbs.home');

        // Check common actions
        if (['create', 'edit'].includes(segment) && te(`common.actions.${segment}`)) {
            return t(`common.actions.${segment}`);
        }

        return segment
            .replace(/-/g, ' ')
            .replace(/\b\w/g, (l) => l.toUpperCase());
    };

    /**
     * Build breadcrumb path from route segments
     */
    const buildPath = (segments: string[], index: number): string => {
        return '/' + segments.slice(0, index + 1).join('/');
    };

    /**
     * Generate breadcrumbs from route
     */
    const getBreadcrumbs = (route: RouteLocationNormalizedLoaded | null): BreadcrumbItem[] => {
        if (!route || !route.path) return [];

        const breadcrumbs: BreadcrumbItem[] = [];
        const pathSegments = route.path.split('/').filter(Boolean);

        // Always add home for admin routes
        if (route.path.startsWith('/dash')) {
            breadcrumbs.push({
                label: t('common.navigation.breadcrumbs.home'),
                path: '/dash',
            });
        }
        // Add home for frontend routes (except home itself)
        else if (route.path !== '/') {
            breadcrumbs.push({
                label: t('common.navigation.breadcrumbs.home'),
                path: '/',
            });
        }
        // If we're on home page, just return empty (or single home item)
        else {
            return [{
                label: t('common.navigation.breadcrumbs.home'),
                path: '/',
            }];
        }

        // Build breadcrumbs from path segments
        pathSegments.forEach((segment, index) => {
            // Skip 'admin' as it's already added as home
            if (segment === 'admin' && index === "0") return;

            const path = buildPath(pathSegments, index);
            const label = getLabel(path, route);

            // Skip if same as previous (avoid duplicates)
            const last = breadcrumbs[breadcrumbs.length - 1];
            if (last && last.path === path) {
                return;
            }

            breadcrumbs.push({
                label,
                path,
            });
        });

        // If route has custom title in meta, use it for last item
        const last = breadcrumbs[breadcrumbs.length - 1];
        if (route.meta?.title && last) {
            const title = route.meta.title as string;
            last.label = te(title) ? t(title) : title;
        }

        return breadcrumbs;
    };

    /**
     * Set custom breadcrumb label for a path
     */
    const setBreadcrumb = (path: string, label: string) => {
        customBreadcrumbs.value[path] = label;
    };

    /**
     * Set multiple custom breadcrumbs at once
     */
    const setBreadcrumbs = (breadcrumbs: Record<string, string>) => {
        customBreadcrumbs.value = {
            ...customBreadcrumbs.value,
            ...breadcrumbs,
        };
    };

    /**
     * Clear custom breadcrumbs
     */
    const clearBreadcrumbs = () => {
        customBreadcrumbs.value = {};
    };

    return {
        getBreadcrumbs,
        setBreadcrumb,
        setBreadcrumbs,
        clearBreadcrumbs,
        getLabel,
    };
}
