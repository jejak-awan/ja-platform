import type { RouteRecordRaw } from 'vue-router';

const developerRoutes: RouteRecordRaw[] = [
    {
        path: 'webhooks',
        name: 'webhooks',
        component: () => import('@/modules/Core/views/admin/dev/webhooks/Index.vue'),
    },
    {
        path: 'plugins',
        name: 'plugins',
        component: () => import('@/modules/Core/views/admin/dev/plugins/Index.vue'),
        meta: { permission: 'manage plugins' },
    },
    {
        path: 'file-manager',
        name: 'file-manager',
        component: () => import('@/modules/Core/views/admin/system/file-manager/Index.vue'),
        meta: { permission: 'manage files' },
    },
];

export default developerRoutes;
