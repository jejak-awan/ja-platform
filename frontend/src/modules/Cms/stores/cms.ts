import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import type { CMSState, Content, SiteSettings } from '@/modules/Cms/types/cms';

const PUBLIC_SETTINGS_CACHE_KEY = 'public_settings_snapshot_v1';

const defaultSiteSettings = (): SiteSettings => ({
    site_name: 'JA-Platform',
    site_description: 'Jejakawan',
    site_url: '',
    admin_email: '',
    site_version: 'v1.0',
    site_logo: '',
    site_favicon: '/favicon.svg'
});

const readCachedPublicSettings = (): SiteSettings | null => {
    if (typeof window === 'undefined') return null;
    try {
        const raw = window.sessionStorage.getItem(PUBLIC_SETTINGS_CACHE_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) return null;
        return { ...defaultSiteSettings(), ...(parsed as Record<string, unknown>) };
    } catch {
        return null;
    }
};

const writeCachedPublicSettings = (settings: SiteSettings): void => {
    if (typeof window === 'undefined') return;
    try {
        window.sessionStorage.setItem(PUBLIC_SETTINGS_CACHE_KEY, JSON.stringify(settings));
    } catch {
        // ignore storage quota / privacy mode errors
    }
};

export const useCmsStore = defineStore('cms', {
    state: (): CMSState => ({
        contents: [],
        categories: [],
        tags: [],
        media: [],
        settings: {}, // Store settings by group or flat key-value
        siteSettings: readCachedPublicSettings() ?? defaultSiteSettings(),
        currentContent: null,
        loading: false,
        loadingGroups: {}, // To track loading state for specific settings groups
        settingsPromises: {}, // To store promises for ongoing settings group fetches
        publicSettingsPromise: null, // Promise for public settings fetch
        publicSettingsLoaded: readCachedPublicSettings() !== null, // Flag to track if public settings were fetched
        themeMode: 'system', // 'light', 'dark', 'system'
        isDarkMode: false,
    }),

    actions: {
        isAuthenticatedLocally(): boolean {
            const userRaw = localStorage.getItem('user');
            if (!userRaw) return false;

            try {
                const parsed = JSON.parse(userRaw);
                return !!parsed && typeof parsed === 'object';
            } catch {
                return false;
            }
        },

        async fetchSettingsGroup(group: string) {
            // If already loading, return existing promise
            if (this.loadingGroups[group]) {
                return this.settingsPromises[group];
            }

            // Mark this group as loading
            this.loadingGroups = { ...this.loadingGroups, [group]: true };

            // Create and store the promise for this fetch operation
            const promise = (async () => {
                try {
                    const response = await api.get(`/admin/cms/settings/group/${group}`);
                    const settingsData = response.data || {};
                    
                    // Only update if there are actual new keys or changed values to prevent reactive thrashing
                    const hasChanges = Object.entries(settingsData).some(([key, value]) => this.settings[key] !== value);
                    if (hasChanges) {
                        this.settings = { ...this.settings, ...settingsData };
                    }
                    return settingsData;
                }
                catch (error: unknown) {
                    logger.error(`Error fetching ${group} settings:`, error);
                    return {};
                } finally {
                    this.loadingGroups = { ...this.loadingGroups, [group]: false };
                    delete this.settingsPromises[group];
                }
            })();

            this.settingsPromises = { ...this.settingsPromises, [group]: promise };
            return promise;
        },

        async fetchPublicSettings(options: { force?: boolean } = {}) {
            // If already loading, return existing promise
            if (this.publicSettingsPromise && !options.force) {
                return this.publicSettingsPromise;
            }

            // Create and store the promise for this fetch operation
            this.publicSettingsPromise = (async () => {
                try {
                    const response = await api.get('/public/settings');
                    const settingsData = response.data || {};
                    
                    // Stability: Check for actual differences before triggering reactivity
                    const currentSettings = this.siteSettings as Record<string, unknown>;
                    const incomingSettings = settingsData as Record<string, unknown>;
                    const hasChanges = Object.entries(incomingSettings).some(([key, value]) => currentSettings[key] !== value);

                    if (hasChanges) {
                        this.siteSettings = { ...this.siteSettings, ...settingsData };
                    }
                    writeCachedPublicSettings(this.siteSettings);
                    
                    return this.siteSettings;
                } catch (error: unknown) {
                    logger.error('Error fetching public settings:', error);
                    return this.siteSettings;
                } finally {
                    this.publicSettingsLoaded = true; // Always mark as loaded to prevent loops
                    this.publicSettingsPromise = null;
                }
            })();

            return this.publicSettingsPromise;
        },

        async fetchContents(params: Record<string, unknown> = {}) {
            this.loading = true;
            try {
                const response = await api.get('/ja/contents', { params });
                const { data } = parseResponse(response);
                this.contents = ensureArray(data);
                return { data: this.contents };
            } catch (error: unknown) {
                logger.error('Error fetching contents:', error);
                this.contents = [];
                return { data: [] };
            } finally {
                this.loading = false;
            }
        },

        async fetchContent(slug: string): Promise<Content | null> {
            this.loading = true;
            try {
                const response = await api.get(`/ja/contents/${slug}`);
                this.currentContent = response.data;
                return response.data;
            } catch (error: unknown) {
                logger.error('Error fetching content:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const response = await api.get('/ja/categories');
                const { data } = parseResponse(response);
                this.categories = ensureArray(data);
                return this.categories;
            } catch (error: unknown) {
                logger.error('Error fetching categories:', error);
                this.categories = [];
                return [];
            }
        },

        async fetchTags() {
            try {
                const response = await api.get('/ja/tags');
                const { data } = parseResponse(response);
                this.tags = ensureArray(data);
                return this.tags;
            } catch (error: unknown) {
                logger.error('Error fetching tags:', error);
                this.tags = [];
                return [];
            }
        },

        async initTheme() {
            const THEME_KEY = 'admin-dark-mode';

            // 1. Detect system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // 2. Load from localStorage or fallback to system
            const saved = localStorage.getItem(THEME_KEY) || 'system';
            this.themeMode = saved as 'light' | 'dark' | 'system';

            // 3. Resolve actual dark mode state
            this.isDarkMode = saved === 'dark' || (saved === 'system' && prefersDark);

            // 4. Apply to document
            this.applyThemeToDocument();

            // 5. Watch for system changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.themeMode === 'system') {
                    this.isDarkMode = e.matches;
                    this.applyThemeToDocument();
                }
            });

            // 6. Try to load from backend (if authenticated)
            await this.loadThemePreferences();
        },

        async loadThemePreferences() {
            if (!this.isAuthenticatedLocally()) return;
            try {
                const response = await api.get('/profile/preferences');
                const backendMode = response.data?.dark_mode;
                const isValidThemeMode = backendMode === 'light' || backendMode === 'dark' || backendMode === 'system';
                if (isValidThemeMode) {
                    if (this.themeMode !== backendMode) {
                        this.setThemeMode(backendMode, false); // Don't sync back to backend
                    }
                }
            } catch (error: unknown) {
                const message = error instanceof Error ? error.message : String(error);
                logger.debug('Failed to load theme preferences:', { message });
            }
        },

        async syncThemeWithBackend(mode: string) {
            if (!this.isAuthenticatedLocally()) return;
            try {
                await api.put('/profile/preferences', { dark_mode: mode });
            } catch (error: unknown) {
                const message = error instanceof Error ? error.message : String(error);
                logger.debug('Theme sync failed:', { message });
            }
        },

        setThemeMode(mode: 'light' | 'dark' | 'system', syncToBackend = true) {
            const THEME_KEY = 'admin-dark-mode';

            // Add no-transitions class to prevent flashing
            document.documentElement.classList.add('no-transitions');

            this.themeMode = mode;
            localStorage.setItem(THEME_KEY, mode);

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.isDarkMode = mode === 'dark' || (mode === 'system' && prefersDark);

            this.applyThemeToDocument();

            if (syncToBackend) {
                this.syncThemeWithBackend(mode);
            }

            // Remove no-transitions class after short delay
            setTimeout(() => {
                document.documentElement.classList.remove('no-transitions');
            }, 50);
        },

        toggleDarkMode(value?: boolean) {
            // If value is provided (e.g. from a switch), use it. 
            // Otherwise, toggle current state.
            const isDark = value !== undefined ? value : !this.isDarkMode;
            const next = isDark ? 'dark' : 'light';
            this.setThemeMode(next);
        },

        applyThemeToDocument() {
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
    },
});
