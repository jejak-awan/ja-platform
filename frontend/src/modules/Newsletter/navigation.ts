import type { NavItem } from '@/shared/utils/navigation';

export const newsletterNavigation: NavItem[] = [
    { 
        name: 'newsletter', 
        to: '/dash/newsletter', 
        label: 'Newsletter', 
        labelKey: 'modules.cms.navigation.menu.newsletter', 
        permission: 'view newsletter', 
        icon: 'mail',
        context: 'both',
        group: 'studio',
        priority: 75
    },
    { 
        name: 'email-templates', 
        to: '/dash/email-templates', 
        label: 'Email Templates', 
        labelKey: 'modules.cms.navigation.menu.emailTemplates', 
        permission: 'manage settings', 
        icon: 'layout',
        context: 'both',
        group: 'studio',
        priority: 74
    },
];

export default newsletterNavigation;
