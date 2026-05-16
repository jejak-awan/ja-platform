import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { School, SchoolUnit } from '@/modules/School/types';

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

    async createInstitution(data: Partial<School>): Promise<AxiosResponse<School>> {
        return api.post('admin/school', data);
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

    async getInstitutionDetail(id: string | number): Promise<AxiosResponse<School>> {
        return api.get(`admin/school/${id}`);
    },

    async getAnalyticsSummary(): Promise<AxiosResponse<any>> {
        return api.get('admin/analytics/summary-full');
    },

    async getUnits(schoolId?: string | number): Promise<AxiosResponse<SchoolUnit[]>> {
        const params = schoolId ? { school_id: schoolId } : {};
        return api.get('admin/institution/levels', { params });
    },

    async storeLevel(data: Partial<SchoolUnit>): Promise<AxiosResponse<SchoolUnit>> {
        return api.post('admin/institution/levels', data);
    },

    async updateUnit(id: string | number, data: Partial<SchoolUnit>): Promise<AxiosResponse<SchoolUnit>> {
        return api.put(`admin/institution/levels/${id}`, data);
    },

    async deleteUnit(id: string | number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/institution/levels/${id}`);
    },

    async selectUnit(id: string | number): Promise<AxiosResponse<SchoolUnit>> {
        return api.post(`admin/institution/levels/${id}/select`);
    },

    async deleteInstitution(id: string | number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/school/${id}`);
    }
};

export default InstitutionService;
