import type { RouteRecordRaw } from 'vue-router';

const layoutRoutes: RouteRecordRaw[] = [
    {
        path: 'menus',
        name: 'menus',
        component: () => import('@/modules/Layout/views/menus/Index.vue'),
        meta: { permission: 'manage menus' },
    },
    {
        path: 'widgets',
        name: 'widgets',
        component: () => import('@/modules/Layout/views/widgets/Index.vue'),
        meta: { permission: 'manage widgets' },
    },
    {
        path: 'redirects',
        name: 'redirects',
        component: () => import('@/modules/Layout/views/redirects/Index.vue'),
        meta: { permission: 'view redirects' },
    },
];

export default layoutRoutes;
