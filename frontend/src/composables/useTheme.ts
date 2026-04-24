import { logger } from '@/utils/logger';
import { ref, computed } from 'vue';
import api from '@/services/api';
import { JANARI_PRESETS, type JanariPresetKey } from '@/modules/Cms/config/janariPresets';

export interface ThemeManifest {
    name?: string;
    version?: string;
    author?: string;
    settings_schema?: Record<string, ThemeSettingSchema>;
    [key: string]: unknown;
}

export interface ThemeSettingSchema {
    type: 'text' | 'color' | 'font' | 'typography' | 'select' | 'boolean';
    label?: string;
    default?: unknown;
    options?: unknown[];
}

export interface Theme {
    name: string;
    slug: string;
    type: string;
    manifest?: ThemeManifest;
    settings?: Record<string, unknown>;
    assets?: {
        css?: string[];
        js?: string[];
    };
    custom_css?: string;
    [key: string]: unknown;
}

// Global shared state
const activeTheme = ref<Theme | null>(null);
const themeSettings = ref<Record<string, unknown>>({});
const themeAssets = ref<{ css: string[]; js: string[] }>({ css: [], js: [] });
const customCss = ref('');
const cssVariables = ref('');
const loading = ref(false);
const error = ref<string | null>(null);
const isLoading = ref(false); // Prevent multiple simultaneous loads
let activeLoadPromise: Promise<void> | null = null; // Shared promise to await if load is in progress
let themeUpdateListener: ((event: MessageEvent) => void) | null = null;

/**
 * Composable for theme management in frontend
 */
export function useTheme() {
    /**
     * Load active theme
     */
    const loadActiveTheme = async (type = 'frontend') => {
        // Return existing promise if already loading
        if (activeLoadPromise && type === 'frontend') {
            return activeLoadPromise;
        }

        // Return immediately if already loaded and no force reload is requested
        if (activeTheme.value && type === 'frontend' && !loading.value) {
            return;
        }

        isLoading.value = true;
        loading.value = true;
        error.value = null;

        activeLoadPromise = (async () => {
            try {
            // Use public endpoint for frontend theme (no auth required)
            const endpoint = type === 'frontend'
                ? `/ja/themes/active?type=${type}`
                : `/admin/cms/themes/active?type=${type}`;

            const response = await api.get(endpoint);
            const data = response.data;

            // Handle null response (no active theme)
            // Stability: Deep compare before setting to avoid unnecessary reactive triggers
            const newDataString = JSON.stringify(data);
            const oldDataString = JSON.stringify(activeTheme.value);

            if (newDataString !== oldDataString) {
                activeTheme.value = data;
                themeSettings.value = data.settings || {};
            }

            // Load theme assets
            if (data.assets) {
                themeAssets.value = data.assets;
                injectCssFiles(data.assets.css || []);
                injectJsFiles(data.assets.js || []);
            }

            if (data.custom_css) {
                customCss.value = data.custom_css;
                applyCustomCss();
            }

            applyThemeStyles();

            // Sync Janari Styles (Accent + Background) on initial load
            const slug = data.slug || '';
            if (slug.startsWith('janari')) {
                syncJanariStyles(themeSettings.value);
            }

            // Add listener for Theme Customizer updates if in frontend
            if (type === 'frontend' && typeof window !== 'undefined') {
                if (themeUpdateListener) {
                    window.removeEventListener('message', themeUpdateListener);
                }
                themeUpdateListener = (event: MessageEvent) => {
                    if (event.data && event.data.type === 'THEME_UPDATE') {
                        // Merge the new settings reactively
                        if (event.data.settings) {
                            themeSettings.value = {
                                ...themeSettings.value,
                                ...event.data.settings
                            };

                            // Batch update for Janari Styles (Accent + Background)
                            const slug = activeTheme.value?.slug || '';
                            if (slug.startsWith('janari')) {
                                requestAnimationFrame(() => {
                                    // Consolidated sync
                                    syncJanariStyles(themeSettings.value);
                                });
                            }

                            window.dispatchEvent(new CustomEvent('theme-animation-settings-changed', {
                                detail: { settings: themeSettings.value }
                            }));
                        }

                        // Apply custom CSS updates natively
                        if (event.data.custom_css !== undefined) {
                            customCss.value = event.data.custom_css;
                            applyCustomCss();
                        }
                    }
                };
                window.addEventListener('message', themeUpdateListener);
            }

        } catch (err: unknown) {
            const errorObj = err as Error;
            logger.warning('Failed to load active theme:', err);
            error.value = errorObj.message || 'Failed to load theme';
            activeTheme.value = null;
            themeSettings.value = {};
            } finally {
                loading.value = false;
                isLoading.value = false;
                activeLoadPromise = null;
            }
        })();

        return activeLoadPromise;
    };

    /**
     * Get theme setting with fallback
     */
    const getSetting = (key: string, defaultValue: unknown = null) => {
        if (!activeTheme.value) {
            return defaultValue;
        }

        if (themeSettings.value && themeSettings.value[key] !== undefined) {
            return themeSettings.value[key];
        }

        const manifest = activeTheme.value.manifest;
        if (manifest && manifest.settings_schema && manifest.settings_schema[key]) {
            return manifest.settings_schema[key].default ?? defaultValue;
        }

        return defaultValue;
    };

    /**
     * Helper: Convert Hex to HSL (Space separated for Tailwind)
     */
    const hexToHsl = (hex: string): string | null => {
        if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return null;

        let r = 0, g = 0, b = 0;
        if (hex.length === 4) {
            r = parseInt('0x' + hex[1] + hex[1]);
            g = parseInt('0x' + hex[2] + hex[2]);
            b = parseInt('0x' + hex[3] + hex[3]);
        } else if (hex.length === 7) {
            r = parseInt('0x' + hex[1] + hex[2]);
            g = parseInt('0x' + hex[3] + hex[4]);
            b = parseInt('0x' + hex[5] + hex[6]);
        }

        r /= 255;
        g /= 255;
        b /= 255;

        const cmin = Math.min(r, g, b), cmax = Math.max(r, g, b), delta = cmax - cmin;
        let h: number, s: number, l: number;

        if (delta === 0) h = 0;
        else if (cmax === r) h = ((g - b) / delta) % 6;
        else if (cmax === g) h = (b - r) / delta + 2;
        else h = (r - g) / delta + 4;

        h = Math.round(h * 60);
        if (h < 0) h += 360;

        l = (cmax + cmin) / 2;
        s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));

        s = parseFloat((s * 100).toFixed(1));
        l = parseFloat((l * 100).toFixed(1));

        return `${h} ${s}% ${l}%`;
    };

    /**
     * Apply theme styles (CSS variables)
     */
    const applyThemeStyles = () => {
        if (!activeTheme.value) return;

        const variables: string[] = [];
        const manifest = activeTheme.value.manifest;

        if (manifest && manifest.settings_schema) {
            Object.keys(manifest.settings_schema).forEach(key => {
                const setting = manifest.settings_schema![key];
                if (!setting) return;
                const value = getSetting(key, setting.default);

                if (value === undefined || value === null) return;

                const cssKey = '--theme-' + key.replace(/_/g, '-');

                if (setting.type === 'color') {
                    const colorValue = value as string;
                    variables.push(`${cssKey}: ${colorValue};`);

                    // Inject HSL version for Shadcn compatibility
                    const hslValue = hexToHsl(colorValue);
                    if (hslValue) {
                        variables.push(`${cssKey}-hsl: ${hslValue};`);
                    }
                } else if (setting.type === 'font' || setting.type === 'typography') {
                    // Inject font-family
                    const fontValue = String(value).includes(' ') ? `"${value}"` : value;
                    variables.push(`${cssKey}: ${fontValue};`);
                }
            });
        }

        if (variables.length > 0) {
            const newCss = `:root {\n  ${variables.join('\n  ')}\n}`;
            
            // STABILITY GUARD: Only update ref and DOM if content changed
            if (cssVariables.value !== newCss) {
                cssVariables.value = newCss;
                injectCssString(cssVariables.value, 'theme-variables');
            }
        }
    };

    /**
     * Consolidated Janari Style Sync
     * Handles Accents, Backgrounds, and Surfaces in one atomic update.
     */
    const syncJanariStyles = (settings: Record<string, unknown>) => {
        // 1. RESOLVE ACCENTS
        const preset = String(settings.color_preset || 'custom');
        let lAcc: string;
        let dAcc: string;

        if (preset !== 'custom' && JANARI_PRESETS[preset as JanariPresetKey]) {
            const p = JANARI_PRESETS[preset as JanariPresetKey];
            lAcc = p.hslLight;
            dAcc = p.hslDark;
        } else {
            const hex = String(settings.color_primary || '#000000');
            const hsl = hexToHsl(hex) || '0 0% 0%';
            lAcc = hsl;
            dAcc = hsl.replace(/\d+%/g, (m, i) => i === 2 ? '100%' : m); // Boost brightness for dark mode custom
        }

        // 2. RESOLVE BACKGROUNDS
        const lightBg = String(settings.bg_light_color || 'white');
        const lightV = String(settings.bg_light_variant || 'clean');
        const darkBg = String(settings.bg_dark_color || 'black');
        const darkV = String(settings.bg_dark_variant || 'clean');

        const lMap: Record<string, Record<string, string>> = {
            white: { clean: '0 0% 100%', soft: '0 0% 96%', matte: '0 0% 92%', warm: '30 15% 96%' },
            cream: { clean: '42 33% 96%', soft: '40 28% 92%', matte: '38 22% 88%', warm: '45 38% 94%' },
            cool_gray: { clean: '210 12% 97%', soft: '215 10% 93%', matte: '220 8% 89%', warm: '205 14% 95%' },
            warm_gray: { clean: '30 6% 96%', soft: '25 8% 92%', matte: '20 5% 88%', warm: '35 10% 94%' },
            mint: { clean: '152 18% 96%', soft: '155 14% 92%', matte: '148 10% 88%', warm: '160 20% 94%' },
            lavender: { clean: '262 18% 97%', soft: '265 14% 93%', matte: '258 10% 89%', warm: '270 20% 95%' },
            blush: { clean: '350 18% 97%', soft: '345 14% 93%', matte: '355 10% 89%', warm: '348 22% 95%' },
            sky: { clean: '200 20% 97%', soft: '205 16% 93%', matte: '198 12% 89%', warm: '195 22% 95%' },
            sand: { clean: '35 25% 95%', soft: '32 20% 91%', matte: '38 16% 87%', warm: '40 30% 93%' }
        };

        const dMap: Record<string, Record<string, string>> = {
            black: { clean: '0 0% 0%', soft: '0 0% 8%', matte: '0 0% 15%', deep: '0 0% 2%' },
            charcoal: { clean: '0 0% 12%', soft: '0 0% 18%', matte: '210 3% 25%', deep: '220 5% 8%' },
            navy: { clean: '222 25% 8%', soft: '220 20% 15%', matte: '215 15% 22%', deep: '225 30% 5%' },
            slate: { clean: '215 16% 14%', soft: '220 12% 22%', matte: '210 10% 28%', deep: '220 20% 10%' },
            forest: { clean: '150 20% 8%', soft: '155 15% 15%', matte: '145 12% 22%', deep: '160 25% 5%' },
            wine: { clean: '350 25% 8%', soft: '345 20% 15%', matte: '355 15% 22%', deep: '350 30% 5%' },
            midnight: { clean: '230 30% 6%', soft: '235 25% 12%', matte: '225 20% 18%', deep: '240 35% 4%' },
            plum: { clean: '280 22% 8%', soft: '275 18% 15%', matte: '285 14% 22%', deep: '290 28% 5%' },
            earth: { clean: '25 20% 8%', soft: '30 16% 15%', matte: '20 12% 22%', deep: '28 25% 5%' }
        };

        const getHsl = (map: Record<string, Record<string, string>>, c: string, v: string, f: string) => {
            const colors = map[c] || map[Object.keys(map)[0] || 'white'];
            if (!colors) return f;
            return colors[v] || colors['clean'] || f;
        };

        const lHsl = getHsl(lMap, lightBg, lightV, '0 0% 100%');
        const dHsl = getHsl(dMap, darkBg, darkV, '0 0% 0%');

        const parseHsl = (str: string) => {
            const p = str.match(/(\d+)\s+(\d+)%\s+(\d+)%/);
            if (!p || p.length < 4) return { h: 0, s: 0, l: 0 };
            return { h: parseInt(p[1] || '0'), s: parseInt(p[2] || '0'), l: parseInt(p[3] || '0') };
        };

        const l = parseHsl(lHsl);
        const d = parseHsl(dHsl);

        // 3. GENERATE CONSOLIDATED CSS
        const css = `
            .theme-janari {
                --janari-accent-hsl-inline: ${lAcc};
                --janari-accent-hsl-inline-dark: ${dAcc};
                --background: ${lHsl};
                --card: ${l.h} ${l.s}% ${Math.min(l.l + 2, 100)}%;
                --popover: ${l.h} ${l.s}% ${Math.min(l.l + 1, 100)}%;
                --muted: ${l.h} ${Math.max(l.s - 5, 0)}% ${Math.max(l.l - 5, 0)}%;
                --border: ${l.h} ${l.s}% ${Math.max(l.l - 8, 0)}%;
            }
            .dark .theme-janari {
                --background: ${dHsl};
                --card: ${d.h} ${d.s}% ${Math.min(d.l + 6, 100)}%;
                --popover: ${d.h} ${d.s}% ${Math.min(d.l + 4, 100)}%;
                --muted: ${d.h} ${d.s}% ${Math.min(d.l + 12, 100)}%;
                --border: ${d.h} ${d.s}% ${Math.min(d.l + 18, 100)}%;
            }
        `;

        injectCssString(css, 'janari-dynamic-theme');
    };
    /**
     * Apply custom CSS
     */
    const applyCustomCss = () => {
        if (customCss.value) {
            injectCssString(customCss.value, 'theme-custom-css');
        }
    };

    /**
     * Inject CSS files into document
     */
    const injectCssFiles = (cssFiles: string[]) => {
        if (!document.head) return;
        cssFiles.forEach((cssFile, index) => {
            const linkId = `theme-css-${index}`;
            const existing = document.getElementById(linkId);
            if (existing) existing.remove();

            const link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            link.href = cssFile.startsWith('http') || cssFile.startsWith('/') ? cssFile : `/${cssFile}`;
            document.head.appendChild(link);
        });
    };

    /**
     * Inject JS files into document
     */
    const injectJsFiles = (jsFiles: string[]) => {
        if (!document.head) return;
        jsFiles.forEach((jsFile, index) => {
            const scriptId = `theme-js-${index}`;
            const existing = document.getElementById(scriptId);
            if (existing) existing.remove();

            const script = document.createElement('script');
            script.id = scriptId;
            script.src = jsFile.startsWith('http') || jsFile.startsWith('/') ? jsFile : `/${jsFile}`;
            script.defer = true;
            document.head.appendChild(script);
        });
    };

    /**
     * Inject CSS string into document
     */
    const injectCssString = (css: string, id: string) => {
        if (!document.head || !css) return;
        
        const existing = document.getElementById(id) as HTMLStyleElement;
        
        // STABILITY GUARD: Only update if content is actually different
        // This prevents infinite reactivity loops when this is called from computed/watchers
        if (existing && existing.textContent === css) {
            return;
        }

        if (existing) {
            existing.textContent = css;
        } else {
            const style = document.createElement('style');
            style.id = id;
            style.textContent = css;
            document.head.appendChild(style);
        }
    };

    const isThemeLoaded = computed(() => activeTheme.value !== null);
    const themeName = computed(() => activeTheme.value?.name || 'Default');
    const themeType = computed(() => activeTheme.value?.type || 'frontend');

    return {
        activeTheme,
        themeSettings,
        themeAssets,
        customCss,
        cssVariables,
        loading,
        error,
        isThemeLoaded,
        themeName,
        themeType,
        loadActiveTheme,
        getSetting,
        applyThemeStyles,
    };
}
