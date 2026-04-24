<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Api\AnalyticsController;
use Modules\Cms\Http\Controllers\Api\CategoryController;
use Modules\Cms\Http\Controllers\Api\CommentController;
use Modules\Cms\Http\Controllers\Api\ContentController;
use Modules\Cms\Http\Controllers\Api\ContentRevisionController;
use Modules\Cms\Http\Controllers\Api\ContentTemplateController;
use Modules\Cms\Http\Controllers\Api\CustomFieldController;
use Modules\Cms\Http\Controllers\Api\EmailTemplateController;
use Modules\Cms\Http\Controllers\Api\FieldGroupController;
use Modules\Cms\Http\Controllers\Api\FormController;
use Modules\Cms\Http\Controllers\Api\FormSubmissionController;
use Modules\Cms\Http\Controllers\Api\MediaController;
use Modules\Cms\Http\Controllers\Api\MediaFolderController;
use Modules\Cms\Http\Controllers\Api\MenuController;
use Modules\Cms\Http\Controllers\Api\NewsletterController;
use Modules\Cms\Http\Controllers\Api\RedirectController;
use Modules\Cms\Http\Controllers\Api\SearchController;
use Modules\Cms\Http\Controllers\Api\SeoController;
use Modules\Cms\Http\Controllers\Api\SettingController;
use Modules\Cms\Http\Controllers\Api\TagController;
use Modules\Cms\Http\Controllers\Api\ThemeController;
use Modules\Cms\Http\Controllers\Api\WidgetController;

Route::prefix('v1')->group(function () {
    // Public CMS API
    Route::prefix('ja')->middleware('throttle:300,1')->group(function () {
        Route::get('/contents', [ContentController::class, 'index']);
        Route::get('/contents/{slug}', [ContentController::class, 'show']);
        Route::get('/contents/{slug}/related', [ContentController::class, 'related']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/tags', [TagController::class, 'index']);
        Route::get('/themes/active', [ThemeController::class, 'getActive']);
        Route::get('/contents/{content}/comments', [CommentController::class, 'index']);
        Route::post('/contents/{content}/comments', [CommentController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:10,1');
        Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->middleware('throttle:10,1');
        Route::get('/menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('/menus/{menu}', [MenuController::class, 'show']);
        Route::get('/widgets/location/{location}', [WidgetController::class, 'getByLocation']);

        // Forms (public) — minimal payload (no raw DB / builder metadata)
        Route::get('/forms/{slug}', [FormController::class, 'publicShow'])->middleware('throttle:forms-public');

        Route::post('/forms/{form:slug}/submit', [FormController::class, 'submit'])->middleware('throttle:10,1');
        Route::post('/forms/{form:slug}/track', [FormController::class, 'track'])->middleware('throttle:forms-track');

        Route::get('/search', [SearchController::class, 'search'])->middleware('throttle:search-public');
        Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->middleware('throttle:search-suggestions');
    });

    // Analytics (Public)
    Route::prefix('analytics')->group(function () {
        Route::post('/track-visit', [AnalyticsController::class, 'trackVisit'])->middleware('throttle:analytics-visit');
        Route::post('/track', [AnalyticsController::class, 'trackEvent'])->middleware('throttle:120,1');
        Route::post('/track/batch', [AnalyticsController::class, 'trackBatch'])->middleware('throttle:120,1');
    });

    // Admin CMS
    Route::prefix('admin/cms')->middleware(['auth:sanctum', 'throttle:admin-cms'])->group(function () {
        // Contents
        Route::get('contents/stats', [ContentController::class, 'stats'])->middleware('permission:view content');
        Route::get('contents', [ContentController::class, 'adminIndex'])->middleware('permission:view content');
        Route::get('contents/{content}', [ContentController::class, 'adminShow'])->middleware('permission:view content');
        Route::post('contents', [ContentController::class, 'store'])->middleware('permission:create content');
        Route::post('contents/autosave', [ContentController::class, 'autosave'])->middleware('permission:create content');
        Route::put('contents/{content}', [ContentController::class, 'update'])->middleware('permission:edit content');
        Route::patch('contents/{content}/autosave', [ContentController::class, 'autosave'])->middleware('permission:edit content');
        Route::delete('contents/{content}', [ContentController::class, 'destroy'])->middleware('permission:delete content');
        Route::delete('contents/trash/empty', [ContentController::class, 'emptyTrash'])->middleware('permission:delete content');
        Route::post('contents/{content}/duplicate', [ContentController::class, 'duplicate'])->middleware('permission:create content');
        Route::post('contents/bulk-action', [ContentController::class, 'bulkAction'])->middleware('permission:edit content');
        Route::put('contents/{content}/approve', [ContentController::class, 'approve'])->middleware('permission:approve content');
        Route::put('contents/{content}/reject', [ContentController::class, 'reject'])->middleware('permission:approve content');
        Route::put('contents/{id}/restore', [ContentController::class, 'restore'])->middleware('permission:delete content');
        Route::patch('contents/{content}/toggle-featured', [ContentController::class, 'toggleFeatured'])->middleware('permission:edit content');
        Route::delete('contents/{id}/force-delete', [ContentController::class, 'forceDelete'])->middleware('permission:delete content');
        Route::post('contents/{content}/lock', [ContentController::class, 'lock'])->middleware('permission:edit content');
        Route::get('contents/{content}/lock-status', [ContentController::class, 'lockStatus'])->middleware('permission:edit content');
        Route::post('contents/{content}/unlock', [ContentController::class, 'unlock'])->middleware('permission:edit content');

        // Newsletter (subscriber management — not core user administration)
        Route::post('newsletter/subscribers/bulk-action', [NewsletterController::class, 'bulkAction'])->middleware('permission:edit newsletter');
        Route::get('newsletter/subscribers', [NewsletterController::class, 'index'])->middleware('permission:view newsletter|edit newsletter');
        Route::delete('newsletter/subscribers/{id}', [NewsletterController::class, 'destroy'])->middleware('permission:delete newsletter|edit newsletter');
        Route::post('newsletter/subscribers/{id}/restore', [NewsletterController::class, 'restore'])->middleware('permission:edit newsletter');
        Route::delete('newsletter/subscribers/{id}/force-delete', [NewsletterController::class, 'forceDelete'])->middleware('permission:delete newsletter');
        Route::get('newsletter/export', [NewsletterController::class, 'export'])->middleware('permission:view newsletter|edit newsletter');

        // Content Revisions
        Route::get('contents/{content}/revisions', [ContentRevisionController::class, 'index'])->middleware('permission:edit content');
        Route::get('contents/{content}/revisions/{revision}', [ContentRevisionController::class, 'show'])->middleware('permission:edit content');
        Route::post('contents/{content}/revisions', [ContentRevisionController::class, 'store'])->middleware('permission:edit content');
        Route::post('contents/{content}/revisions/{revision}/restore', [ContentRevisionController::class, 'restore'])->middleware('permission:edit content');
        Route::delete('contents/{content}/revisions/{revision}', [ContentRevisionController::class, 'destroy'])->middleware('permission:edit content');

        // Categories
        Route::post('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])->middleware('permission:edit categories');
        Route::apiResource('categories', CategoryController::class)->middleware('permission:view categories');
        Route::post('categories/{category}/move', [CategoryController::class, 'move'])->middleware('permission:edit categories');

        // Content Templates
        Route::post('content-templates/bulk-action', [ContentTemplateController::class, 'bulkAction'])->middleware('permission:edit content templates');
        Route::put('content-templates/{id}/restore', [ContentTemplateController::class, 'restore'])->middleware('permission:edit content templates');
        Route::delete('content-templates/{id}/force-delete', [ContentTemplateController::class, 'forceDelete'])->middleware('permission:delete content templates');
        Route::post('content-templates/{content_template}/create-content', [ContentTemplateController::class, 'createContent'])->middleware('permission:create content');
        Route::apiResource('content-templates', ContentTemplateController::class)->middleware('permission:view content templates');

        // Tags
        Route::get('tags/statistics', [TagController::class, 'statistics'])->middleware('permission:view tags');
        Route::post('tags/bulk-delete', [TagController::class, 'bulkDelete'])->middleware('permission:delete tags');
        Route::apiResource('tags', TagController::class)->middleware('permission:view tags');

        // Media
        Route::get('media/filters', [MediaController::class, 'filters'])->middleware('permission:view media');
        Route::get('media/statistics', [MediaController::class, 'statistics'])->middleware('permission:view media');
        Route::post('media/upload', [MediaController::class, 'upload'])->middleware(['throttle:media-upload', 'permission:upload media']);
        Route::post('media/upload-multiple', [MediaController::class, 'uploadMultiple'])->middleware(['throttle:media-upload-multiple', 'permission:upload media']);
        Route::post('media/bulk-action', [MediaController::class, 'bulkAction'])->middleware('permission:edit media');
        Route::delete('media/empty-trash', [MediaController::class, 'emptyTrash'])->middleware('permission:delete media');
        Route::post('media/empty-trash', [MediaController::class, 'emptyTrash'])->middleware('permission:delete media');
        Route::post('media/download-zip', [MediaController::class, 'downloadZip'])->middleware('permission:view media');
        Route::post('media/scan', [MediaController::class, 'scan'])->middleware('permission:manage media');
        Route::get('media', [MediaController::class, 'index'])->middleware('permission:view media');
        Route::get('media/{media}', [MediaController::class, 'show'])->middleware('permission:view media');
        Route::put('media/{media}', [MediaController::class, 'update'])->middleware('permission:edit media');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->middleware('permission:delete media');
        Route::post('media/{media}/delete', [MediaController::class, 'destroy'])->middleware('permission:delete media');
        Route::post('media/{id}/restore', [MediaController::class, 'restore'])->middleware('permission:edit media');
        Route::delete('media/{id}/force-delete', [MediaController::class, 'forceDelete'])->middleware('permission:delete media');
        Route::post('media/{id}/force-delete', [MediaController::class, 'forceDelete'])->middleware('permission:delete media');
        Route::post('media/{media}/thumbnail', [MediaController::class, 'generateThumbnail'])->middleware('permission:edit media');
        Route::post('media/{media}/resize', [MediaController::class, 'resize'])->middleware('permission:edit media');
        Route::post('media/{media}/edit', [MediaController::class, 'edit'])->middleware('permission:edit media');
        Route::get('media/{media}/usage', [MediaController::class, 'usage'])->middleware('permission:view media');

        // Media Folders
        Route::post('media-folders/{id}/restore', [MediaFolderController::class, 'restore'])->middleware('permission:edit media');
        Route::delete('media-folders/{id}/force-delete', [MediaFolderController::class, 'forceDelete'])->middleware('permission:delete media');
        Route::post('media-folders/{id}/force-delete', [MediaFolderController::class, 'forceDelete'])->middleware('permission:delete media');
        Route::apiResource('media-folders', MediaFolderController::class)->middleware('permission:view media');
        Route::post('media-folders/{mediaFolder}/delete', [MediaFolderController::class, 'destroy'])->middleware('permission:delete media');
        Route::post('media-folders/{mediaFolder}/move', [MediaFolderController::class, 'move'])->middleware('permission:edit media');

        // Comments
        Route::get('comments', [CommentController::class, 'adminIndex'])->middleware('permission:manage comments');
        Route::get('comments/statistics', [CommentController::class, 'statistics'])->middleware('permission:manage comments');
        Route::post('comments/bulk', [CommentController::class, 'bulkAction'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/approve', [CommentController::class, 'approve'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/reject', [CommentController::class, 'reject'])->middleware('permission:manage comments');
        Route::put('comments/{comment}/spam', [CommentController::class, 'markAsSpam'])->middleware('permission:manage comments');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('permission:manage comments');

        // SEO
        Route::get('seo/sitemap', [SeoController::class, 'generateSitemap'])->middleware('permission:view settings|manage settings');
        Route::get('seo/robots-txt', [SeoController::class, 'getRobotsTxt'])->middleware('permission:manage settings');
        Route::put('seo/robots-txt', [SeoController::class, 'updateRobotsTxt'])->middleware('permission:manage settings');
        Route::get('contents/{content}/seo-analysis', [SeoController::class, 'analyzeContent'])->middleware('permission:edit content');
        Route::get('contents/{content}/schema', [SeoController::class, 'generateSchema'])->middleware('permission:edit content');

        // Redirects
        Route::get('redirects/statistics', [RedirectController::class, 'statistics'])->middleware('permission:manage settings');
        Route::apiResource('redirects', RedirectController::class)->middleware('permission:manage settings');

        // Themes
        Route::get('themes/active', [ThemeController::class, 'getActive'])->middleware('permission:view themes|manage themes');
        Route::get('themes/active/locations', [ThemeController::class, 'locations'])->middleware('permission:view themes|manage themes');
        Route::apiResource('themes', ThemeController::class)->except(['store'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/activate', [ThemeController::class, 'activate'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/deactivate', [ThemeController::class, 'deactivate'])->middleware('permission:manage themes');
        Route::post('themes/scan', [ThemeController::class, 'scan'])->middleware('permission:manage themes');
        Route::post('themes/{theme}/validate', [ThemeController::class, 'validate'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/setting', [ThemeController::class, 'getSetting'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/settings', [ThemeController::class, 'updateSettings'])->middleware('permission:manage themes');
        Route::put('themes/{theme}/custom-css', [ThemeController::class, 'updateCustomCss'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/components', [ThemeController::class, 'getComponents'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/config', [ThemeController::class, 'getConfig'])->middleware('permission:manage themes');
        Route::get('themes/{theme}/composables', [ThemeController::class, 'getComposables'])->middleware('permission:manage themes');

        // Menus
        Route::post('menus/bulk-action', [MenuController::class, 'bulkAction'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/restore', [MenuController::class, 'restore'])->middleware('permission:manage menus');
        Route::delete('menus/{menu}/force-delete', [MenuController::class, 'forceDelete'])->middleware('permission:manage menus');
        Route::apiResource('menus', MenuController::class)->middleware('permission:manage menus');
        Route::get('menus/{menu}/items', [MenuController::class, 'items'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/items', [MenuController::class, 'addItem'])->middleware('permission:manage menus');
        Route::put('menus/{menu}/items/{menuItem}', [MenuController::class, 'updateItem'])->middleware('permission:manage menus');
        Route::delete('menus/{menu}/items/{menuItem}', [MenuController::class, 'deleteItem'])->middleware('permission:manage menus');
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorderItems'])->middleware('permission:manage menus');
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation'])->middleware('permission:manage menus');

        // Widgets
        Route::get('widgets/locations', [WidgetController::class, 'locations'])->middleware('permission:manage widgets');
        Route::apiResource('widgets', WidgetController::class)->middleware('permission:manage widgets');
        Route::get('widgets/location/{location}', [WidgetController::class, 'getByLocation'])->middleware('permission:manage widgets');
        Route::post('widgets/reorder', [WidgetController::class, 'reorder'])->middleware('permission:manage widgets');

        // Forms
        Route::post('forms/bulk-action', [FormController::class, 'bulkAction'])->middleware('permission:manage forms');
        Route::post('forms/{form}/duplicate', [FormController::class, 'duplicate'])->middleware('permission:manage forms');
        Route::post('forms/{form}/restore', [FormController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('forms/{form}/force-delete', [FormController::class, 'forceDelete'])->middleware('permission:manage forms');
        Route::apiResource('forms', FormController::class)->middleware('permission:manage forms');
        Route::post('forms/{form}/fields', [FormController::class, 'addField'])->middleware('permission:manage forms');
        Route::put('forms/{form}/fields/{formField}', [FormController::class, 'updateField'])->middleware('permission:manage forms');
        Route::delete('forms/{form}/fields/{formField}', [FormController::class, 'deleteField'])->middleware('permission:manage forms');
        Route::post('forms/{form}/reorder-fields', [FormController::class, 'reorderFields'])->middleware('permission:manage forms');

        // Form Submissions
        Route::get('forms/{form}/submissions', [FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('form-submissions', [FormSubmissionController::class, 'index'])->middleware('permission:manage forms');
        Route::get('/form-submissions/{formSubmission}/export-pdf', [FormSubmissionController::class, 'exportPdf'])->middleware('permission:manage forms');
        Route::get('form-submissions/{formSubmission}', [FormSubmissionController::class, 'show'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/read', [FormSubmissionController::class, 'markAsRead'])->middleware('permission:manage forms');
        Route::put('form-submissions/{formSubmission}/archive', [FormSubmissionController::class, 'archive'])->middleware('permission:manage forms');
        Route::delete('form-submissions/{formSubmission}', [FormSubmissionController::class, 'destroy'])->middleware('permission:manage forms');
        Route::post('form-submissions/{formSubmission}/restore', [FormSubmissionController::class, 'restore'])->middleware('permission:manage forms');
        Route::delete('form-submissions/{formSubmission}/force-delete', [FormSubmissionController::class, 'forceDelete'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/export', [FormSubmissionController::class, 'export'])->middleware('permission:manage forms');
        Route::get('forms/{form}/submissions/statistics', [FormSubmissionController::class, 'statistics'])->middleware('permission:manage forms');

        // Search (Admin)
        Route::prefix('search')->group(function () {
            Route::get('', [SearchController::class, 'search'])->middleware('permission:view content');
            Route::get('suggestions', [SearchController::class, 'suggestions'])->middleware('permission:view content');
            Route::get('popular-queries', [SearchController::class, 'popularQueries'])->middleware('permission:view analytics');
            Route::get('no-results-queries', [SearchController::class, 'noResultsQueries'])->middleware('permission:view analytics');
            Route::get('stats', [SearchController::class, 'searchStats'])->middleware('permission:view analytics');
            Route::post('reindex', [SearchController::class, 'reindex'])->middleware('permission:manage content');
        });

        // Custom Fields
        Route::apiResource('field-groups', FieldGroupController::class)->middleware('permission:manage content');
        Route::apiResource('custom-fields', CustomFieldController::class)->middleware('permission:manage content');

        // Email Templates
        Route::apiResource('email-templates', EmailTemplateController::class)->middleware('permission:manage settings');

        // Content Preview
        Route::get('contents/{content}/preview', [ContentController::class, 'preview'])->middleware('permission:edit content');

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('', [SettingController::class, 'index'])->middleware('permission:view settings');
            Route::get('group/{group}', [SettingController::class, 'getGroup'])->middleware('permission:view settings');
            Route::post('bulk-update', [SettingController::class, 'bulkUpdate'])->middleware('permission:manage settings');
            Route::post('test-storage', [SettingController::class, 'testStorage'])->middleware('permission:manage settings');
        });

        // Analytics (Admin)
        Route::prefix('analytics')->middleware('permission:view analytics')->group(function () {
            Route::get('overview', [AnalyticsController::class, 'overview']);
            Route::get('visits', [AnalyticsController::class, 'visits']);
            Route::get('top-pages', [AnalyticsController::class, 'topPages']);
            Route::get('top-content', [AnalyticsController::class, 'topContent']);
            Route::get('devices', [AnalyticsController::class, 'devices']);
            Route::get('browsers', [AnalyticsController::class, 'browsers']);
            Route::get('countries', [AnalyticsController::class, 'countries']);
            Route::get('referrers', [AnalyticsController::class, 'referrers']);
            Route::get('events', [AnalyticsController::class, 'events']);
            Route::get('event-stats', [AnalyticsController::class, 'eventStats']);
            Route::get('realtime', [AnalyticsController::class, 'realTime'])->middleware('throttle:120,1');
            Route::get('export', [AnalyticsController::class, 'export']);
            Route::post('cleanup', [AnalyticsController::class, 'cleanup'])->middleware('permission:manage settings');
            Route::post('purge-all', [AnalyticsController::class, 'purgeAll'])
                ->middleware(['permission:manage settings', 'throttle:5,60']);
        });
    });
});
