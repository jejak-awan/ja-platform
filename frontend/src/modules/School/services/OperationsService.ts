import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Visitor, Attendance, OperationAuditLog, LibraryBook, Graduate } from '@/modules/School/types';
import type { Asset } from '@/modules/School/types/logistics';
import type { Staff } from '@/modules/School/types/hr';

export const OperationsService = {
    async getAuditLogs(params: Record<string, any> = {}): Promise<AxiosResponse<OperationAuditLog[]>> {
        return api.get('manage/school/extensions/logs', { params });
    },

    async getExecutiveSummary(schoolId: string): Promise<AxiosResponse<any>> {
        return api.get('manage/school/analytics/summary', { params: { school_id: schoolId } });
    },

    async getVisitors(params: Record<string, any> = {}): Promise<AxiosResponse<Visitor[]>> {
        return api.get('manage/school/operations/visitors', { params });
    },

    async checkInVisitor(data: any): Promise<AxiosResponse<Visitor>> {
        const formData = data instanceof FormData ? data : new FormData();
        // Assume data is object if not FormData
        if (!(data instanceof FormData)) {
            Object.keys(data).forEach(key => formData.append(key, data[key]));
        }
        return api.post('manage/school/operations/visitors/check-in', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async checkOutVisitor(id: string): Promise<AxiosResponse<Visitor>> {
        return api.post(`manage/school/operations/visitors/${id}/check-out`);
    },

    async getLibraryBooks(params: Record<string, any> = {}): Promise<AxiosResponse<LibraryBook[]>> {
        return api.get('manage/school/extensions/library', { params });
    },

    async getAlumni(params: Record<string, any> = {}): Promise<AxiosResponse<Graduate[]>> {
        return api.get('manage/school/extensions/alumni', { params });
    },

    async getAttendances(params: Record<string, any> = {}): Promise<AxiosResponse<Attendance[]>> {
        return api.get('manage/school/operations/attendance', { params });
    },

    async storeAttendance(data: Partial<Attendance>): Promise<AxiosResponse<Attendance>> {
        return api.post('manage/school/operations/attendance', data);
    },

    async updateAttendance(id: string, data: Partial<Attendance>): Promise<AxiosResponse<Attendance>> {
        return api.put(`manage/school/operations/attendance/${id}`, data);
    },

    async deleteAttendance(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/operations/attendance/${id}`);
    },

    async getStudentAffairs(type: string, params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        // More specific mapping if possible, but keep any[] for generic Student Affairs for now
        // except when we know the specific types like LibraryBook/Borrower/Graduate
        return api.get(`manage/school/operations/${type}`, { params });
    },

    async getMaintenanceAssets(): Promise<AxiosResponse<Asset[]>> {
        return api.get('manage/school/logistics/assets/maintenance');
    },

    async getMaintenanceStaff(): Promise<AxiosResponse<Staff[]>> {
        return api.get('manage/school/hr/staff/maintenance');
    },

    async storeStudentAffair(type: string, data: any): Promise<AxiosResponse<any>> {
        return api.post(`manage/school/operations/${type}`, data);
    },

    async updateStudentAffair(type: string, id: string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`manage/school/operations/${type}/${id}`, data);
    },

    async deleteStudentAffair(type: string, id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/operations/${type}/${id}`);
    },

    async getEligibleGraduates(params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get('manage/school/operations/graduation/eligible', { params });
    },

    async getGraduationResults(params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get('manage/school/operations/graduation/results', { params });
    },

    async updateGraduationResult(data: any): Promise<AxiosResponse<any>> {
        return api.post('manage/school/operations/graduation/results', data);
    },

    async processGraduation(data: { student_ids: string[]; graduation_year: number; status: string }): Promise<AxiosResponse<any>> {
        return api.post('manage/school/operations/graduation/batch', data);
    },

    async importGrades(file: File, graduationYear: number): Promise<AxiosResponse<any>> {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('graduation_year', String(graduationYear));
        return api.post('manage/school/operations/graduation/import-grades', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    async checkPublicGraduation(params: { identifier: string; dob: string }): Promise<AxiosResponse<any>> {
        return api.get('public/graduation/check', { params });
    },

    // Document Templates
    async getDocumentTemplates(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/operations/document-templates', { params });
    },

    async storeDocumentTemplate(data: any): Promise<AxiosResponse<any>> {
        return api.post('manage/school/operations/document-templates', data);
    },

    async updateDocumentTemplate(id: string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`manage/school/operations/document-templates/${id}`, data);
    },

    async deleteDocumentTemplate(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/operations/document-templates/${id}`);
    }
};

export default OperationsService;
