/**
 * Standardized Configuration for JA-Apps Frontend
 * Eliminates hardcoded domains by prioritizing Environment Variables (Vite)
 */

export const config = {
    // Domains
    appDomain: import.meta.env.VITE_ROOT_DOMAIN || 'smkn1cijulang.sch.id',
    
    // URLs
    appUrl: import.meta.env.VITE_PORTAL_URL || `https://${import.meta.env.VITE_ROOT_DOMAIN || 'smkn1cijulang.sch.id'}`,
    
    // API Defaults
    apiBaseUrl: '/api/v1',
    storageBaseUrl: '/storage',
};

export default config;
