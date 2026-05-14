import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Student, Violation, Achievement, CounselingRecord } from '@/modules/School/types';

export const StudentService = {
    async getStudents(params: Record<string, any> = {}): Promise<AxiosResponse<Student[]>> {
        return api.get('admin/students', { params });
    },

    async searchStudents(query: string): Promise<AxiosResponse<Student[]>> {
        return api.get('admin/students', { params: { search: query, per_page: 20 } });
    },

    async getStudent(id: number): Promise<AxiosResponse<Student>> {
        return api.get(`admin/students/${id}`);
    },

    async createStudent(data: Partial<Student>): Promise<AxiosResponse<Student>> {
        return api.post('admin/students', data);
    },

    async updateStudent(id: number, data: Partial<Student>): Promise<AxiosResponse<Student>> {
        return api.put(`admin/students/${id}`, data);
    },

    async deleteStudent(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/students/${id}`);
    },

    async getViolations(params: Record<string, any> = {}): Promise<AxiosResponse<Violation[]>> {
        return api.get('admin/operations/violations', { params });
    },

    async getAchievements(params: Record<string, any> = {}): Promise<AxiosResponse<Achievement[]>> {
        return api.get('admin/operations/achievements', { params });
    },

    async getCounselingRecords(params: Record<string, any> = {}): Promise<AxiosResponse<CounselingRecord[]>> {
        return api.get('admin/operations/counseling', { params });
    }
};

export default StudentService;
