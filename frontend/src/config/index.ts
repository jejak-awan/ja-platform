/**
 * Standardized Hybrid Configuration for JA-Platform
 * Centrally manages environment variables with type safety and defaults.
 */

import { appConfig, type AppConfig } from './app';
import { apiConfig, type ApiConfig } from './api';
import { authConfig, type AuthConfig } from './auth';

export interface RootConfig {
    app: AppConfig;
    api: ApiConfig;
    auth: AuthConfig;
    
    // Legacy support for common fields
    appDomain: string;
    appUrl: string;
    apiBaseUrl: string;
    storageBaseUrl: string;
}

export const config: RootConfig = {
    app: appConfig,
    api: apiConfig,
    auth: authConfig,
    
    // Flattened legacy mappings to prevent breaking existing code
    appDomain: appConfig.domain,
    appUrl: appConfig.url,
    apiBaseUrl: apiConfig.baseUrl,
    storageBaseUrl: '/storage',
};

export default config;
export { appConfig, apiConfig, authConfig };
