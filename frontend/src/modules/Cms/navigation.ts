import type { NavItem } from '@/shared/utils/navigation';

export const cmsNavigation: NavItem[] = [
    {
        label: 'Content', labelKey: 'modules.cms.navigation.sections.content_studio', icon: 'layers', context: 'both', children: [
            { name: 'contents.index', to: '/dash/contents', label: 'Contents', labelKey: 'modules.cms.navigation.menu.studio', permission: 'view content' },
            { name: 'categories.index', to: '/dash/categories', label: 'Categories', labelKey: 'modules.cms.navigation.menu.categories', permission: 'view categories' },
            { name: 'content-templates.index', to: '/dash/content-templates', label: 'Templates', labelKey: 'modules.cms.navigation.menu.contentTemplates', permission: 'manage content templates' },
            { name: 'comments.index', to: '/dash/comments', label: 'Comments', labelKey: 'modules.cms.navigation.menu.comments', permission: 'view comments' },
        ]
    },
    {
        label: 'Design & Configuration', labelKey: 'modules.cms.navigation.sections.design_config', icon: 'palette', context: 'both', children: [
            { name: 'themes', to: '/dash/themes', label: 'Themes', labelKey: 'modules.cms.navigation.menu.themes', permission: 'manage themes' },
            { name: 'cms.seo', to: '/dash/seo', label: 'SEO', labelKey: 'modules.cms.navigation.menu.seo', permission: 'manage settings' },
            { name: 'cms-settings', to: '/dash/cms/settings', label: 'CMS Settings', labelKey: 'modules.cms.navigation.menu.cmsSettings', permission: 'manage settings' },
        ]
    },
];
