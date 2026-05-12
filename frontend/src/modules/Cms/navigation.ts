import type { NavItem } from '@/shared/utils/navigation';

export const cmsNavigation: NavItem[] = [
    {
        label: 'Content', labelKey: 'modules.cms.navigation.sections.content_studio', icon: 'layers', context: 'both', children: [
            { name: 'studio', to: '/dash/studio', label: 'Contents', labelKey: 'modules.cms.navigation.menu.studio', permission: 'view content' },
            { name: 'media', to: '/dash/media', label: 'Media Library', labelKey: 'modules.cms.navigation.menu.mediaLibrary', permission: 'view media' },
            { name: 'comments', to: '/dash/comments', label: 'Comments', labelKey: 'modules.cms.navigation.menu.comments', permission: 'view comments' },
        ]
    },
    {
        label: 'Marketing & Insights', labelKey: 'modules.cms.navigation.sections.marketing_insights', icon: 'megaphone', context: 'both', children: [
            { name: 'analytics', to: '/dash/analytics', label: 'Analytics', labelKey: 'modules.cms.navigation.menu.analytics', permission: 'view analytics' },
            { name: 'forms', to: '/dash/forms', label: 'Forms', labelKey: 'modules.cms.navigation.menu.forms', permission: 'view forms' },
            { name: 'newsletter', to: '/dash/newsletter', label: 'Newsletter', labelKey: 'modules.cms.navigation.menu.newsletter', permission: 'view newsletter' },
            { name: 'email-templates', to: '/dash/email-templates', label: 'Email Templates', labelKey: 'modules.cms.navigation.menu.emailTemplates', permission: 'manage settings' },
        ]
    },
    {
        label: 'SEO & Optimization', labelKey: 'modules.cms.navigation.sections.seo_optimization', icon: 'search', context: 'both', children: [
            { name: 'seo', to: '/dash/seo', label: 'SEO Tools', labelKey: 'modules.cms.navigation.menu.seoTools', permission: 'manage settings' },
            { name: 'redirects', to: '/dash/redirects', label: 'Redirects', labelKey: 'modules.cms.navigation.menu.redirects', permission: 'view redirects' },
        ]
    },
    {
        label: 'Design & Configuration', labelKey: 'modules.cms.navigation.sections.design_config', icon: 'palette', context: 'both', children: [
            { name: 'themes', to: '/dash/themes', label: 'Themes', labelKey: 'modules.cms.navigation.menu.themes', permission: 'view themes' },
            { name: 'menus', to: '/dash/menus', label: 'Menus', labelKey: 'modules.cms.navigation.menu.menus', permission: 'view menus' },
            { name: 'widgets', to: '/dash/widgets', label: 'Widgets', labelKey: 'modules.cms.navigation.menu.widgets', permission: 'view widgets' },
            { name: 'cms-settings', to: '/dash/cms/settings', label: 'CMS Settings', labelKey: 'modules.cms.navigation.menu.cmsSettings', permission: 'manage settings' },
        ]
    },
    { type: 'divider', label: 'advanced', labelKey: 'modules.core.navigation.sections.advanced', context: 'foundation' },
    { name: 'custom-fields', to: '/dash/custom-fields', label: 'Custom Fields', labelKey: 'modules.cms.navigation.menu.customFields', icon: 'custom-fields', permission: 'manage content', context: 'foundation' },
];
