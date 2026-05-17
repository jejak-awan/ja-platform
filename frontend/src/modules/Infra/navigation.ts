import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    { 
        name: 'webhooks', 
        to: '/dash/webhooks', 
        label: 'Webhooks', 
        labelKey: 'modules.infra.navigation.menu.webhooks', 
        icon: 'webhook', 
        permission: 'manage webhooks',
        context: 'system',
        group: 'operations',
        priority: 75
    },
];

export default infraNavigation;
