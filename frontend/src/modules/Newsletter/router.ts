import type { RouteRecordRaw } from 'vue-router';

const newsletterRoutes: RouteRecordRaw[] = [
    {
        path: 'newsletter',
        name: 'newsletter',
        component: () => import('@/modules/Newsletter/views/newsletter/Index.vue'),
        meta: { permission: 'view newsletter' },
    },
    {
        path: 'email-templates',
        name: 'email-templates',
        component: () => import('@/modules/Newsletter/views/email-templates/Index.vue'),
        meta: { permission: 'manage settings' },
    },
    {
        path: 'email-templates/create',
        name: 'email-templates.create',
        component: () => import('@/modules/Newsletter/views/email-templates/Create.vue'),
        meta: { permission: 'manage settings' },
    },
    {
        path: 'email-templates/:id/edit',
        name: 'email-templates.edit',
        component: () => import('@/modules/Newsletter/views/email-templates/Edit.vue'),
        meta: { permission: 'manage settings' },
    },
];

export default newsletterRoutes;
