import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseResponse, parseSingleResponse } from '@/shared/utils/responseParser';
import StudentService from '../services/StudentService';
import type { Student, Violation, Achievement, CounselingRecord } from '@/modules/School/types';
import type { PaginationData } from '@/shared/utils/responseParser';

interface StudentState {
    students: Student[];
    currentStudent: Student | null;
    violations: Violation[];
    achievements: Achievement[];
    counselingRecords: CounselingRecord[];
    loading: boolean;
    pagination: PaginationData | null;
}

export const useStudentStore = defineStore('student', {
    state: (): StudentState => ({
        students: [],
        currentStudent: null,
        violations: [],
        achievements: [],
        counselingRecords: [],
        loading: false,
        pagination: null,
    }),

    actions: {
        async fetchStudents(params = {}) {
            this.loading = true;
            try {
                const response = await StudentService.getStudents(params);
                const { data, pagination } = parseResponse<Student>(response);
                this.students = data;
                this.pagination = pagination;
            } catch (e) {
                logger.error('Failed to fetch students:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchStudent(id: string) {
            this.loading = true;
            try {
                const response = await StudentService.getStudent(id);
                this.currentStudent = parseSingleResponse<Student>(response);
            } catch (e) {
                logger.error('Failed to fetch student:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveStudent(data: Partial<Student>, id: string | null = null) {
            try {
                if (id) await StudentService.updateStudent(id, data);
                else await StudentService.createStudent(data);
                await this.fetchStudents();
            } catch (e) {
                logger.error('Failed to save student:', e);
                throw e;
            }
        },

        async deleteStudent(id: string) {
            try {
                await StudentService.deleteStudent(id);
                this.students = this.students.filter(s => s.id !== id);
            } catch (e) {
                logger.error('Failed to delete student:', e);
                throw e;
            }
        },

        async fetchViolations(params = {}) {
            this.loading = true;
            try {
                const response = await StudentService.getViolations(params);
                const { data } = parseResponse<Violation>(response);
                this.violations = data;
            } catch (e) {
                logger.error('Failed to fetch violations:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchAchievements(params = {}) {
            this.loading = true;
            try {
                const response = await StudentService.getAchievements(params);
                const { data } = parseResponse<Achievement>(response);
                this.achievements = data;
            } catch (e) {
                logger.error('Failed to fetch achievements:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchCounselingRecords(params = {}) {
            this.loading = true;
            try {
                const response = await StudentService.getCounselingRecords(params);
                const { data } = parseResponse<CounselingRecord>(response);
                this.counselingRecords = data;
            } catch (e) {
                logger.error('Failed to fetch counseling records:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
