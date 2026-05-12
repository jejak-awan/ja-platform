import { defineStore } from 'pinia';

export type ContextType = 'system' | 'unit';

interface WorkspaceState {
    activeContextType: ContextType;
    activeUnitId: number | null;
    activeUnitName: string | null;
}

export const useWorkspaceStore = defineStore('workspace', {
    state: (): WorkspaceState => ({
        activeContextType: 'system',
        activeUnitId: null,
        activeUnitName: null,
    }),

    getters: {
        isSystem: (state) => state.activeContextType === 'system',
        isUnit: (state) => state.activeContextType === 'unit',
        activeId: (state) => state.activeUnitId || 0,
    },

    actions: {
        setSystemContext() {
            this.activeContextType = 'system';
            this.activeUnitId = null;
            this.activeUnitName = null;
            sessionStorage.setItem('janari_context', 'system');
            sessionStorage.removeItem('janari_unit_id');
        },

        setUnitContext(id: number, name: string) {
            this.activeContextType = 'unit';
            this.activeUnitId = id;
            this.activeUnitName = name;
            sessionStorage.setItem('janari_context', 'unit');
            sessionStorage.setItem('janari_unit_id', id.toString());
        },

        /**
         * Initialize workspace from storage on app boot.
         */
        initWorkspace() {
            const savedContext = sessionStorage.getItem('janari_context') as ContextType;
            const savedUnitId = sessionStorage.getItem('janari_unit_id');

            if (savedContext === 'unit' && savedUnitId) {
                this.activeContextType = 'unit';
                this.activeUnitId = parseInt(savedUnitId, 10);
            } else {
                this.setSystemContext();
            }
        }
    },
    persist: true // Using pinia-plugin-persistedstate for reliability
});
