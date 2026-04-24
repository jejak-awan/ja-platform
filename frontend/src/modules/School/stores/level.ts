import { defineStore } from 'pinia';
import { parseResponse } from '@/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { SchoolLevel } from '@/types';

export interface LevelSettings {
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
    kurikulum?: string;
    // Legal
    npwp?: string;
    sk_pendirian?: string;
    tgl_sk_pendirian?: string;
    sk_operasional?: string;
    tgl_sk_operasional?: string;
    bank_name?: string;
    bank_account_number?: string;
    bank_account_holder?: string;
    [key: string]: any;
}

export interface ExtendedSchoolLevel extends SchoolLevel {
    settings?: LevelSettings;
}

export const useLevelStore = defineStore('level', {
    state: () => ({
        levels: [] as SchoolLevel[],
        loading: false,
        activeLevelId: localStorage.getItem('active_level_id') ? Number(localStorage.getItem('active_level_id')) : null as number | null,
    }),

    getters: {
        activeLevel: (state) => state.levels.find(l => l.id === state.activeLevelId) || null,
    },

    actions: {
        async fetchLevels(_schoolId?: number) {
            this.loading = true;
            try {
                const response = await InstitutionService.getLevels();
                const { data } = parseResponse<SchoolLevel>(response);
                this.levels = data.map(l => ({
                    ...l,
                    settings: (l as any).settings || { use_primary_address: true }
                } as ExtendedSchoolLevel));

                if (!this.activeLevelId && this.levels.length > 0) {
                    const firstId = this.levels[0]?.id;
                    if (firstId !== undefined) this.setActiveLevel(firstId);
                }
            } catch (e) {
                console.error('[LevelStore] Failed to fetch levels:', e);
            } finally {
                this.loading = false;
            }
        },

        setActiveLevel(id: number) {
            this.activeLevelId = id;
            localStorage.setItem('active_level_id', id.toString());
        },

        async createLevel(payload: Partial<SchoolLevel>) {
            this.loading = true;
            try {
                await InstitutionService.storeLevel(payload);
                await this.fetchLevels();
            } catch (e) {
                console.error('[LevelStore] Failed to create level:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async updateLevel(id: number, payload: Partial<SchoolLevel>) {
            this.loading = true;
            try {
                await InstitutionService.updateLevel(id, payload);
                await this.fetchLevels();
            } catch (e) {
                console.error(`[LevelStore] Failed to update level ${id}:`, e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async deleteLevel(id: number) {
            this.loading = true;
            try {
                await InstitutionService.deleteLevel(id);
                this.levels = this.levels.filter(l => l.id !== id);
                if (this.activeLevelId === id) {
                    const firstLevelId = this.levels[0]?.id;
                    this.activeLevelId = firstLevelId !== undefined ? firstLevelId : null;
                }
            } finally {
                this.loading = false;
            }
        }
    }
});
