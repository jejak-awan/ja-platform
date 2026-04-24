import api from '@/services/api';
import type { AxiosResponse } from 'axios';
import type { Exam, QuestionBank, Course, ExamResult, Question, LmsOverview } from '@/types';

export const LmsService = {
    async getOverview(): Promise<AxiosResponse<LmsOverview>> {
        return api.get('admin/lms/overview');
    },

    async getCourses(): Promise<AxiosResponse<Course[]>> {
        return api.get('admin/lms/courses');
    },

    async getCourse(id: number | string): Promise<AxiosResponse<Course>> {
        return api.get(`admin/lms/courses/${id}`);
    },

    async getQuestionBanks(): Promise<AxiosResponse<QuestionBank[]>> {
        return api.get('admin/lms/banks');
    },

    async storeQuestionBank(data: Partial<QuestionBank>): Promise<AxiosResponse<QuestionBank>> {
        return api.post('admin/lms/banks', data);
    },

    async updateQuestionBank(id: number, data: Partial<QuestionBank>): Promise<AxiosResponse<QuestionBank>> {
        return api.put(`admin/lms/banks/${id}`, data);
    },

    async deleteQuestionBank(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/lms/banks/${id}`);
    },

    async getExams(): Promise<AxiosResponse<Exam[]>> {
        return api.get('admin/lms/exams');
    },

    async storeExam(data: Partial<Exam>): Promise<AxiosResponse<Exam>> {
        return api.post('admin/lms/exams', data);
    },

    async updateExam(id: number, data: Partial<Exam>): Promise<AxiosResponse<Exam>> {
        return api.put(`admin/lms/exams/${id}`, data);
    },

    async deleteExam(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/lms/exams/${id}`);
    },

    async getExamQuestions(id: number | string): Promise<AxiosResponse<Question[]>> {
        return api.get(`admin/lms/exams/${id}/questions`);
    },

    async syncExamQuestions(id: number | string, questions: any[]): Promise<AxiosResponse<{ message: string }>> {
        return api.post(`admin/lms/exams/${id}/questions/sync`, { questions });
    },

    async getExamResults(params: Record<string, any> = {}): Promise<AxiosResponse<ExamResult[]>> {
        return api.get('admin/lms/results', { params });
    },

    async getQuestions(bankId: number): Promise<AxiosResponse<Question[]>> {
        return api.get(`admin/lms/banks/${bankId}/questions`);
    },

    async storeQuestion(bankId: number, data: Partial<Question>): Promise<AxiosResponse<Question>> {
        return api.post(`admin/lms/banks/${bankId}/questions`, data);
    },

    async updateQuestion(id: number, data: Partial<Question>): Promise<AxiosResponse<Question>> {
        return api.put(`admin/lms/questions/${id}`, data);
    },

    async deleteQuestion(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/lms/questions/${id}`);
    },

    // Teacher Management
    async getManagementStats(): Promise<AxiosResponse<LmsOverview>> {
        return api.get('admin/lms/manage/stats');
    },

    async getManagementCourses(params: Record<string, any> = {}): Promise<AxiosResponse<Course[]>> {
        return api.get('admin/lms/manage/courses', { params });
    },

    async getCourseMonitoring(id: number | string): Promise<AxiosResponse<any[]>> {
        return api.get(`admin/lms/manage/courses/${id}/monitoring`);
    },

    async saveCourse(data: Partial<Course>): Promise<AxiosResponse<Course>> {
        if (data.id) {
            return api.put(`admin/lms/courses/${data.id}`, data);
        }
        return api.post('admin/lms/courses', data);
    }
};

export default LmsService;
