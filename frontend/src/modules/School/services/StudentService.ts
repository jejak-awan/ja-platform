import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Student, Violation, Achievement, CounselingRecord } from '@/modules/School/types';

export const StudentService = {
    async getStudents(params: Record<string, any> = {}): Promise<AxiosResponse<Student[]>> {
        return api.get('manage/school/students', { params });
    },

    async searchStudents(query: string): Promise<AxiosResponse<Student[]>> {
        return api.get('manage/school/students', { params: { search: query, per_page: 20 } });
    },

    async getStudent(id: string): Promise<AxiosResponse<Student>> {
        return api.get(`manage/school/students/${id}`);
    },

    async createStudent(data: Partial<Student>): Promise<AxiosResponse<Student>> {
        return api.post('manage/school/students', data);
    },

    async updateStudent(id: string, data: Partial<Student>): Promise<AxiosResponse<Student>> {
        return api.put(`manage/school/students/${id}`, data);
    },

    async deleteStudent(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/students/${id}`);
    },

    async getViolations(params: Record<string, any> = {}): Promise<AxiosResponse<Violation[]>> {
        return api.get('manage/school/operations/violations', { params });
    },

    async getAchievements(params: Record<string, any> = {}): Promise<AxiosResponse<Achievement[]>> {
        return api.get('manage/school/operations/achievements', { params });
    },

    async getCounselingRecords(params: Record<string, any> = {}): Promise<AxiosResponse<CounselingRecord[]>> {
        return api.get('manage/school/operations/counseling', { params });
    }
};

export default StudentService;
