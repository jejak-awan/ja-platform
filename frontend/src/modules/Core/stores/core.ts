import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import api from '@/engine/api/client';


export interface SiteSettings {
    site_name: string;
    site_description: string;
    site_url: string;
    admin_email: string;
    site_version: string;
    site_logo: string;
    site_favicon: string;
    [key: string]: any;
}

export interface CoreState {
    settings: Record<string, any>;
    appIdentity: {
        app_name: string;
        app_logo: string;
        app_favicon: string;
        app_license_tier: string;
        has_white_label: boolean;
    };
    siteSettings: SiteSettings;
    maintenance: {
        mode: boolean;
        title: string;
        message: string;
        countdown_enabled: boolean;
        end_time: string;
    };
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<any>>;
    publicSettingsLoaded: boolean;
    publicSettingsPromise: Promise<any> | null;
}

// siteSettings moved back to CmsStore as per user request (CMS is public web authority)

export const useCoreStore = defineStore('core', {
    state: (): CoreState => ({
        settings: {},
        appIdentity: {
            app_name: 'Janari App',
            app_logo: '',
            app_favicon: '',
            app_license_tier: 'basic',
            has_white_label: false,
        },
        siteSettings: {
            site_name: 'JA-Platform',
            site_description: '',
            site_url: '',
            admin_email: '',
            site_version: '',
            site_logo: '',
            site_favicon: '/favicon.svg'
        },
        maintenance: {
            mode: false,
            title: '',
            message: '',
            countdown_enabled: false,
            end_time: '',
        },
        loadingGroups: {},
        settingsPromises: {},
        publicSettingsLoaded: false,
        publicSettingsPromise: null,
    }),

    actions: {
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
                    // Note: Calling Core settings endpoint
                    const response = await api.get(`/admin/core/settings/group/${group}`);
                    const settingsData = response.data || {};
                    
                    // Only update if there are actual new keys or changed values
                    const hasChanges = Object.entries(settingsData).some(([key, value]) => this.settings[key] !== value);
                    if (hasChanges) {
                        this.settings = { ...this.settings, ...settingsData };
                    }
                    return settingsData;
                }
                catch (error: unknown) {
                    logger.error(`[Core Store] Error fetching ${group} settings:`, error);
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

            this.publicSettingsPromise = (async () => {
                try {
                    const response = await api.get('/public/settings');
                    const data = response.data || {};
                    
                    // Sync Site Settings
                    this.siteSettings = {
                        ...this.siteSettings,
                        site_name: data.site_name || this.siteSettings.site_name,
                        site_description: data.site_description || '',
                        site_url: data.site_url || '',
                        admin_email: data.admin_email || '',
                        site_version: data.site_version || '',
                        site_logo: data.site_logo || '',
                        site_favicon: data.site_favicon || '/favicon.ico',
                    };

                    // Sync App Identity (Branding)
                    // We preserve existing values if the new ones are empty to avoid flickering
                    this.appIdentity = {
                        ...this.appIdentity,
                        app_name: data.app_name || data.site_name || this.appIdentity.app_name || 'Janari App',
                        app_logo: data.app_logo || data.site_logo || this.appIdentity.app_logo || '',
                        app_favicon: data.app_favicon || data.site_favicon || this.appIdentity.app_favicon || '',
                        app_license_tier: data.app_license_tier || this.appIdentity.app_license_tier || 'basic',
                        has_white_label: ['pro_plus', 'white_label'].includes(data.app_license_tier || this.appIdentity.app_license_tier),
                    };

                    this.maintenance = {
                        mode: !!data.maintenance_mode,
                        title: data.maintenance_title || '',
                        message: data.maintenance_message || '',
                        countdown_enabled: !!data.maintenance_countdown_enabled,
                        end_time: data.maintenance_end_time || '',
                    };
                    


                    return data;
                } catch (error) {
                    logger.error('[Core Store] Error fetching public settings:', error);
                    return {};
                } finally {
                    this.publicSettingsLoaded = true;
                    this.publicSettingsPromise = null;
                }
            })();

            return this.publicSettingsPromise;
        },

        async fetchAppIdentity() {
            try {
                // Fetch branding from Core settings
                const response = await api.get('/admin/core/settings/group/branding');
                const data = response.data || {};
                
                this.appIdentity = {
                    ...this.appIdentity,
                    app_name: data.app_name || this.appIdentity.app_name || 'Janari App',
                    app_logo: data.app_logo || this.appIdentity.app_logo || '',
                    app_favicon: data.app_favicon || this.appIdentity.app_favicon || '',
                    app_license_tier: data.app_license_tier || this.appIdentity.app_license_tier || 'basic',
                    has_white_label: ['pro_plus', 'white_label'].includes(data.app_license_tier || this.appIdentity.app_license_tier),
                };

                // Inject favicon dynamically
                if (this.appIdentity.app_favicon) {
                    const link = document.querySelector("link[rel~='icon']") as HTMLLinkElement;
                    if (link) link.href = this.appIdentity.app_favicon;
                }
                
                return this.appIdentity;
            } catch (error) {
                logger.error('[Core Store] Error fetching app identity:', error);
                return this.appIdentity;
            }
        },

        getSetting(key: string, defaultValue: any = null) {
            return this.settings[key] !== undefined ? this.settings[key] : defaultValue;
        }
    },
});
