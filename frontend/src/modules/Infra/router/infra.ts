import type { RouteRecordRaw } from 'vue-router';

const infraRoutes: RouteRecordRaw[] = [
    {
        path: 'webhooks',
        name: 'webhooks',
        component: () => import('@/modules/Infra/views/admin/webhooks/Index.vue'),
        meta: { permission: 'manage webhooks' },
    },
];

export default infraRoutes;
