import axios, { 
    type AxiosInstance, 
    type AxiosResponse, 
    type AxiosError, 
    type InternalAxiosRequestConfig,
    type AxiosRequestConfig
} from 'axios';

export interface ApiRequestConfig extends AxiosRequestConfig {
    _skipManualRedirect?: boolean;
}
import { logger } from '@/shared/utils/logger';

// --- SECURITY & STATE FLAGS ---
let isRedirectingToLogin = false;
let abortController = new AbortController();

declare global {
    interface Window {
        __isSessionTerminated?: boolean;
    }
}

window.__isSessionTerminated = false;

// --- PERFORMANCE MONITORING ---
interface ApiPerfEntry {
    url: string;
    method: string;
    status: number;
    durationMs: number;
    at: number;
}

const apiPerfBuffer: ApiPerfEntry[] = [];
const MAX_API_PERF_BUFFER = 250;

const pushApiPerfEntry = (entry: ApiPerfEntry): void => {
    apiPerfBuffer.push(entry);
    if (apiPerfBuffer.length > MAX_API_PERF_BUFFER) {
        apiPerfBuffer.splice(0, apiPerfBuffer.length - MAX_API_PERF_BUFFER);
    }
};

export const consumeApiPerfEntries = (): ApiPerfEntry[] => {
    if (apiPerfBuffer.length === 0) return [];
    const out = [...apiPerfBuffer];
    apiPerfBuffer.length = 0;
    return out;
};

// --- API CLIENT CONFIG ---
const apiClient: AxiosInstance = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

/**
 * Ensure CSRF cookie is set (call this before first authenticated request)
 */
export const getCsrfCookie = async (): Promise<void> => {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    } catch (error) {
        logger.error('Failed to get CSRF cookie:', error);
    }
};

// --- UTILITIES ---
export const triggerVaporLock = (): void => {
    if (window.__isSessionTerminated) return;
    window.__isSessionTerminated = true;
    abortController.abort('Vapor Lock: Session Terminated');
    logger.warning('[Security] Vapor Lock triggered. All requests cancelled.');
};

export const resetLockdown = (): void => {
    window.__isSessionTerminated = false;
    abortController = new AbortController();
    logger.info('[Security] Lockdown reset.');
};

// --- INTERCEPTORS ---

// Request: Auth scoping & Vapor Lock
apiClient.interceptors.request.use((config: InternalAxiosRequestConfig & { _perfStartedAt?: number }) => {
    config._perfStartedAt = performance.now();
    const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'user'];
    const isAuthRequest = authEndpoints.some(endpoint => config.url?.includes(endpoint));

    // Vapor Lock check
    if (window.__isSessionTerminated && !isAuthRequest) {
        return Promise.reject(new axios.Cancel('Vapor Lock: Request Blocked'));
    }

    if (!isAuthRequest) {
        config.signal = abortController.signal;
    }

    // Inject Workspace Context
    const activeUnitId = sessionStorage.getItem('active_level_id');
    if (activeUnitId) {
        config.headers['X-Level-ID'] = activeUnitId;
    }

    return config;
});

// Response: Global Error Handling & Data Unpacking
apiClient.interceptors.response.use(
    (response: AxiosResponse) => {
        const config = response.config as InternalAxiosRequestConfig & { _perfStartedAt?: number };
        const startedAt = config._perfStartedAt || performance.now();
        
        pushApiPerfEntry({
            url: config.url || 'unknown',
            method: (config.method || 'get').toUpperCase(),
            status: response.status,
            durationMs: Math.max(0, performance.now() - startedAt),
            at: Date.now(),
        });

        // Standardized Data Unpacking
        const responseData = response.data;
        if (responseData && typeof responseData === 'object' && 'success' in responseData) {
            if (responseData.success && responseData.data !== undefined) {
                response.data = responseData.data;
            }
        }
        return response;
    },
    async (error: AxiosError) => {
        const config = error.config as (InternalAxiosRequestConfig & { _perfStartedAt?: number }) | undefined;
        if (config) {
            const startedAt = config._perfStartedAt || performance.now();
            pushApiPerfEntry({
                url: config.url || 'unknown',
                method: (config.method || 'get').toUpperCase(),
                status: error.response?.status ?? 0,
                durationMs: Math.max(0, performance.now() - startedAt),
                at: Date.now(),
            });
        }
        const status = error.response?.status;
        const currentPath = window.location.pathname;

        // 1. Session Expiry (401/419)
        if (status === 401 || status === 419) {
            if (isRedirectingToLogin) return Promise.reject(error);
            
            const url = error.config?.url || '';
            if (url.includes('logout')) return Promise.resolve({ data: {} });

            triggerVaporLock();
            isRedirectingToLogin = true;

            logger.error(`[Auth] Session expired (${status}). Redirecting to login.`);
            
            // Cleanup local storage to prevent loops
            localStorage.removeItem('user');
            
            window.location.href = `/auth/portal-sign-in?redirect=${encodeURIComponent(currentPath)}`;
            return new Promise(() => {}); // Stop execution
        }

        // 2. Shield Challenge (429 Rate Limit)
        if (status === 429) {
            const shieldNonce = error.response?.headers?.['x-shield-challenge'];
            if (shieldNonce) {
                logger.info('[Security] Bot shield challenge detected. Handling...');
                // Logic for solving challenge would go here (importing security store)
            }
        }

        // 3. Maintenance (503)
        if (status === 503) {
            window.location.href = '/maintenance';
        }

        return Promise.reject(error);
    }
);

export default apiClient;
