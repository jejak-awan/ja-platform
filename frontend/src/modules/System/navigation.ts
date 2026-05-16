import type { NavItem } from '@/shared/utils/navigation';

export const systemNavigation: NavItem[] = [
    {
        label: 'Users & Access', labelKey: 'modules.system.navigation.sections.users_access', icon: 'users', context: 'system', children: [
            { name: 'users.index', to: '/dash/users', label: 'Users', labelKey: 'modules.system.navigation.menu.users', permission: 'view users' },
            { name: 'roles', to: '/dash/roles', label: 'Roles & Permissions', labelKey: 'modules.system.navigation.menu.roles', permission: 'view roles' },
        ]
    },
    {
        label: 'Monitoring', labelKey: 'modules.system.navigation.sections.monitoring', icon: 'activity', context: 'system', role: 'super', children: [
            { name: 'journal-dashboard', to: '/dash/journal-dashboard', label: 'Journal Dashboard', labelKey: 'modules.system.navigation.menu.journalDashboard', permission: 'view logs' },
            { name: 'activity-journal', to: '/dash/activity-journal', label: 'Activity Journal', labelKey: 'modules.system.navigation.menu.activityJournal', permission: 'view activity logs' },
            { name: 'security-journal', to: '/dash/security-journal', label: 'Security Journal', labelKey: 'modules.system.navigation.menu.securityJournal', permission: 'view security logs' },
            { name: 'system-journal', to: '/dash/system-journal', label: 'System Journal', labelKey: 'modules.system.navigation.menu.systemJournal', permission: 'view system' },
            { name: 'access-journal', to: '/dash/access-journal', label: 'Access History', labelKey: 'modules.system.navigation.menu.accessJournal', permission: 'view users' },
        ]
    },
    {
        label: 'Infrastructure', labelKey: 'modules.system.navigation.sections.infrastructure', icon: 'settings', context: 'system', children: [
            { name: 'system', to: '/dash/system', label: 'System Info', labelKey: 'modules.system.navigation.menu.systemInfo', permission: 'view system', role: 'super' },
            { name: 'settings', to: '/dash/settings', label: 'System Settings', labelKey: 'modules.system.navigation.menu.settings', permission: 'view settings' },
            { name: 'system-notifications', to: '/dash/system/notifications', label: 'Notifications', labelKey: 'modules.system.navigation.menu.systemNotifications', permission: 'manage system' },
            { name: 'backups', to: '/dash/backups', label: 'Backups', labelKey: 'modules.system.navigation.menu.backups', permission: 'view backups', role: 'super' },
            { name: 'redis', to: '/dash/redis', label: 'Redis Cache', labelKey: 'modules.system.navigation.menu.redis', permission: 'manage settings', role: 'super' },
            { name: 'scheduled-tasks', to: '/dash/scheduled-tasks', label: 'Scheduled Tasks', labelKey: 'modules.system.navigation.menu.scheduledTasks', permission: 'view scheduled tasks', role: 'super' },
            { name: 'languages', to: '/dash/languages', label: 'Languages', labelKey: 'modules.system.navigation.menu.languages', permission: 'view settings' },
        ]
    },
    {
        label: 'Developer', labelKey: 'modules.system.navigation.sections.developer', icon: 'code', context: 'system', role: 'super', children: [
            { name: 'plugins', to: '/dash/plugins', label: 'Plugins', labelKey: 'modules.system.navigation.menu.plugins', permission: 'view plugins' },
        ]
    },
];
