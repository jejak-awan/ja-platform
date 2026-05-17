import type { NavItem } from '@/shared/utils/navigation';

export const searchNavigation: NavItem[] = [
    { 
        name: 'search', 
        to: '/dash/search', 
        label: 'Search & Indexing', 
        labelKey: 'modules.cms.navigation.menu.search', 
        icon: 'search', 
        permission: 'manage search',
        context: 'both',
        group: 'studio',
        priority: 65
    },
];

export default searchNavigation;
