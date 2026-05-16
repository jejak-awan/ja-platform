import type { NavItem } from '@/shared/utils/navigation';

export const formsNavigation: NavItem[] = [
    { name: 'forms', to: '/dash/forms', label: 'Forms', labelKey: 'modules.cms.navigation.menu.forms', permission: 'view forms', icon: 'clipboard-list' },
];

export default formsNavigation;
