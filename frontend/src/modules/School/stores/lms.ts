import { defineStore } from 'pinia';
import { logger } from '@/utils/logger';
import { parseResponse } from '@/utils/responseParser';
import LmsService from '../services/LmsService';
import type { Exam, ExamResult, QuestionBank } from '@/types';

interface LmsState {
    banks: QuestionBank[];
    exams: Exam[];
    results: ExamResult[];
    loading: boolean;
    error: string | null;
}

export const useLmsStore = defineStore('lms', {
    state: (): LmsState => ({
        banks: [],
        exams: [],
        results: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchExams() {
            this.loading = true;
            this.error = null;
            try {
                const response = await LmsService.getExams();
                const { data } = parseResponse<Exam>(response);
                this.exams = data;
            } catch (e: unknown) {
                this.error = (e as Error).message || 'Failed to fetch exams';
                logger.error('Failed to fetch exams:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchResults(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await LmsService.getExamResults(params);
                const { data } = parseResponse<ExamResult>(response);
                this.results = data;
            } catch (e: unknown) {
                this.error = (e as Error).message || 'Failed to fetch exam results';
                logger.error('Failed to fetch exam results:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
