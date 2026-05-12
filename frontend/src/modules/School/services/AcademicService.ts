import api from '@/core/api/client';
import type { AxiosResponse } from 'axios';
import type { 
    AcademicYear, Semester, Subject, StudyGroup, Schedule, Journal, AcademicOverview,
    Department, StudyGroupMember
} from '@/modules/School/types';

export const AcademicService = {
    async getOverview(): Promise<AxiosResponse<AcademicOverview>> {
        return api.get('admin/academic/overview');
    },

    async getAcademicYears(): Promise<AxiosResponse<AcademicYear[]>> {
        return api.get('admin/academic/years');
    },

    async storeAcademicYear(data: Partial<AcademicYear>): Promise<AxiosResponse<AcademicYear>> {
        return api.post('admin/academic/years', data);
    },

    async updateAcademicYear(id: number, data: Partial<AcademicYear>): Promise<AxiosResponse<AcademicYear>> {
        return api.put(`admin/academic/years/${id}`, data);
    },

    async deleteAcademicYear(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/years/${id}`);
    },

    async getSemesters(): Promise<AxiosResponse<Semester[]>> {
        return api.get('admin/academic/semesters');
    },

    async storeSemester(data: Partial<Semester>): Promise<AxiosResponse<Semester>> {
        return api.post('admin/academic/semesters', data);
    },

    async updateSemester(id: number, data: Partial<Semester>): Promise<AxiosResponse<Semester>> {
        return api.put(`admin/academic/semesters/${id}`, data);
    },

    async deleteSemester(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/semesters/${id}`);
    },

    async getSubjects(): Promise<AxiosResponse<Subject[]>> {
        return api.get('admin/academic/subjects');
    },

    async storeSubject(data: Partial<Subject>): Promise<AxiosResponse<Subject>> {
        return api.post('admin/academic/subjects', data);
    },

    async updateSubject(id: number, data: Partial<Subject>): Promise<AxiosResponse<Subject>> {
        return api.put(`admin/academic/subjects/${id}`, data);
    },

    async deleteSubject(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/subjects/${id}`);
    },

    async getDepartments(): Promise<AxiosResponse<Department[]>> {
        return api.get('admin/academic/departments');
    },

    async getStudyGroupMembers(groupId: number | string): Promise<AxiosResponse<StudyGroupMember[]>> {
        return api.get(`admin/academic/study-groups/${groupId}/members`);
    },

    async addStudyGroupMember(groupId: number | string, studentId: number | string): Promise<AxiosResponse<StudyGroupMember>> {
        return api.post(`admin/academic/study-groups/${groupId}/members`, { student_id: studentId });
    },

    async removeStudyGroupMember(groupId: number | string, studentId: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/study-groups/${groupId}/members/${studentId}`);
    },

    async getStudyGroups(): Promise<AxiosResponse<StudyGroup[]>> {
        return api.get('admin/academic/study-groups');
    },

    async storeStudyGroup(data: Partial<StudyGroup>): Promise<AxiosResponse<StudyGroup>> {
        return api.post('admin/academic/study-groups', data);
    },

    async updateStudyGroup(id: number, data: Partial<StudyGroup>): Promise<AxiosResponse<StudyGroup>> {
        return api.put(`admin/academic/study-groups/${id}`, data);
    },

    async deleteStudyGroup(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/study-groups/${id}`);
    },

    async getSchedules(params: Record<string, any> = {}): Promise<AxiosResponse<Schedule[]>> {
        return api.get('admin/academic/schedules', { params });
    },

    async storeSchedule(data: Partial<Schedule>): Promise<AxiosResponse<Schedule>> {
        return api.post('admin/academic/schedules', data);
    },

    async updateSchedule(id: number, data: Partial<Schedule>): Promise<AxiosResponse<Schedule>> {
        return api.put(`admin/academic/schedules/${id}`, data);
    },

    async deleteSchedule(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/schedules/${id}`);
    },

    async getJournals(params: Record<string, any> = {}): Promise<AxiosResponse<Journal[]>> {
        return api.get('admin/academic/journals', { params });
    },

    async storeJournal(data: Partial<Journal>): Promise<AxiosResponse<Journal>> {
        return api.post('admin/academic/journals', data);
    },

    async updateJournal(id: number, data: Partial<Journal>): Promise<AxiosResponse<Journal>> {
        return api.put(`admin/academic/journals/${id}`, data);
    },

    async deleteJournal(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/academic/journals/${id}`);
    }
};


export default AcademicService;
