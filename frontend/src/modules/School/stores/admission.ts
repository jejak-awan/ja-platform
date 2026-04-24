import { defineStore } from 'pinia';
import { logger } from '@/utils/logger';
import { parseResponse } from '@/utils/responseParser';
import AdmissionService from '../services/AdmissionService';

interface AdmissionState {
    enrollments: any[];
    loading: boolean;
}

export const useAdmissionStore = defineStore('admission', {
    state: (): AdmissionState => ({
        enrollments: [],
        loading: false,
    }),

    actions: {
        async fetchEnrollments(params = {}) {
            this.loading = true;
            try {
                const response = await AdmissionService.getEnrollments(params);
                const { data } = parseResponse(response);
                this.enrollments = data;
            } catch (e) {
                logger.error('Failed to fetch enrollments:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
