/**
 * App Configuration
 * Centered app metadata and environment flags
 */

export interface AppConfig {
    name: string;
    domain: string;
    url: string;
    version: string;
    isDev: boolean;
}

export const appConfig: AppConfig = {
    name: import.meta.env.VITE_APP_NAME || 'JA-Platform',
    domain: import.meta.env.VITE_ROOT_DOMAIN || 'localhost',
    url: import.meta.env.VITE_PORTAL_URL || `http://${import.meta.env.VITE_ROOT_DOMAIN || 'localhost'}`,
    version: '2.0.0-pro',
    isDev: import.meta.env.DEV,
};

export default appConfig;
