import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Enrollment, AdmissionSettings } from '@/modules/School/types';

export const AdmissionService = {
    async getSettings(): Promise<AxiosResponse<AdmissionSettings>> {
        return api.get('admin/admission/settings');
    },

    async updateSettings(data: Partial<AdmissionSettings>): Promise<AxiosResponse<AdmissionSettings>> {
        return api.put('admin/admission/settings', data);
    },

    async getEnrollments(params: Record<string, any> = {}): Promise<AxiosResponse<Enrollment[]>> {
        return api.get('admin/admission/enrollments', { params });
    },

    async getEnrollment(id: string): Promise<AxiosResponse<Enrollment>> {
        return api.get(`admin/admission/enrollments/${id}`);
    },

    async storeEnrollment(data: any): Promise<AxiosResponse<Enrollment>> {
        return api.post('admin/admission/enrollments', data);
    },

    async updateEnrollmentStatus(id: string | string, status: string): Promise<AxiosResponse<Enrollment>> {
        return api.patch(`admin/admission/enrollments/${id}/status`, { status });
    },

    async admitEnrollment(id: string | string): Promise<AxiosResponse<any>> {
        return api.post(`admin/admission/enrollments/${id}/admit`);
    }
};

export default AdmissionService;
