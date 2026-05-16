import type { NavItem } from '@/shared/utils/navigation';

export const libraryNavigation: NavItem[] = [
    { name: 'tags', to: '/dash/tags', label: 'Tags', labelKey: 'modules.cms.navigation.menu.tags', permission: 'manage content', icon: 'tags' },
    { name: 'custom-fields', to: '/dash/custom-fields', label: 'Custom Fields', labelKey: 'modules.cms.navigation.menu.customFields', permission: 'manage content', icon: 'layers' },
];

export default libraryNavigation;
