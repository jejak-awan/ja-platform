/** Canonical API paths (relative to axios baseURL `/api/v1`). */

export const cmsPaths = {
    contents: '/manage/cms/contents',
    content: (id: string) => `/manage/cms/contents/${id}`,
    publicContents: '/public/cms/contents',
    publicContent: (slug: string) => `/public/cms/contents/${slug}`,
    publicCategories: '/public/cms/categories',
    settings: '/manage/cms/settings',
    comments: '/manage/cms/comments',
    contentTemplates: '/manage/cms/content-templates',
    seo: '/manage/cms/seo',
} as const;

export const libraryPaths = {
    tags: '/manage/library/tags',
    tagStatistics: '/manage/library/tags/statistics',
    tag: (id: string) => `/manage/library/tags/${id}`,
    customFields: '/manage/library/custom-fields',
    customField: (id: string) => `/manage/library/custom-fields/${id}`,
    fieldGroups: '/manage/library/field-groups',
    fieldGroup: (id: string) => `/manage/library/field-groups/${id}`,
    categories: '/manage/library/categories',
    category: (id: string) => `/manage/library/categories/${id}`,
} as const;

export const layoutPaths = {
    menus: '/manage/layout/menus',
    menu: (id: string) => `/manage/layout/menus/${id}`,
    menuRestore: (id: string) => `/manage/layout/menus/${id}/restore`,
    menuForceDelete: (id: string) => `/manage/layout/menus/${id}/force-delete`,
    publicMenuByLocation: (location: string) => `/public/layout/menus/location/${location}`,
    widgets: '/manage/layout/widgets',
    widget: (id: string) => `/manage/layout/widgets/${id}`,
    publicWidgetsByLocation: (location: string) => `/public/layout/widgets/location/${location}`,
    urlRewrites: '/manage/layout/url-rewrites',
    urlRewrite: (id: string) => `/manage/layout/url-rewrites/${id}`,
    themes: '/manage/layout/themes',
    themeLocations: '/manage/layout/themes/active/locations',
    publicThemeActive: '/public/layout/themes/active',
} as const;

export const mediaPaths = {
    index: '/manage/media',
    upload: '/manage/media/upload',
    statistics: '/manage/media/statistics',
    filters: '/manage/media/filters',
    bulk: '/manage/media/bulk',
    emptyTrash: '/manage/media/empty-trash',
    file: (id: string) => `/manage/media/${id}`,
    restore: (id: string) => `/manage/media/${id}/restore`,
    usage: (id: string) => `/manage/media/${id}/usage`,
    thumbnail: (id: string) => `/manage/media/${id}/thumbnail`,
    resize: (id: string) => `/manage/media/${id}/resize`,
    edit: (id: string) => `/manage/media/${id}/edit`,
    folders: '/manage/folders',
    folder: (id: string) => `/manage/folders/${id}`,
} as const;

export const formsPaths = {
    index: '/manage/forms',
    bulkAction: '/manage/forms/bulk-action',
    form: (id: string) => `/manage/forms/${id}`,
    formFields: (formId: string | number) => `/manage/forms/${formId}/fields`,
    formField: (formId: string | number, fieldId: string | number) => `/manage/forms/${formId}/fields/${fieldId}`,
    reorderFields: (formId: string | number) => `/manage/forms/${formId}/reorder-fields`,
    submissions: (formId: string | number) => `/manage/forms/${formId}/submissions`,
    submissionsExport: (formId: string | number) => `/manage/forms/${formId}/submissions/export`,
    submissionsStatistics: (formId: string | number) => `/manage/forms/${formId}/submissions/statistics`,
} as const;

export const formSubmissionPaths = {
    submission: (id: string) => `/manage/form-submissions/${id}`,
    exportPdf: (id: string) => `/manage/form-submissions/${id}/export-pdf`,
} as const;

export const newsletterPaths = {
    subscribe: '/public/newsletter/subscribe',
    subscribers: '/manage/newsletter/subscribers',
    subscriber: (id: string) => `/manage/newsletter/subscribers/${id}`,
    subscriberForce: (id: string) => `/manage/newsletter/subscribers/${id}/force`,
    subscriberRestore: (id: string) => `/manage/newsletter/subscribers/${id}/restore`,
    subscribersExport: '/manage/newsletter/subscribers/export',
    subscribersBulk: '/manage/newsletter/subscribers/bulk',
} as const;

export const searchPaths = {
    public: '/public/search',
    manageStats: '/manage/search/stats',
    manageQueries: '/manage/search/queries',
    deleteQuery: (id: string) => `/manage/search/queries/${id}`,
    clearQueries: '/manage/search/queries/clear',
    reindex: '/manage/search/reindex',
} as const;

export const analyticsPaths = {
    track: '/public/analytics/track',
    trackBatch: '/public/analytics/track/batch',
    trackVisit: '/public/analytics/track-visit',
    overview: '/manage/analytics/overview',
    visits: '/manage/analytics/visits',
    topPages: '/manage/analytics/top-pages',
    topContent: '/manage/analytics/top-content',
    devices: '/manage/analytics/devices',
    browsers: '/manage/analytics/browsers',
    countries: '/manage/analytics/countries',
    referrers: '/manage/analytics/referrers',
    realtime: '/manage/analytics/realtime',
    export: '/manage/analytics/export',
    cleanup: '/manage/analytics/cleanup',
    purgeAll: '/manage/analytics/purge-all',
} as const;

export const systemPaths = {
    emailTemplates: '/manage/system/email-templates',
    emailTemplate: (id: string) => `/manage/system/email-templates/${id}`,
    settings: '/manage/system/settings',
    settingsGroup: (group: string) => `/manage/system/settings/group/${group}`,
    testStorage: '/manage/system/settings/test-storage',
} as const;

export const aiPaths = {
    providers: '/manage/ai/providers',
    models: (provider: string) => `/manage/ai/models/${provider}`,
    generate: '/manage/ai/generate',
} as const;

export const infraPaths = {
    fileManager: '/manage/infra/file-manager',
    fileManagerUpload: '/manage/infra/file-manager/upload',
    fileManagerDownload: '/manage/infra/file-manager/download',
    fileManagerDelete: '/manage/infra/file-manager/delete',
    fileManagerFolder: '/manage/infra/file-manager/folder',
    fileManagerMove: '/manage/infra/file-manager/move',
    fileManagerTrash: '/manage/infra/file-manager/trash',
    fileManagerRestore: '/manage/infra/file-manager/restore',
    fileManagerTrashEmpty: '/manage/infra/file-manager/trash/empty',
    fileManagerTrashPermanent: '/manage/infra/file-manager/trash/permanent',
} as const;
