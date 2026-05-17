import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseSingleResponse, type PaginationData } from '@/shared/utils/responseParser';
import InstitutionService from '../services/InstitutionService';
import type { School } from '@/modules/School/types';

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

        async fetchSchool(_id?: string) {
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
                    
                    // Persist for API interceptor
                    if (this.currentSchool.id) {
                        sessionStorage.setItem('active_school_id', String(this.currentSchool.id));
                    }
                }
            } catch (e: unknown) {
                logger.error('Failed to fetch institution:', e);
            } finally {
                this.loading = false;
            }
        },

        // Legacy aliases
        async createSchool(data: Partial<School>) {
            this.loading = true;
            try {
                const response = await InstitutionService.createInstitution(data);
                this.currentSchool = parseSingleResponse<School>(response);
                if (this.currentSchool) {
                    this.schools = [this.currentSchool];
                    if (this.currentSchool.id) {
                        sessionStorage.setItem('active_school_id', String(this.currentSchool.id));
                    }
                }
                return this.currentSchool;
            } catch (e: unknown) {
                logger.error('Failed to create institution:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async updateSchool(_id: string, data: School) {
            return this.saveInstitution(data);
        },

        async deleteSchool(id: string) {
            this.loading = true;
            try {
                await InstitutionService.deleteInstitution(id);
                if (this.currentSchool?.id === id) {
                    this.currentSchool = null;
                    this.schools = [];
                    sessionStorage.removeItem('active_school_id');
                }
            } catch (e: unknown) {
                logger.error('Failed to delete institution:', e);
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async saveInstitution(data: Partial<School>) {
            this.loading = true;
            try {
                const response = await InstitutionService.updateInstitution(data);
                this.currentSchool = parseSingleResponse<School>(response);
                if (this.currentSchool) {
                    this.schools = [this.currentSchool];
                    if (this.currentSchool.id) {
                        sessionStorage.setItem('active_school_id', String(this.currentSchool.id));
                    }
                }
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
