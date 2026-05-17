import type { NavItem } from '@/shared/utils/navigation';

export const mediaNavigation: NavItem[] = [
    { 
        name: 'media', 
        to: '/dash/media', 
        label: 'Media Library', 
        labelKey: 'common.navigation.menu.mediaLibrary', 
        permission: 'view media', 
        icon: 'image',
        context: 'both',
        group: 'studio',
        priority: 90
    },
    { 
        name: 'file-manager', 
        to: '/dash/file-manager', 
        label: 'File Manager', 
        labelKey: 'common.navigation.menu.fileManager', 
        permission: 'manage files', 
        icon: 'folder',
        context: 'both',
        group: 'studio',
        priority: 70
    },
];
