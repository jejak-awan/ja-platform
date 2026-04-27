import { logger } from '@/utils/logger';
import type { RouteLocationNormalized } from 'vue-router';
import api from '@/services/api';
import { SECURITY_ROUTES } from '@/config/security';

let initialNavigationDone = false;
let lastTrackedPath = '';
let lastTrackedAt = 0;

export const trackRouteVisit = (to: RouteLocationNormalized, from: RouteLocationNormalized): void => {
    if (!initialNavigationDone) {
        initialNavigationDone = true;
        return;
    }

    if (to.path.startsWith(SECURITY_ROUTES.dashboardBase) || to.name === 'maintenance' || to.path === '/maintenance') {
        return;
    }

    if (to.path === from.path) {
        return;
    }

    const now = Date.now();
    if (to.path === lastTrackedPath && now - lastTrackedAt < 5000) {
        return;
    }

    lastTrackedPath = to.path;
    lastTrackedAt = now;

    const payload = {
        url: window.location.href,
        path: to.path,
        title: document.title,
    };

    const send = () => {
        api.post('/analytics/track-visit', payload).catch((err) => {
            logger.debug('Analytics tracking failed:', err);
        });
    };

    if (typeof requestIdleCallback === 'function') {
        requestIdleCallback(send, { timeout: 2500 });
    } else {
        requestAnimationFrame(send);
    }
};
