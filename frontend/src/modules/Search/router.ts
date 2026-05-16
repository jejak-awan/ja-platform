import type { RouteRecordRaw } from 'vue-router';

const searchRoutes: RouteRecordRaw[] = [
    {
        path: 'search',
        name: 'search',
        component: () => import('@/modules/Search/views/search/Index.vue'),
        meta: { permission: 'manage search' },
    },
];

export default searchRoutes;
