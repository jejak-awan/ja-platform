import type { NavItem } from '@/shared/utils/navigation';

export const coreNavigation: NavItem[] = [
    {
        label: 'Users & Access', labelKey: 'modules.core.navigation.sections.users_access', icon: 'users', context: 'system', children: [
            { name: 'users.index', to: '/dash/users', label: 'Users', labelKey: 'modules.core.navigation.menu.users', permission: 'view users' },
            { name: 'roles', to: '/dash/roles', label: 'Roles & Permissions', labelKey: 'modules.core.navigation.menu.roles', permission: 'view roles' },
        ]
    },
    {
        label: 'Monitoring', labelKey: 'modules.core.navigation.sections.monitoring', icon: 'activity', context: 'system', role: 'super', children: [
            { name: 'journal-dashboard', to: '/dash/journal-dashboard', label: 'Journal Dashboard', labelKey: 'modules.core.navigation.menu.journalDashboard', permission: 'view logs' },
            { name: 'activity-journal', to: '/dash/activity-journal', label: 'Activity Journal', labelKey: 'modules.core.navigation.menu.activityJournal', permission: 'view activity logs' },
            { name: 'security-journal', to: '/dash/security-journal', label: 'Security Journal', labelKey: 'modules.core.navigation.menu.securityJournal', permission: 'view security logs' },
            { name: 'system-journal', to: '/dash/system-journal', label: 'System Journal', labelKey: 'modules.core.navigation.menu.systemJournal', permission: 'view system' },
            { name: 'access-journal', to: '/dash/access-journal', label: 'Access History', labelKey: 'modules.core.navigation.menu.accessJournal', permission: 'view users' },
        ]
    },
    {
        label: 'Infrastructure', labelKey: 'modules.core.navigation.sections.infrastructure', icon: 'settings', context: 'system', children: [
            { name: 'system', to: '/dash/system', label: 'System Info', labelKey: 'modules.core.navigation.menu.systemInfo', permission: 'view system', role: 'super' },
            { name: 'settings', to: '/dash/settings', label: 'System Settings', labelKey: 'modules.core.navigation.menu.settings', permission: 'view settings' },
            { name: 'system-notifications', to: '/dash/system/notifications', label: 'Notifications', labelKey: 'modules.core.navigation.menu.systemNotifications', permission: 'manage system' },
            { name: 'backups', to: '/dash/backups', label: 'Backups', labelKey: 'modules.core.navigation.menu.backups', permission: 'view backups', role: 'super' },
            { name: 'file-manager', to: '/dash/file-manager', label: 'File Manager', labelKey: 'modules.core.navigation.menu.fileManager', permission: 'manage files' },
            { name: 'redis', to: '/dash/redis', label: 'Redis Cache', labelKey: 'modules.core.navigation.menu.redis', permission: 'manage settings', role: 'super' },
            { name: 'scheduled-tasks', to: '/dash/scheduled-tasks', label: 'Scheduled Tasks', labelKey: 'modules.core.navigation.menu.scheduledTasks', permission: 'view scheduled tasks', role: 'super' },
            { name: 'languages', to: '/dash/languages', label: 'Languages', labelKey: 'modules.core.navigation.menu.languages', permission: 'view settings' },
        ]
    },
    {
        label: 'Developer', labelKey: 'modules.core.navigation.sections.developer', icon: 'code', context: 'system', role: 'super', children: [
            { name: 'webhooks', to: '/dash/webhooks', label: 'Webhooks', labelKey: 'modules.core.navigation.menu.webhooks', permission: 'manage settings' },
            { name: 'plugins', to: '/dash/plugins', label: 'Plugins', labelKey: 'modules.core.navigation.menu.plugins', permission: 'view plugins' },
        ]
    },
];
