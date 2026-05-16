import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export type WorkspaceContextType = 'system' | 'unit' | 'foundation' | 'authority';

export const useWorkspaceStore = defineStore('workspace', () => {
    const activeWorkspaceId = ref<number | null>(
        sessionStorage.getItem('active_workspace_id') 
            ? Number(sessionStorage.getItem('active_workspace_id')) 
            : null
    );
    const activeWorkspaceName = ref<string | null>(sessionStorage.getItem('active_workspace_name') || null);
    const activeContextType = ref<WorkspaceContextType>(
        (sessionStorage.getItem('active_workspace_context') as WorkspaceContextType) || 'system'
    );
    const switching = ref(false);

    const isSystem = computed(() => activeContextType.value === 'system');
    const isUnit = computed(() => activeContextType.value === 'unit');
    const activeId = computed(() => activeWorkspaceId.value || 0);
    const context = computed(() => activeContextType.value);

    async function setWorkspaceContext(id: string | null, type: WorkspaceContextType = 'unit', name: string | null = null, silent = false) {
        if (activeWorkspaceId.value === id && activeContextType.value === type && !silent) return;

        switching.value = true;
        activeWorkspaceId.value = id;
        activeContextType.value = type;
        activeWorkspaceName.value = name;

        if (id !== null) {
            sessionStorage.setItem('active_workspace_id', id.toString());
        } else {
            sessionStorage.removeItem('active_workspace_id');
        }
        
        sessionStorage.setItem('active_workspace_context', type);
        if (name) {
            sessionStorage.setItem('active_workspace_name', name);
        } else {
            sessionStorage.removeItem('active_workspace_name');
        }

        try {
            if (!silent) {
                window.location.reload();
            }
        } finally {
            switching.value = false;
        }
    }

    async function setSystemContext(silent = false) {
        await setWorkspaceContext(0, 'system', 'System', silent);
    }

    function initWorkspace() {
        const savedId = sessionStorage.getItem('active_workspace_id');
        const savedContext = sessionStorage.getItem('active_workspace_context') as WorkspaceContextType;
        
        if (savedId !== null && savedContext) {
            activeWorkspaceId.value = Number(savedId);
            activeContextType.value = savedContext;
            activeWorkspaceName.value = sessionStorage.getItem('active_workspace_name');
        } else {
            activeContextType.value = 'system';
            activeWorkspaceId.value = 0;
        }
    }

    return {
        activeWorkspaceId,
        activeWorkspaceName,
        activeContextType,
        switching,
        isSystem,
        isUnit,
        activeId,
        context,
        setWorkspaceContext,
        setSystemContext,
        initWorkspace
    };
});
