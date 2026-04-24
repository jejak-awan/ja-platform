import api from '@/services/api';
import type { AxiosResponse } from 'axios';
import type { School, SchoolLevel } from '@/types';

export const InstitutionService = {
    async getInstitution(): Promise<AxiosResponse<School>> {
        return api.get('admin/institution');
    },

    async getDefaultInstitution(): Promise<AxiosResponse<School>> {
        return api.get('admin/school/default');
    },

    async updateInstitution(data: Partial<School>): Promise<AxiosResponse<School>> {
        return api.put('admin/institution', data);
    },

    async updateLogo(file: File | FormData): Promise<AxiosResponse<{ logo_url: string }>> {
        const formData = file instanceof FormData ? file : new FormData();
        if (!(file instanceof FormData)) {
            formData.append('logo', file);
        }
        return api.post('admin/institution/logo', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async getStats(): Promise<AxiosResponse<any>> {
        return api.get('admin/school/stats');
    },

    async getInstitutionDetail(id: number | string): Promise<AxiosResponse<School>> {
        return api.get(`admin/school/${id}`);
    },

    async getAnalyticsSummary(): Promise<AxiosResponse<any>> {
        return api.get('admin/analytics/summary-full');
    },

    async getLevels(): Promise<AxiosResponse<SchoolLevel[]>> {
        return api.get('admin/institution/levels');
    },

    async storeLevel(data: Partial<SchoolLevel>): Promise<AxiosResponse<SchoolLevel>> {
        return api.post('admin/institution/levels', data);
    },

    async updateLevel(id: number, data: Partial<SchoolLevel>): Promise<AxiosResponse<SchoolLevel>> {
        return api.put(`admin/institution/levels/${id}`, data);
    },

    async deleteLevel(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/institution/levels/${id}`);
    }
};

export default InstitutionService;
