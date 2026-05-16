import { useAuthStore } from '@/modules/System/stores/auth';
import { useWorkspaceStore } from '../stores/workspace';
import { registry } from '../registry';
import { logger } from '@/shared/utils/logger';

/**
 * CORE BOOTSTRAP
 * The single entrypoint for application initialization.
 */
export async function bootstrapApp() {
    logger.info('[Kernel] Initializing System Kernel...');

    const authStore = useAuthStore();
    const workspaceStore = useWorkspaceStore();

    // 1. Initialize Global Stores from Storage
    workspaceStore.initWorkspace();

    // 2. Load User Profile if authenticated (check session)
    try {
        await authStore.fetchUser();
    } catch (e) {
        logger.warning('[Kernel] No active session found or profile fetch failed.');
    }
    
    // 3. Load Modules
    try {
        const { modules } = await import('@/modules/index');
        modules.forEach(m => registry.register(m));
        await registry.initializeAll();
    } catch (error) {
        logger.error('[Kernel] Failed to register modules during bootstrap', error);
    }

    // 3. Finalize Initialization
    logger.info('[Kernel] System Kernel ready.', {
        context: workspaceStore.activeContextType,
        isAuthenticated: authStore.isAuthenticated
    });

    return { authStore, workspaceStore, registry };
}
