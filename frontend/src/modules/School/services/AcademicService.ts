import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { 
    AcademicYear, Semester, Subject, StudyGroup, Schedule, Journal, AcademicOverview,
    Department, StudyGroupMember
} from '@/modules/School/types';

export const AcademicService = {
    async getOverview(): Promise<AxiosResponse<AcademicOverview>> {
        return api.get('manage/school/academic/overview');
    },

    async getAcademicYears(): Promise<AxiosResponse<AcademicYear[]>> {
        return api.get('manage/school/academic/years');
    },

    async storeAcademicYear(data: Partial<AcademicYear>): Promise<AxiosResponse<AcademicYear>> {
        return api.post('manage/school/academic/years', data);
    },

    async updateAcademicYear(id: string, data: Partial<AcademicYear>): Promise<AxiosResponse<AcademicYear>> {
        return api.put(`manage/school/academic/years/${id}`, data);
    },

    async deleteAcademicYear(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/years/${id}`);
    },

    async getSemesters(): Promise<AxiosResponse<Semester[]>> {
        return api.get('manage/school/academic/semesters');
    },

    async storeSemester(data: Partial<Semester>): Promise<AxiosResponse<Semester>> {
        return api.post('manage/school/academic/semesters', data);
    },

    async updateSemester(id: string, data: Partial<Semester>): Promise<AxiosResponse<Semester>> {
        return api.put(`manage/school/academic/semesters/${id}`, data);
    },

    async deleteSemester(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/semesters/${id}`);
    },

    async getSubjects(): Promise<AxiosResponse<Subject[]>> {
        return api.get('manage/school/academic/subjects');
    },

    async storeSubject(data: Partial<Subject>): Promise<AxiosResponse<Subject>> {
        return api.post('manage/school/academic/subjects', data);
    },

    async updateSubject(id: string, data: Partial<Subject>): Promise<AxiosResponse<Subject>> {
        return api.put(`manage/school/academic/subjects/${id}`, data);
    },

    async deleteSubject(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/subjects/${id}`);
    },

    async getDepartments(): Promise<AxiosResponse<Department[]>> {
        return api.get('manage/school/academic/departments');
    },

    async getStudyGroupMembers(groupId: string): Promise<AxiosResponse<StudyGroupMember[]>> {
        return api.get(`manage/school/academic/study-groups/${groupId}/members`);
    },

    async addStudyGroupMember(groupId: string, studentId: string): Promise<AxiosResponse<StudyGroupMember>> {
        return api.post(`manage/school/academic/study-groups/${groupId}/members`, { student_id: studentId });
    },

    async removeStudyGroupMember(groupId: string, studentId: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/study-groups/${groupId}/members/${studentId}`);
    },

    async getStudyGroups(): Promise<AxiosResponse<StudyGroup[]>> {
        return api.get('manage/school/academic/study-groups');
    },

    async storeStudyGroup(data: Partial<StudyGroup>): Promise<AxiosResponse<StudyGroup>> {
        return api.post('manage/school/academic/study-groups', data);
    },

    async updateStudyGroup(id: string, data: Partial<StudyGroup>): Promise<AxiosResponse<StudyGroup>> {
        return api.put(`manage/school/academic/study-groups/${id}`, data);
    },

    async deleteStudyGroup(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/study-groups/${id}`);
    },

    async getSchedules(params: Record<string, any> = {}): Promise<AxiosResponse<Schedule[]>> {
        return api.get('manage/school/academic/schedules', { params });
    },

    async storeSchedule(data: Partial<Schedule>): Promise<AxiosResponse<Schedule>> {
        return api.post('manage/school/academic/schedules', data);
    },

    async updateSchedule(id: string, data: Partial<Schedule>): Promise<AxiosResponse<Schedule>> {
        return api.put(`manage/school/academic/schedules/${id}`, data);
    },

    async deleteSchedule(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/schedules/${id}`);
    },

    async getJournals(params: Record<string, any> = {}): Promise<AxiosResponse<Journal[]>> {
        return api.get('manage/school/academic/journals', { params });
    },

    async storeJournal(data: Partial<Journal>): Promise<AxiosResponse<Journal>> {
        return api.post('manage/school/academic/journals', data);
    },

    async updateJournal(id: string, data: Partial<Journal>): Promise<AxiosResponse<Journal>> {
        return api.put(`manage/school/academic/journals/${id}`, data);
    },

    async deleteJournal(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/academic/journals/${id}`);
    }
};


export default AcademicService;
