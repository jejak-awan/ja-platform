import type { NavItem } from '@/shared/utils/navigation';

export const layoutNavigation: NavItem[] = [
    { name: 'menus', to: '/dash/menus', label: 'Menus', labelKey: 'modules.cms.navigation.menu.menus', permission: 'view menus', icon: 'menu' },
    { name: 'widgets', to: '/dash/widgets', label: 'Widgets', labelKey: 'modules.cms.navigation.menu.widgets', permission: 'view widgets', icon: 'layout' },
    { name: 'redirects', to: '/dash/redirects', label: 'Redirects', labelKey: 'modules.cms.navigation.menu.redirects', permission: 'view redirects', icon: 'undo' },
];

export default layoutNavigation;
