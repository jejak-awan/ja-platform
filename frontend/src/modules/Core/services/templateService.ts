
import api from '@/core/api/client';
import type { AxiosResponse } from 'axios';

export interface TemplateData {
    name: string;
    body_template?: string;
    type?: string;
    [key: string]: unknown;
}

export default {
    getTemplates(params?: Record<string, unknown>): Promise<AxiosResponse> {
        return api.get('/admin/cms/content-templates', { params });
    },
    saveTemplate(data: TemplateData): Promise<AxiosResponse> {
        return api.post('/admin/cms/content-templates', data);
    },
    deleteTemplate(id: number | string): Promise<AxiosResponse> {
        return api.delete(`/admin/cms/content-templates/${id}`);
    },
    getTemplate(id: number | string): Promise<AxiosResponse> {
        return api.get(`/admin/cms/content-templates/${id}`);
    }
}
