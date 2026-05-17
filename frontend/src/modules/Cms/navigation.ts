import type { NavItem } from '@/shared/utils/navigation';

export const cmsNavigation: NavItem[] = [
    { 
        name: 'contents.index', 
        to: '/dash/contents', 
        label: 'Content', 
        labelKey: 'modules.cms.navigation.menu.studio', 
        permission: 'view content', 
        icon: 'file-text', 
        context: 'both', 
        group: 'studio', 
        priority: 100 
    },
    { 
        name: 'comments.index', 
        to: '/dash/comments', 
        label: 'Comments', 
        labelKey: 'modules.cms.navigation.menu.comments', 
        permission: 'view comments', 
        icon: 'message-square', 
        context: 'both', 
        group: 'studio', 
        priority: 85 
    },
    {
        name: 'themes', 
        to: '/dash/themes', 
        label: 'Themes', 
        labelKey: 'modules.cms.navigation.menu.themes', 
        permission: 'manage themes',
        icon: 'palette',
        context: 'both',
        group: 'design',
        priority: 100
    },
    { 
        name: 'cms.seo', 
        to: '/dash/seo', 
        label: 'SEO', 
        labelKey: 'modules.cms.navigation.menu.seo', 
        permission: 'manage settings',
        icon: 'globe',
        context: 'both',
        group: 'design',
        priority: 80
    },
    { 
        name: 'cms-settings', 
        to: '/dash/cms/settings', 
        label: 'CMS Settings', 
        labelKey: 'modules.cms.navigation.menu.cmsSettings', 
        permission: 'manage settings',
        icon: 'settings',
        context: 'both',
        group: 'design',
        priority: 70
    },
];
