import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Enrollment, AdmissionSettings } from '@/modules/School/types';

export const AdmissionService = {
    async getSettings(): Promise<AxiosResponse<AdmissionSettings>> {
        return api.get('manage/school/admission/settings');
    },

    async updateSettings(data: Partial<AdmissionSettings>): Promise<AxiosResponse<AdmissionSettings>> {
        return api.put('manage/school/admission/settings', data);
    },

    async getEnrollments(params: Record<string, any> = {}): Promise<AxiosResponse<Enrollment[]>> {
        return api.get('manage/school/admission/enrollments', { params });
    },

    async getEnrollment(id: string): Promise<AxiosResponse<Enrollment>> {
        return api.get(`manage/school/admission/enrollments/${id}`);
    },

    async storeEnrollment(data: any): Promise<AxiosResponse<Enrollment>> {
        return api.post('manage/school/admission/enrollments', data);
    },

    async updateEnrollmentStatus(id: string | string, status: string): Promise<AxiosResponse<Enrollment>> {
        return api.patch(`manage/school/admission/enrollments/${id}/status`, { status });
    },

    async admitEnrollment(id: string | string): Promise<AxiosResponse<any>> {
        return api.post(`manage/school/admission/enrollments/${id}/admit`);
    }
};

export default AdmissionService;
