const GSAP_ASSET_PATTERNS = ['/assets/gsap-', '/assets/useGsapAnimations-'];
const KILL_SWITCH_STYLE_ID = 'admin-gsap-kill-switch';

const hasGsapAssetLoaded = (): boolean => {
    if (typeof performance === 'undefined' || typeof performance.getEntriesByType !== 'function') {
        return false;
    }

    const resources = performance.getEntriesByType('resource');
    return resources.some((entry) => {
        const name = String((entry as PerformanceResourceTiming).name || '');
        return GSAP_ASSET_PATTERNS.some((pattern) => name.includes(pattern));
    });
};

const enableAnimationKillSwitch = (): void => {
    if (document.getElementById(KILL_SWITCH_STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = KILL_SWITCH_STYLE_ID;
    style.textContent = `
html[data-admin-gsap-blocked="1"] *,
html[data-admin-gsap-blocked="1"] *::before,
html[data-admin-gsap-blocked="1"] *::after {
  animation: none !important;
  transition: none !important;
}
`;
    document.head.appendChild(style);
};

export const enforceAdminNoGsap = (logger: { error: (message: string, data?: unknown) => void }): void => {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    const check = () => {
        const gsapDetected =
            hasGsapAssetLoaded() ||
            typeof (window as unknown as { gsap?: unknown }).gsap !== 'undefined';

        if (!gsapDetected) return;
        if (document.documentElement.dataset.adminGsapBlocked === '1') return;

        document.documentElement.dataset.adminGsapBlocked = '1';
        enableAnimationKillSwitch();
        logger.error('[Admin GSAP Guard] Unexpected GSAP runtime detected in admin bundle', {
            path: window.location.pathname,
            userAgent: navigator.userAgent,
        });
    };

    check();
    window.addEventListener('load', check, { once: true });
    setTimeout(check, 1000);
};
