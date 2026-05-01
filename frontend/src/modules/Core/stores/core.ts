import { logger } from '@/utils/logger';
import { defineStore } from 'pinia';
import api from '@/services/api';

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
    maintenance: {
        mode: boolean;
        title: string;
        message: string;
        countdown_enabled: boolean;
        end_time: string;
    };
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<any>>;
}

// siteSettings moved back to CmsStore as per user request (CMS is public web authority)

export const useCoreStore = defineStore('core', {
    state: (): CoreState => ({
        settings: {},
        maintenance: {
            mode: false,
            title: '',
            message: '',
            countdown_enabled: false,
            end_time: '',
        },
        loadingGroups: {},
        settingsPromises: {},
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
        
        async fetchPublicSettings() {
            try {
                const response = await api.get('/public/settings');
                const data = response.data || {};
                
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
            }
        },

        getSetting(key: string, defaultValue: any = null) {
            return this.settings[key] !== undefined ? this.settings[key] : defaultValue;
        }
    },
});
