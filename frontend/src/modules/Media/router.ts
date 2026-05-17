import type { RouteRecordRaw } from 'vue-router';

const mediaRoutes: RouteRecordRaw[] = [
    {
        path: 'media',
        name: 'media',
        component: () => import('@/modules/Media/views/media/Index.vue'),
        meta: { permission: 'manage media' },
    },
    {
        path: 'file-manager',
        name: 'file-manager',
        component: () => import('@/modules/Media/views/file-manager/Index.vue'),
        meta: { permission: 'manage files' },
    },
];

export default mediaRoutes;
