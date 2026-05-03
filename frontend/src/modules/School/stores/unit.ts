import { defineStore } from 'pinia';
import { parseResponse } from '@/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { SchoolUnit } from '@/types';

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
        activeUnitId: localStorage.getItem('active_level_id') ? Number(localStorage.getItem('active_level_id')) : null as number | null,
    }),

    getters: {
        activeUnit: (state) => state.levels.find(l => l.id === state.activeUnitId) || null,
    },

    actions: {
        async fetchUnits(_schoolId?: number) {
            this.loading = true;
            try {
                const response = await InstitutionService.getUnits(_schoolId);
                const { data } = parseResponse<SchoolUnit>(response);
                this.levels = data.map(l => ({
                    ...l,
                    settings: (l as any).settings || { use_primary_address: true }
                } as ExtendedSchoolUnit));

                if (!this.activeUnitId && this.levels.length > 0) {
                    const defaultLevel = this.levels.find(l => (l as any).settings?.is_default);
                    const idToSet = defaultLevel?.id || this.levels[0]?.id;
                    if (idToSet !== undefined) this.setActiveLevel(idToSet);
                }
            } catch (e) {
                console.error('[LevelStore] Failed to fetch levels:', e);
            } finally {
                this.loading = false;
            }
        },

        setActiveLevel(id: number) {
            this.activeUnitId = id;
            localStorage.setItem('active_level_id', id.toString());
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
