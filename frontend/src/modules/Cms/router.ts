import type { RouteRecordRaw } from 'vue-router';

const cmsRoutes: RouteRecordRaw[] = [
    {
        path: 'contents',
        name: 'contents.index',
        component: () => import('@/modules/Cms/views/admin/contents/Index.vue'),
        meta: { permission: 'view content' },
    },
    {
        path: 'contents/create',
        name: 'contents.create',
        component: () => import('@/modules/Cms/views/admin/contents/Create.vue'),
        meta: { permission: 'create content' },
    },
    {
        path: 'contents/:id/edit',
        name: 'contents.edit',
        component: () => import('@/modules/Cms/views/admin/contents/Edit.vue'),
        meta: { permission: 'edit content' },
    },
    {
        path: 'categories',
        name: 'categories.index',
        component: () => import('@/modules/Cms/views/admin/categories/Index.vue'),
        meta: { permission: 'view categories' },
    },
    {
        path: 'content-templates',
        name: 'content-templates.index',
        component: () => import('@/modules/Cms/views/admin/content-templates/Index.vue'),
        meta: { permission: 'manage content templates' },
    },
    {
        path: 'content-templates/create',
        name: 'content-templates.create',
        component: () => import('@/modules/Cms/views/admin/content-templates/Create.vue'),
        meta: { permission: 'manage content templates' },
    },
    {
        path: 'content-templates/:id/edit',
        name: 'content-templates.edit',
        component: () => import('@/modules/Cms/views/admin/content-templates/Edit.vue'),
        meta: { permission: 'manage content templates' },
    },
    {
        path: 'comments',
        name: 'comments.index',
        component: () => import('@/modules/Cms/views/admin/comments/Index.vue'),
        meta: { permission: 'view comments' },
    },
    {
        path: 'themes',
        name: 'themes',
        component: () => import('@/modules/Cms/views/admin/style/themes/Index.vue'),
        meta: { permission: 'manage themes' },
    },
    {
        path: 'themes/:slug/customizer',
        name: 'themes.customizer',
        component: () => import('@/modules/Cms/views/admin/style/themes/ThemeCustomizerWorkspace.vue'),
        meta: { permission: 'manage themes' },
    },
    {
        path: 'cms/settings',
        name: 'cms-settings',
        component: () => import('@/modules/Cms/views/admin/settings/Index.vue'),
        meta: { permission: 'manage settings' },
    },
    {
        path: 'seo',
        name: 'cms.seo',
        component: () => import('@/modules/Cms/views/admin/seo/Index.vue'),
        meta: { permission: 'manage settings' },
    },
];

export default cmsRoutes;
