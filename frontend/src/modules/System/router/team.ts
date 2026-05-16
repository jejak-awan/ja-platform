import type { RouteRecordRaw } from 'vue-router';

const teamRoutes: RouteRecordRaw[] = [
    {
        path: 'users',
        name: 'users.index',
        component: () => import('@/modules/System/views/admin/team/users/Index.vue'),
        meta: { permission: 'manage users' },
    },
    {
        path: 'users/create',
        name: 'users.create',
        component: () => import('@/modules/System/views/admin/team/users/Create.vue'),
    },
    {
        path: 'users/:id/edit',
        name: 'users.edit',
        component: () => import('@/modules/System/views/admin/team/users/Edit.vue'),
    },
    {
        path: 'roles',
        name: 'roles',
        component: () => import('@/modules/System/views/admin/team/roles/Index.vue'),
        meta: { permission: 'view roles' },
    },
    {
        path: 'roles/create',
        name: 'roles.create',
        component: () => import('@/modules/System/views/admin/team/roles/Index.vue'),
    },
    {
        path: 'roles/:id/edit',
        name: 'roles.edit',
        component: () => import('@/modules/System/views/admin/team/roles/Index.vue'),
    },
    {
        path: 'profile',
        name: 'profile',
        component: () => import('@/modules/System/views/admin/Profile.vue'),
    },
];

export default teamRoutes;
