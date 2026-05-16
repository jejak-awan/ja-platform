import type { NavItem } from '@/shared/utils/navigation';

export const mediaNavigation: NavItem[] = [
    { name: 'media', to: '/dash/media', label: 'Media Library', labelKey: 'modules.cms.navigation.menu.mediaLibrary', permission: 'view media', icon: 'image' },
];
