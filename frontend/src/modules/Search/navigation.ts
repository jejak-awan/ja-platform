import type { NavItem } from '@/shared/utils/navigation';

export const searchNavigation: NavItem[] = [
    { name: 'search', to: '/dash/search', label: 'Search & Indexing', labelKey: 'modules.cms.navigation.menu.search', icon: 'search', permission: 'manage search' },
];

export default searchNavigation;
