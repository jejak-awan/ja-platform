import { defineStore } from 'pinia';
import { parseResponse } from '@/shared/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { SchoolUnit } from '@/modules/School/types';

export interface UnitSettings {
    // Location
    use_primary_address?: boolean;
    address?: string;
    rt?: string;
    rw?: string;
    dusun?: string;
    desa_kelurahan?: string;
    kecamatan?: string;
    kabupaten_kota?: string;
    provinsi?: string;
    provinsi_id?: string;
    kabupaten_kota_id?: string;
    kecamatan_id?: string;
    desa_kelurahan_id?: string;
    kode_pos?: string;
    phone?: string;
    email?: string;
    fax?: string;
    website?: string;
    accreditation?: string;
    // Profile
    principal_name?: string;
    foundation_name?: string;
    vision?: string;
    mission?: string;
    goals?: string;
    history?: string;
    // Legal
    npwp?: string;
    nss?: string;
    nds?: string;
    sk_pendirian?: string;
    tgl_sk_pendirian?: string;
    sk_operasional?: string;
    tgl_sk_operasional?: string;
    bank_name?: string;
    bank_account_number?: string;
    bank_account_holder?: string;
    logo?: string;
    logo_dinas?: string;
    logo_yayasan?: string;
    [key: string]: any;
}

export interface ExtendedSchoolUnit extends SchoolUnit {
    settings?: UnitSettings;
}

export const useUnitStore = defineStore('unit', {
    state: () => ({
        levels: [] as SchoolUnit[],
        loading: false,
        activeUnitId: sessionStorage.getItem('active_level_id') ? Number(sessionStorage.getItem('active_level_id')) : null as number | null,
        activeContextType: sessionStorage.getItem('active_context_type') || 'unit' as 'system' | 'foundation' | 'authority' | 'unit',
        switchingContext: false,
        fetchPromise: null as Promise<void> | null,
    }),

    getters: {
        activeUnit: (state) => state.levels.find(l => l.id === state.activeUnitId) || null,
    },

    actions: {
        async fetchUnits(_schoolId?: number, silent = true) {
            if (this.fetchPromise) return this.fetchPromise;

            this.fetchPromise = (async () => {
                this.loading = true;
                try {
                    const response = await InstitutionService.getUnits(_schoolId);
                    const { data } = parseResponse<SchoolUnit>(response);
                    this.levels = data.map(l => ({
                        ...l,
                        settings: (l as any).settings || { use_primary_address: true }
                    } as ExtendedSchoolUnit));

                    // Only auto-initialize if we don't have a persistent context yet
                    if (this.activeUnitId === null && this.levels.length > 0) {
                        const schoolStore = (await import('./school')).useSchoolStore();
                        const authStore = (await import('../../Core/stores/auth')).useAuthStore();
                        const school = schoolStore.currentSchool;
                        const isSuper = authStore.isAtLeastRole('super');
                        
                        // Logic: If Multi-Unit AND (Private school OR Super Admin), default to Global/Pusat (ID 0)
                        if (school?.is_multi_unit && (school?.type === 'private' || isSuper)) {
                            const targetType = isSuper ? 'system' : 'foundation';
                            await this.setActiveLevel(0, targetType, silent);
                        } else {
                            // Fallback: Use default setting or first unit
                            const defaultLevel = this.levels.find(l => (l as any).settings?.is_default);
                            const idToSet = defaultLevel?.id || this.levels[0]?.id;
                            if (idToSet !== undefined) await this.setActiveLevel(idToSet, 'unit', silent);
                        }
                    }
                } catch (e) {
                    if (String(e).toLowerCase().includes('aborted')) return;
                    console.error('[LevelStore] Failed to fetch levels:', e);
                } finally {
                    this.loading = false;
                    this.fetchPromise = null;
                }
            })();

            return this.fetchPromise;
        },

        async setActiveLevel(id: number, type: 'system' | 'foundation' | 'authority' | 'unit' = 'unit', silent = false) {
            // Idempotency guard: prevent redundant context-switch cascades.
            if (this.activeUnitId === id && this.activeContextType === type) {
                return;
            }
            if (this.switchingContext) {
                return;
            }

            this.switchingContext = true;
            this.activeUnitId = id;
            this.activeContextType = type;
            sessionStorage.setItem('active_level_id', id.toString());
            sessionStorage.setItem('active_context_type', type);
            
            try {
                await InstitutionService.selectUnit(id);
                // After switching unit on backend, we usually want to reload the page 
                // to ensure all other stores refresh their data based on the new context
                if (!silent) {
                    window.location.reload();
                }
            } catch (e) {
                if (String(e).toLowerCase().includes('aborted')) return;
                console.error('[LevelStore] Failed to sync unit switch with backend:', e);
            } finally {
                this.switchingContext = false;
            }
        },

        async createUnit(payload: Partial<SchoolUnit>) {
            this.loading = true;
            try {
                const response = await InstitutionService.storeLevel(payload);
                const schoolId = response.data?.school_id || payload.school_id;
                await this.fetchUnits(schoolId);
            } catch (e) {
                console.error('[LevelStore] Failed to create level:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async updateUnit(id: number, payload: Partial<SchoolUnit>) {
            this.loading = true;
            try {
                const response = await InstitutionService.updateUnit(id, payload);
                const schoolId = response.data?.school_id || payload.school_id;
                if (schoolId) await this.fetchUnits(schoolId);
            } catch (e) {
                console.error(`[LevelStore] Failed to update level ${id}:`, e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async deleteUnit(id: number) {
            this.loading = true;
            try {
                await InstitutionService.deleteUnit(id);
                this.levels = this.levels.filter(l => l.id !== id);
                if (this.activeUnitId === id) {
                    const firstLevelId = this.levels[0]?.id;
                    this.activeUnitId = firstLevelId !== undefined ? firstLevelId : null;
                }
            } finally {
                this.loading = false;
            }
        }
    }
});
