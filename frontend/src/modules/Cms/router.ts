import type { RouteRecordRaw } from 'vue-router';

const cmsRoutes: RouteRecordRaw[] = [
    {
        path: 'studio',
        name: 'studio',
        component: () => import('@/modules/Cms/views/admin/studio/Index.vue'),
        meta: { permission: 'manage content' },
    },
    {
        path: 'contents',
        name: 'contents',
        component: () => import('@/modules/Cms/views/admin/studio/contents/Index.vue'),
        meta: { permission: 'manage content' },
    },
    {
        path: 'contents/calendar',
        name: 'contents.calendar',
        component: () => import('@/modules/Cms/views/admin/studio/contents/Calendar.vue'),
    },
    {
        path: 'content-templates/create',
        name: 'content-templates.create',
        component: () => import('@/modules/Cms/views/admin/studio/templates/Create.vue'),
        meta: { permission: 'create content templates' },
    },
    {
        path: 'content-templates/:id/edit',
        name: 'content-templates.edit',
        component: () => import('@/modules/Cms/views/admin/studio/templates/Edit.vue'),
        meta: { permission: 'edit content templates' },
    },
    {
        path: 'contents/create',
        name: 'contents.create',
        component: () => import('@/modules/Cms/views/admin/studio/contents/Create.vue'),
    },
    {
        path: 'contents/:id/edit',
        name: 'contents.edit',
        component: () => import('@/modules/Cms/views/admin/studio/contents/Edit.vue'),
    },
    {
        path: 'contents/:id/revisions',
        name: 'contents.revisions',
        component: () => import('@/modules/Cms/views/admin/studio/contents/Revisions.vue'),
    },
    {
        path: 'media',
        name: 'media',
        component: () => import('@/modules/Media/views/media/Index.vue'),
        meta: { permission: 'manage media' },
    },
    {
        path: 'categories',
        name: 'categories',
        component: () => import('@/modules/Cms/views/admin/studio/categories/Index.vue'),
    },
    {
        path: 'newsletter',
        name: 'newsletter',
        component: () => import('@/modules/Newsletter/views/newsletter/Index.vue'),
    },
    {
        path: 'comments',
        name: 'comments',
        component: () => import('@/modules/Cms/views/admin/studio/comments/Index.vue'),
    },
    {
        path: 'forms',
        name: 'forms',
        component: () => import('@/modules/Forms/views/forms/Index.vue'),
    },
    {
        path: 'forms/create',
        name: 'forms.create',
        component: () => import('@/modules/Forms/views/forms/Create.vue'),
    },
    {
        path: 'forms/:id/edit',
        name: 'forms.edit',
        component: () => import('@/modules/Forms/views/forms/Edit.vue'),
    },
    {
        path: 'forms/:id/submissions',
        name: 'forms.submissions',
        component: () => import('@/modules/Forms/views/forms/SubmissionsPage.vue'),
    },
    {
        path: 'forms/:id/analytics',
        name: 'forms.analytics',
        component: () => import('@/modules/Forms/views/forms/AnalyticsPage.vue'),
    },
    {
        path: 'tags',
        name: 'tags',
        component: () => import('@/modules/Library/views/tags/Index.vue'),
    },
    {
        path: 'email-templates',
        name: 'email-templates',
        component: () => import('@/modules/Newsletter/views/email-templates/Index.vue'),
    },
    {
        path: 'email-templates/create',
        name: 'email-templates.create',
        component: () => import('@/modules/Newsletter/views/email-templates/Create.vue'),
    },
    {
        path: 'email-templates/:id/edit',
        name: 'email-templates.edit',
        component: () => import('@/modules/Newsletter/views/email-templates/Edit.vue'),
    },
    {
        path: 'seo',
        name: 'seo',
        component: () => import('@/modules/System/views/admin/seo/Index.vue'),
    },
    {
        path: 'redirects',
        name: 'redirects',
        component: () => import('@/modules/Layout/views/redirects/Index.vue'),
    },
    {
        path: 'custom-fields',
        name: 'custom-fields',
        component: () => import('@/modules/Library/views/custom-fields/Index.vue'),
    },
    {
        path: 'search',
        name: 'search',
        component: () => import('@/modules/Search/views/search/Index.vue'),
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
        path: 'analytics',
        name: 'analytics',
        component: () => import('@/modules/System/views/admin/analytics/Index.vue'),
        meta: { permission: 'view analytics' },
    },
    {
        path: 'cms/settings',
        name: 'cms-settings',
        component: () => import('@/modules/Cms/views/admin/settings/Index.vue'),
        meta: { permission: 'manage settings' },
    },
];

export default cmsRoutes;
