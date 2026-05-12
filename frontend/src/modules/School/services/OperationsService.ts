import api from '@/core/api/client';
import type { AxiosResponse } from 'axios';
import type { Visitor, Attendance, OperationAuditLog, LibraryBook, Graduate } from '@/modules/School/types';
import type { Asset } from '@/modules/School/types/logistics';
import type { Staff } from '@/modules/School/types/hr';

export const OperationsService = {
    async getAuditLogs(params: Record<string, any> = {}): Promise<AxiosResponse<OperationAuditLog[]>> {
        return api.get('admin/extensions/logs', { params });
    },

    async getExecutiveSummary(schoolId: number): Promise<AxiosResponse<any>> {
        return api.get('admin/analytics/summary', { params: { school_id: schoolId } });
    },

    async getVisitors(params: Record<string, any> = {}): Promise<AxiosResponse<Visitor[]>> {
        return api.get('admin/operations/visitors', { params });
    },

    async checkInVisitor(data: any): Promise<AxiosResponse<Visitor>> {
        const formData = data instanceof FormData ? data : new FormData();
        // Assume data is object if not FormData
        if (!(data instanceof FormData)) {
            Object.keys(data).forEach(key => formData.append(key, data[key]));
        }
        return api.post('admin/operations/visitors/check-in', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async checkOutVisitor(id: number | string): Promise<AxiosResponse<Visitor>> {
        return api.post(`admin/operations/visitors/${id}/check-out`);
    },

    async getLibraryBooks(params: Record<string, any> = {}): Promise<AxiosResponse<LibraryBook[]>> {
        return api.get('admin/extensions/library', { params });
    },

    async getAlumni(params: Record<string, any> = {}): Promise<AxiosResponse<Graduate[]>> {
        return api.get('admin/extensions/alumni', { params });
    },

    async getAttendances(params: Record<string, any> = {}): Promise<AxiosResponse<Attendance[]>> {
        return api.get('admin/operations/attendance', { params });
    },

    async storeAttendance(data: Partial<Attendance>): Promise<AxiosResponse<Attendance>> {
        return api.post('admin/operations/attendance', data);
    },

    async updateAttendance(id: number | string, data: Partial<Attendance>): Promise<AxiosResponse<Attendance>> {
        return api.put(`admin/operations/attendance/${id}`, data);
    },

    async deleteAttendance(id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/operations/attendance/${id}`);
    },

    async getStudentAffairs(type: string, params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        // More specific mapping if possible, but keep any[] for generic Student Affairs for now
        // except when we know the specific types like LibraryBook/Borrower/Graduate
        return api.get(`admin/operations/${type}`, { params });
    },

    async getMaintenanceAssets(): Promise<AxiosResponse<Asset[]>> {
        return api.get('admin/logistics/assets/maintenance');
    },

    async getMaintenanceStaff(): Promise<AxiosResponse<Staff[]>> {
        return api.get('admin/hr/staff/maintenance');
    },

    async storeStudentAffair(type: string, data: any): Promise<AxiosResponse<any>> {
        return api.post(`admin/operations/${type}`, data);
    },

    async updateStudentAffair(type: string, id: number | string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`admin/operations/${type}/${id}`, data);
    },

    async deleteStudentAffair(type: string, id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/operations/${type}/${id}`);
    },

    async getEligibleGraduates(params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get('admin/operations/graduation/eligible', { params });
    },

    async getGraduationResults(params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get('admin/operations/graduation/results', { params });
    },

    async updateGraduationResult(data: any): Promise<AxiosResponse<any>> {
        return api.post('admin/operations/graduation/results', data);
    },

    async processGraduation(data: { student_ids: number[]; graduation_year: number; status: string }): Promise<AxiosResponse<any>> {
        return api.post('admin/operations/graduation/batch', data);
    },

    async importGrades(file: File, graduationYear: number): Promise<AxiosResponse<any>> {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('graduation_year', String(graduationYear));
        return api.post('admin/operations/graduation/import-grades', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async checkPublicGraduation(params: { identifier: string; dob: string }): Promise<AxiosResponse<any>> {
        return api.get('public/graduation/check', { params });
    },

    // Document Templates
    async getDocumentTemplates(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('admin/operations/document-templates', { params });
    },

    async storeDocumentTemplate(data: any): Promise<AxiosResponse<any>> {
        return api.post('admin/operations/document-templates', data);
    },

    async updateDocumentTemplate(id: number | string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`admin/operations/document-templates/${id}`, data);
    },

    async deleteDocumentTemplate(id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/operations/document-templates/${id}`);
    }
};

export default OperationsService;
