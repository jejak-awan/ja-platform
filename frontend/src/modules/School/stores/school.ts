import { defineStore } from 'pinia';
import { logger } from '@/utils/logger';
import { parseSingleResponse, type PaginationData } from '@/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { School } from '@/types';

interface SchoolState {
    schools: School[];
    currentSchool: School | null;
    loading: boolean;
    pagination: PaginationData | null;
}

export const useSchoolStore = defineStore('school', {
    state: (): SchoolState => ({
        schools: [],
        currentSchool: null,
        loading: false,
        pagination: null,
    }),

    actions: {
        // Legacy alias for tests and components
        async fetchSchools(_page = 1) {
            return this.fetchSchool();
        },

        async fetchSchool(_id?: number) {
            this.loading = true;
            try {
                const response = await InstitutionService.getInstitution();
                // Ensure we get a single object, even if wrapped in data or returned as array
                const result = parseSingleResponse<School | School[]>(response);
                
                if (result) {
                    // If result is an array (e.g. data.data was an array), take the first item
                    const schoolData = Array.isArray(result) ? result[0] : result;
                    this.currentSchool = schoolData as School;
                    this.schools = [this.currentSchool];
                }
            } catch (e: unknown) {
                logger.error('Failed to fetch institution:', e);
            } finally {
                this.loading = false;
            }
        },

        // Legacy aliases
        async createSchool(data: School) {
            return this.saveInstitution(data);
        },

        async updateSchool(_id: number, data: School) {
            return this.saveInstitution(data);
        },

        async deleteSchool(_id: number) {
            logger.warning('Delete school called but not supported for singleton institution');
        },

        async saveInstitution(data: Partial<School>) {
            this.loading = true;
            try {
                const response = await InstitutionService.updateInstitution(data);
                this.currentSchool = parseSingleResponse<School>(response);
                return this.currentSchool;
            } catch (e: unknown) {
                logger.error('Failed to save institution:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async uploadLogo(file: File) {
            const formData = new FormData();
            formData.append('logo', file);
            try {
                const response = await InstitutionService.updateLogo(formData);
                const data = parseSingleResponse<{ logo_url: string }>(response);
                if (data && this.currentSchool) {
                    this.currentSchool.logo = data.logo_url;
                }
                return data;
            } catch (e: unknown) {
                logger.error('Failed to upload logo:', e);
                throw e;
            }
        }
    }
});
