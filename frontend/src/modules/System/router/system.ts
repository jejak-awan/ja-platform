import type { RouteRecordRaw } from 'vue-router';

const systemRoutes: RouteRecordRaw[] = [
    {
        path: 'settings',
        name: 'settings',
        component: () => import('@/modules/System/views/admin/settings/general/Index.vue'),
        meta: { permission: 'manage settings' },
    },
    {
        path: 'cache',
        name: 'cache',
        component: () => import('@/modules/System/views/admin/settings/cache/Index.vue'),
    },
    {
        path: 'backups',
        name: 'backups',
        component: () => import('@/modules/System/views/admin/settings/backups/Index.vue'),
        meta: { permission: 'manage backups' },
    },
    {
        path: 'security-journal',
        name: 'security-journal',
        component: () => import('@/modules/System/views/admin/pulse/security/Index.vue'),
        meta: { noCache: true },
    },
    {
        path: 'system',
        name: 'system',
        component: () => import('@/modules/System/views/admin/settings/system/Index.vue'),
        meta: { permission: 'manage system' },
    },
    {
        path: 'redis',
        name: 'redis',
        component: () => import('@/modules/System/views/admin/settings/system/Redis.vue'),
        meta: { title: 'Redis Management', permission: 'manage settings' },
    },
    {
        path: 'system/notifications',
        name: 'system-notifications',
        component: () => import('@/modules/System/views/admin/settings/system/NotificationManager.vue'),
        meta: { title: 'Notification Manager', permission: 'manage system' },
    },
    {
        path: 'activity-journal',
        name: 'activity-journal',
        component: () => import('@/modules/System/views/admin/pulse/activity/Index.vue'),
        meta: { noCache: true },
    },
    {
        path: 'access-journal',
        name: 'access-journal',
        component: () => import('@/modules/System/views/admin/pulse/access/Index.vue'),
        meta: { noCache: true },
    },
    {
        path: 'journal-dashboard',
        name: 'journal-dashboard',
        component: () => import('@/modules/System/views/admin/pulse/Index.vue'),
        meta: { noCache: true },
    },
    {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/modules/System/views/admin/settings/notifications/Index.vue'),
    },
    {
        path: 'scheduled-tasks',
        name: 'scheduled-tasks',
        component: () => import('@/modules/System/views/admin/settings/system/ScheduledTasks.vue'),
        meta: { permission: 'manage scheduled tasks' },
    },
    {
        path: 'languages',
        name: 'languages',
        component: () => import('@/modules/System/views/admin/settings/languages/Index.vue'),
        meta: { permission: 'manage settings' },
    },
    {
        path: 'system-journal',
        name: 'system-journal',
        component: () => import('@/modules/System/views/admin/pulse/system/Index.vue'),
        meta: { noCache: true },
    },
];

export default systemRoutes;
