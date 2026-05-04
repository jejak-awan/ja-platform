import { logger } from '@/utils/logger';
import { appConfig } from '@/config';
import axios, { type AxiosInstance, type InternalAxiosRequestConfig, type AxiosError, type AxiosResponse, type AxiosRequestConfig } from 'axios';
import { SystemMonitor } from './SystemMonitor';
import { type ZodSchema } from 'zod';
import { SECURITY_ROUTES, isProtectedDashboardPath } from '@/config/security';
import { useSystemError } from '@/composables/useSystemError';
// router import removed to break circular dependency



// Circuit breaker to prevent redundant redirects during auth loops
let isRedirectingToLogin = false;
let isHandlingCriticalError = false;

const hasPersistedUser = (): boolean => {
    const userRaw = localStorage.getItem('user');
    if (!userRaw) return false;

    try {
        const parsed = JSON.parse(userRaw);
        return !!parsed && typeof parsed === 'object';
    } catch {
        return false;
    }
};

declare global {
    interface Window {
        __isSessionTerminated?: boolean;
        __is403Blocked?: boolean;
    }
}

// Global flag to prevent multiple toasts and parallel redirect attempts
window.__isSessionTerminated = false;

// Vapor Lock: Global controller to cancel all pending requests instantly
let abortController = new AbortController();

// Add custom property to config for skipping checks or adding schemas
export interface CustomAxiosRequestConfig extends InternalAxiosRequestConfig {
    _skipManualRedirect?: boolean;
    _schema?: ZodSchema;
    _perfStartedAt?: number;
}

export interface ApiRequestConfig extends AxiosRequestConfig {
    _skipManualRedirect?: boolean;
    _schema?: ZodSchema;
}

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

const api: AxiosInstance = axios.create({
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
 * RESET VAPOR LOCK: Recovers the ability to make requests (used after login/refresh)
 */
export const resetLockdown = (): void => {
    window.__isSessionTerminated = false;
    abortController = new AbortController();
};

/**
 * TRIGGER VAPOR LOCK: Instantly kills all outgoing and pending requests
 */
export const triggerVaporLock = (): void => {
    if (window.__isSessionTerminated) return;
    window.__isSessionTerminated = true;
    abortController.abort('Vapor Lock: Session Terminated');
};

// Ensure CSRF cookie is set (call this before first authenticated request)
export const getCsrfCookie = async (): Promise<void> => {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    } catch (error) {
        logger.error('Failed to get CSRF cookie:', error);
    }
};

// Request Interceptor
api.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
        (config as CustomAxiosRequestConfig)._perfStartedAt = performance.now();
        // BREAK CIRCUIT: If system is down, block all non-critical requests
        if (SystemMonitor.isRequestBlocked && !config.url?.includes('system/health')) {
            logger.debug('Request blocked by Circuit Breaker:', { url: config.url });
            return Promise.reject(new axios.Cancel('System is undergoing maintenance'));
        }

        // Whitelist auth-related endpoints so they are NOT blocked even if session is marked as dead
        const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
        const isAuthRequest = authEndpoints.some(endpoint => config.url?.includes(endpoint));

        // VAPOR LOCK CHECK: Instantly block any non-auth request if session is dead
        if (window.__isSessionTerminated && !isAuthRequest) {
            return Promise.reject(new axios.Cancel('Vapor Lock: Request Blocked'));
        }

        // Attach abort signal to the request (unless it's an auth request we WANT to succeed)
        if (!isAuthRequest) {
            config.signal = abortController.signal;
        }

        // Manual XSRF-TOKEN handling for added robustness
        // This ensures the header is ALWAYS set if the cookie is present,
        // bypassing occasional Axios auto-detection failures.
        const xsrfToken = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        if (xsrfToken) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
        }

        // LEVEL CONTEXT: Inject active level ID for multi-tenancy scoping
        const activeUnitId = localStorage.getItem('active_level_id');
        if (activeUnitId) {
            config.headers['X-Level-ID'] = activeUnitId;
        }

        // SCHOOL CONTEXT: Inject active school ID
        const activeSchoolId = localStorage.getItem('active_school_id');
        if (activeSchoolId) {
            config.headers['X-School-Id'] = activeSchoolId;
        }

        return config;
    },
    (error: unknown) => {
        return Promise.reject(error);
    }
);

// Response interceptor - Handle standardized response format and errors
api.interceptors.response.use(
    (response: AxiosResponse) => {
        const config = response.config as CustomAxiosRequestConfig;
        const startedAt = typeof config._perfStartedAt === 'number' ? config._perfStartedAt : performance.now();
        pushApiPerfEntry({
            url: config.url || 'unknown',
            method: String(config.method || 'get').toUpperCase(),
            status: response.status,
            durationMs: Math.max(0, performance.now() - startedAt),
            at: Date.now(),
        });
        const responseData = response.data;

        // 1. Zod Schema Runtime Validation (Combat "False Security")
        if (config._schema && responseData?.success) {
            try {
                const dataToValidate = responseData.data !== undefined ? responseData.data : responseData;
                const result = config._schema.safeParse(dataToValidate);
                
                if (!result.success) {
                    logger.error('API Runtime Schema Mismatch:', {
                        url: config.url,
                        errors: result.error.format()
                    });
                    
                    if (appConfig.isDev) {
                        console.error(`[TS-Security] Schema mismatch on ${config.url}`, result.error.format());
                    }
                } else {
                    // Replace data with parsed data to ensure type safety and strip extra fields
                    if (responseData.data !== undefined) {
                        responseData.data = result.data;
                    } else {
                        response.data = result.data;
                    }
                }
            } catch (e) {
                logger.error('Zod parsing logic error:', e);
            }
        }

        // 2. Standardized Response Unpacking
        // Definitive fix for reactivity loops: Remove Proxy and unpack data directly.
        if (responseData && typeof responseData === 'object' && 'success' in responseData) {
            const { success, data } = responseData as any;
            if (success && data !== undefined) {
                response.data = data;
            }
        }

        return response;
    },
    async (error: AxiosError<{ message?: string; retry_after?: number }> & { config: CustomAxiosRequestConfig }) => {
        const errorConfig = error.config as CustomAxiosRequestConfig | undefined;
        if (errorConfig) {
            const startedAt = typeof errorConfig._perfStartedAt === 'number' ? errorConfig._perfStartedAt : performance.now();
            pushApiPerfEntry({
                url: errorConfig.url || 'unknown',
                method: String(errorConfig.method || 'get').toUpperCase(),
                status: error.response?.status ?? 0,
                durationMs: Math.max(0, performance.now() - startedAt),
                at: Date.now(),
            });
        }
        const originalRequest = error.config;

        // Skip global redirect if requested by the caller
        if (originalRequest?._skipManualRedirect) {
            return Promise.reject(error);
        }

        // HANDLE 503 SERVICE UNAVAILABLE (Maintenance Mode / Deployment)
        if (error.response?.status === 503) {
            logger.warning('Server returned 503 - Service Unavailable');
            return Promise.reject(error);
        }

        // Handle 419 CSRF token mismatch / Session Expired
        if (error.response?.status === 419) {
            const url = error.config?.url || '';
            const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
            const isAuthRequest = authEndpoints.some(endpoint => url.includes(endpoint));
            const currentPath = window.location.pathname;

            // If it's a 419 on a whitelisted endpoint, don't trigger global lockdown
            if (isAuthRequest) {
                if (currentPath.includes(SECURITY_ROUTES.login) || currentPath.includes('/login')) {
                    getCsrfCookie(); // Attempt to self-heal CSRF
                }

                // If LOGOUT fails with 419, it's fine (session is already gone), silence it
                // We RESOLVE here to bypass the component's catch block entirely
                if (url.includes('logout')) {
                    return Promise.resolve({ data: { message: 'Logout Silenced: Session already dead' } } as AxiosResponse);
                }

                return Promise.reject(error);
            }

            triggerVaporLock();

            if (!currentPath.includes('/419') && !currentPath.includes(SECURITY_ROUTES.login) && !currentPath.includes('/login')) {
                // FORCE CLEAR: Wipe all auth-related storage to break the loop
                localStorage.removeItem('user');
                localStorage.removeItem('permissions');
                localStorage.removeItem('roles');
                
                // Set circuit breaker flag
                (window as any).__isSessionTerminated = true;

                if (isRedirectingToLogin) {
                    return Promise.reject(new axios.Cancel('Vapor Lock: 419 Silenced (Duplicate Redirect)'));
                }

                isRedirectingToLogin = true;

                const hasSessionToken = hasPersistedUser();
                const isProtectedProbePath = isProtectedDashboardPath(currentPath);
                const target = (!hasSessionToken && isProtectedProbePath)
                    ? SECURITY_ROUTES.notFound
                    : `${SECURITY_ROUTES.login}?redirect=${encodeURIComponent(currentPath)}`;
                window.location.href = target;
            }

            // Return silent rejection to avoid component catch blocks logging "419" after lockdown
            return Promise.reject(new axios.Cancel('Vapor Lock: 419 Silenced'));
        }

        // Handle 401 Unauthorized (session expired from Sanctum)
        if (error.response?.status === 401) {
            const url = error.config?.url || '';
            const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'captcha/', 'user'];
            const isAuthRequest = authEndpoints.some(endpoint => url.includes(endpoint));

            // Whitelist check
            if (isAuthRequest) {
                // If LOGOUT fails with 401, it's fine (session is already gone), silence it
                // We RESOLVE here to bypass the component's catch block entirely
                if (url.includes('logout')) {
                    return Promise.resolve({ data: { message: 'Logout Silenced: Already unauthorized' } } as AxiosResponse);
                }
                return Promise.reject(error);
            }

            triggerVaporLock();

            // Public endpoints are frontend routes that don't require session termination on 401
            const publicEndpoints = ['/api/v1/ja/', '/api/v1/ja/contents', '/api/v1/ja/categories', '/api/v1/ja/tags', '/api/v1/ja/themes/active', '/api/v1/ja/search', '/api/v1/ja/languages'];
            const isPublicEndpoint = publicEndpoints.some(endpoint => url.startsWith(endpoint));
            const hasAuthHeader = !!error.config?.headers?.Authorization;

            if (hasAuthHeader || !isPublicEndpoint) {
                const currentPath = window.location.pathname;
                if (!currentPath.includes(SECURITY_ROUTES.login) && !currentPath.includes('/419')) {
                    // FORCE CLEAR: Wipe all auth-related storage to break the loop
                    localStorage.removeItem('user');
                    localStorage.removeItem('permissions');
                    localStorage.removeItem('roles');
                    
                    // Set circuit breaker flag
                    (window as any).__isSessionTerminated = true;

                    if (isRedirectingToLogin) {
                        return Promise.reject(new axios.Cancel('Vapor Lock: 401 Silenced (Duplicate Redirect)'));
                    }

                    isRedirectingToLogin = true;

                    const hasSessionToken = hasPersistedUser();
                    const isProtectedProbePath = isProtectedDashboardPath(currentPath);
                    const target = (!hasSessionToken && isProtectedProbePath)
                        ? SECURITY_ROUTES.notFound
                        : `${SECURITY_ROUTES.login}?redirect=${encodeURIComponent(currentPath !== '/' ? currentPath : SECURITY_ROUTES.dashboardBase)}`;
                    window.location.href = target;
                }
            }

            // Return silent rejection
            return Promise.reject(new axios.Cancel('Vapor Lock: 401 Silenced'));
        }

        // Handle 403 Forbidden - Redirect to maintenance if it looks like a global block
        if (error.response?.status === 403) {
            const url = error.config?.url || '';
            const isAdminEndpoint = url.includes('/admin/');
            const isSessionTerminated = !!window.__isSessionTerminated;

            // Expected during standby/session lockdown on protected admin APIs.
            // Keep behavior blocked, but silence noisy console/error handling chains.
            if (isSessionTerminated && isAdminEndpoint) {
                return Promise.reject(new axios.Cancel('Vapor Lock: Admin 403 Silenced'));
            }
            
            // 403/503 Forbidden/Maintenance handling
            // We NO LONGER push to the router inside the interceptor for critical initialization APIs.
            // This prevents the infinite recursion loop between the Guard and the Interceptor.
            if (url.includes('public/settings') || url.includes('system/health') || url.endsWith('/user') || url.includes('captcha/')) {
                logger.debug('Critical API blocked (403/503):', { url });
                return Promise.reject(error);
            }

            logger.debug('403 Forbidden:', { url: error.config?.url });
        }

        // Handle 429 Rate Limit - check for Bot Shield challenge
        if (error.response?.status === 429) {
            const responseData = error.response.data as { challenge?: { nonce?: string; difficulty?: string } } | undefined;
            const shieldNonce = error.response.headers?.['x-shield-challenge'] || responseData?.challenge?.nonce;
            const shieldDifficulty = parseInt(error.response.headers?.['x-shield-difficulty'] || responseData?.challenge?.difficulty || '0', 10);

            if (shieldNonce && shieldDifficulty > 0) {
                try {
                    // Lazy store access to prevent initialization cycles
                    const { useSecurityStore } = await import('@/modules/Core/stores/security');
                    const securityStore = useSecurityStore();
                    const solution = await securityStore.solveChallenge(shieldNonce, shieldDifficulty);

                    if (solution !== null && error.config) {
                        // Resubmit original request - it will now include the new cookies set by verification
                        return api(error.config);
                    }
                } catch (solveError) {
                    logger.error('Shield challenge solve failed:', solveError);
                }
            }

            if (error.response.data && !error.response.data.retry_after) {
                const retryAfter = error.response.headers?.['retry-after'] ||
                    error.response.headers?.['Retry-After'] ||
                    '60';
                error.response.data.retry_after = parseInt(String(retryAfter), 10);
            }
        }

        // 1. Session Expiry / CORS
        if (error.response?.status && [401, 419].includes(error.response.status)) {
            if (isHandlingCriticalError) return Promise.reject(error);
            isHandlingCriticalError = true;

            const redirect = window.location.pathname + window.location.search;
            const hasSessionToken = hasPersistedUser();
            const isProtectedProbePath = isProtectedDashboardPath(redirect);
            const target = (!hasSessionToken && isProtectedProbePath)
                ? SECURITY_ROUTES.notFound
                : `${SECURITY_ROUTES.login}?redirect=${encodeURIComponent(redirect)}`;
            
            // ATOMIC RECOVERY: Redirect to login immediately if unauthorized
            // Using window.location.href to hard-reset the JS environment and clear all loops
            window.location.href = target;
            return new Promise(() => {}); // Never resolve to stop further processing
        }

        // 2. Server Error (500)
        if (error.response?.status === 500) {
            if (isHandlingCriticalError) return Promise.reject(error);
            isHandlingCriticalError = true;

            const { showError } = useSystemError();
            showError({
                code: 500,
                title: 'System Error',
                message: 'Internal server error occurred.',
                description: 'We have been notified and are looking into it.',
                reason: error.response?.data?.message || 'Unknown Server Error'
            });
        }

        return Promise.reject(error);
    }
);

export default api;
