import api from '@/services/api';
import type { AxiosResponse } from 'axios';
import type { CbtSession, Exam } from '@/types';

export const CbtService = {
    async getExams(params: Record<string, any> = {}): Promise<AxiosResponse<Exam[]>> {
        return api.get('admin/cbt/exams', { params });
    },

    async getSessions(params: Record<string, any> = {}): Promise<AxiosResponse<CbtSession[]>> {
        return api.get('admin/cbt/sessions', { params });
    },

    async storeSession(data: Partial<CbtSession>): Promise<AxiosResponse<CbtSession>> {
        return api.post('admin/cbt/sessions', data);
    },

    async updateSession(id: number | string, data: Partial<CbtSession>): Promise<AxiosResponse<CbtSession>> {
        return api.put(`admin/cbt/sessions/${id}`, data);
    },

    async deleteSession(id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/cbt/sessions/${id}`);
    },

    async generateToken(id: number | string): Promise<AxiosResponse<{ token: string }>> {
        return api.post(`admin/cbt/sessions/${id}/token`);
    }
};

export default CbtService;
