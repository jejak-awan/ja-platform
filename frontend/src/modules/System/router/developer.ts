import type { RouteRecordRaw } from 'vue-router';

const developerRoutes: RouteRecordRaw[] = [
    {
        path: 'plugins',
        name: 'plugins',
        component: () => import('@/modules/System/views/admin/dev/plugins/Index.vue'),
        meta: { permission: 'manage plugins' },
    },
];

export default developerRoutes;
