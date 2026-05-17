import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useWorkspaceStore, type WorkspaceContextType } from '@/engine/stores/workspace';
import { parseResponse } from '@/shared/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { SchoolUnit } from '@/modules/School/types';

export const useUnitStore = defineStore('unit', () => {
    const workspaceStore = useWorkspaceStore();
    
    const levels = ref<SchoolUnit[]>([]);
    const loading = ref(false);
    const fetchPromise = ref<Promise<void> | null>(null);

    const activeUnit = computed(() => levels.value.find(l => l.id === workspaceStore.activeWorkspaceId) || null);
    const activeUnitId = computed(() => workspaceStore.activeWorkspaceId);
    const activeContextType = computed(() => workspaceStore.activeContextType);
    const switchingContext = computed(() => workspaceStore.switching);

    async function fetchUnits(schoolId?: string, silent = true) {
        if (fetchPromise.value) return fetchPromise.value;

        fetchPromise.value = (async () => {
            loading.value = true;
            try {
                const response = await InstitutionService.getUnits(schoolId);
                const { data } = parseResponse<SchoolUnit>(response);
                levels.value = data;

                // Auto-initialize if needed
                if (workspaceStore.activeWorkspaceId === null && levels.value.length > 0) {
                    // Logic: If exactly one unit, use it. Otherwise default to system (ID 0)
                    if (levels.value.length === 1) {
                        const unit = levels.value[0];
                        if (unit) {
                            await setActiveLevel(unit.id, 'unit', silent, unit.name);
                        }
                    } else {
                        await setActiveLevel('0', 'system', silent);
                    }
                }
            } catch (e) {
                console.error('[UnitStore] Failed to fetch units:', e);
            } finally {
                loading.value = false;
                fetchPromise.value = null;
            }
        })();

        return fetchPromise.value;
    }

    async function setActiveLevel(id: string, type: WorkspaceContextType = 'unit', silent = false, name: string | null = null) {
        await workspaceStore.setWorkspaceContext(id, type, name, silent);
    }

    async function createUnit(data: Partial<SchoolUnit>) {
        loading.value = true;
        try {
            const response = await InstitutionService.storeLevel(data);
            await fetchUnits(data.school_id, true);
            return response;
        } finally {
            loading.value = false;
        }
    }

    async function updateUnit(id: string, data: Partial<SchoolUnit>) {
        loading.value = true;
        try {
            const response = await InstitutionService.updateUnit(id, data);
            await fetchUnits(data.school_id, true);
            return response;
        } finally {
            loading.value = false;
        }
    }

    async function deleteUnit(id: string) {
        loading.value = true;
        try {
            const response = await InstitutionService.deleteUnit(id);
            await fetchUnits(undefined, true);
            return response;
        } finally {
            loading.value = false;
        }
    }

    return {
        levels,
        loading,
        activeUnit,
        activeUnitId,
        activeContextType,
        switchingContext,
        fetchUnits,
        setActiveLevel,
        createUnit,
        updateUnit,
        deleteUnit
    };
});

export type UnitSettings = any;
