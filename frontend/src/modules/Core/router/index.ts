import type { RouteRecordRaw } from 'vue-router';
import systemRoutes from './system';
import teamRoutes from './team';
import developerRoutes from './developer';

const coreRoutes: RouteRecordRaw[] = [
    {
        path: 'dashboard',
        name: 'core.dashboard',
        component: () => import('@/modules/Core/views/admin/Dashboard.vue'),
        meta: { permission: 'view dashboard' },
    },
    ...systemRoutes,
    ...teamRoutes,
    ...developerRoutes,
];

export default coreRoutes;
