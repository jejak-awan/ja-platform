# CMS theming (JA Platform)

This document describes how public themes are wired between the Laravel API and the Vue SPA.

## Layout

- **Vue theme source** lives under the frontend package: `frontend/src/modules/Cms/views/themes/{slug}/` (see `CMS_THEME_VIEWS_RELATIVE_PATH` in backend `config/cms.php`).
- **Backend** reads `theme.json` from that path via `Theme::getThemePath()` and `ThemeViews` helper. `php artisan theme:make` creates folders there; `scanThemes()` registers rows from the same root.
- **Active theme API**: public `GET /api/v1/ja/themes/active` returns the serialized theme (settings, manifest, assets, `supports`, timestamps). HTTP cache headers are controlled by `CMS_PUBLIC_ACTIVE_THEME_HTTP_CACHE_MAX_AGE`.

## SPA resolution

- **`useTheme`** loads the active theme once (deduplicated), unwraps the standard API envelope, injects CSS/JS assets, applies `settings_schema` CSS variables, and listens for `THEME_UPDATE` postMessages only from `window.location.origin`.
- **`ThemePageResolver`** maps `page="components/Header"` (etc.) to `views/themes/{slug}/...` using Vite `import.meta.glob`. If the slug folder is missing views, set **`parent_theme`** on the theme row to the slug that owns the Vue files (see `php artisan cms:themes:backfill-janari-parent`).

## Janari canvas (optional stack)

Themes that reuse the Janari preset / data-attribute surface should declare either:

- `manifest.supports.janari_canvas: true` in `theme.json`, or
- the same flag on the **`supports`** JSON column in the `themes` table,

**or** keep a slug prefixed with `janari` (legacy). When enabled, the layout adds `theme-janari`, drives `data-janari-*` attributes, and `useTheme` syncs accent CSS variables (`janari.css` is loaded globally from `app.ts`).

## Cache

- Theme payload and fragments are cached in Laravel; `ThemeCacheService::clearTheme` / `clearAll` avoid flushing the entire app cache on non-tagged drivers.

## Tests & quality

- Backend: PHPUnit + `composer run quality` (PHPStan).
- Frontend: `npm run quality:frontend` (ESLint, `vue-tsc`, Playwright list). Vitest covers helpers such as `themeManifest.ts` under `tests/unit/`.
