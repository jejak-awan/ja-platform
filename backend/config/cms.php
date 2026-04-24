<?php

/**
 * CMS / tema — override ENV (hanya file di config/ aplikasi yang boleh memakai env() untuk Larastan + config:cache).
 * Modul Cms meng-merge `name` dll. ke kunci `cms` setelah file ini dimuat.
 */
return [
    'profile_public_theme_api' => filter_var(env('PROFILE_PUBLIC_THEME_API', false), FILTER_VALIDATE_BOOL),
    'public_active_theme_http_cache_max_age' => max(0, (int) env('CMS_PUBLIC_ACTIVE_THEME_HTTP_CACHE_MAX_AGE', 60)),
    /** Relative to backend `base_path()`; default matches ja-apps monorepo (frontend package). */
    'theme_views_relative_path' => env('CMS_THEME_VIEWS_RELATIVE_PATH', '../frontend/src/modules/Cms/views/themes'),
];
