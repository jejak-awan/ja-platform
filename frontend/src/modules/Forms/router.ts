import type { RouteRecordRaw } from 'vue-router';

const formsRoutes: RouteRecordRaw[] = [
    {
        path: 'forms',
        name: 'forms',
        component: () => import('@/modules/Forms/views/forms/Index.vue'),
        meta: { permission: 'view forms' },
    },
    {
        path: 'forms/create',
        name: 'forms.create',
        component: () => import('@/modules/Forms/views/forms/Create.vue'),
        meta: { permission: 'manage forms' },
    },
    {
        path: 'forms/:id/edit',
        name: 'forms.edit',
        component: () => import('@/modules/Forms/views/forms/Edit.vue'),
        meta: { permission: 'manage forms' },
    },
    {
        path: 'forms/:id/submissions',
        name: 'forms.submissions',
        component: () => import('@/modules/Forms/views/forms/SubmissionsPage.vue'),
        meta: { permission: 'view forms' },
    },
    {
        path: 'forms/:id/analytics',
        name: 'forms.analytics',
        component: () => import('@/modules/Forms/views/forms/AnalyticsPage.vue'),
        meta: { permission: 'view forms' },
    },
];

export default formsRoutes;
