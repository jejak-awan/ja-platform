import type { RouteRecordRaw } from 'vue-router';

const mediaRoutes: RouteRecordRaw[] = [
    {
        path: 'file-manager',
        name: 'file-manager',
        component: () => import('@/modules/Media/views/admin/file-manager/Index.vue'),
        meta: { permission: 'manage files' },
    },
];

export default mediaRoutes;
