import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { School, SchoolUnit } from '@/modules/School/types';

export const InstitutionService = {
    async getInstitution(): Promise<AxiosResponse<School>> {
        return api.get('manage/school/institution');
    },

    async getDefaultInstitution(): Promise<AxiosResponse<School>> {
        return api.get('manage/school/school/default');
    },

    async updateInstitution(data: Partial<School>): Promise<AxiosResponse<School>> {
        return api.put('manage/school/institution', data);
    },

    async createInstitution(data: Partial<School>): Promise<AxiosResponse<School>> {
        return api.post('manage/school/school', data);
    },

    async updateLogo(file: File | FormData): Promise<AxiosResponse<{ logo_url: string }>> {
        const formData = file instanceof FormData ? file : new FormData();
        if (!(file instanceof FormData)) {
            formData.append('logo', file);
        }
        return api.post('manage/school/institution/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async getStats(): Promise<AxiosResponse<any>> {
        return api.get('manage/school/school/stats');
    },

    async getInstitutionDetail(id: string): Promise<AxiosResponse<School>> {
        return api.get(`manage/school/school/${id}`);
    },

    async getAnalyticsSummary(): Promise<AxiosResponse<any>> {
        return api.get('manage/school/analytics/summary-full');
    },

    async getUnits(schoolId?: string): Promise<AxiosResponse<SchoolUnit[]>> {
        const params = schoolId ? { school_id: schoolId } : {};
        return api.get('manage/school/institution/levels', { params });
    },

    async storeLevel(data: Partial<SchoolUnit>): Promise<AxiosResponse<SchoolUnit>> {
        return api.post('manage/school/institution/levels', data);
    },

    async updateUnit(id: string, data: Partial<SchoolUnit>): Promise<AxiosResponse<SchoolUnit>> {
        return api.put(`manage/school/institution/levels/${id}`, data);
    },

    async deleteUnit(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/institution/levels/${id}`);
    },

    async selectUnit(id: string): Promise<AxiosResponse<SchoolUnit>> {
        return api.post(`manage/school/institution/levels/${id}/select`);
    },

    async deleteInstitution(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/school/${id}`);
    }
};

export default InstitutionService;
