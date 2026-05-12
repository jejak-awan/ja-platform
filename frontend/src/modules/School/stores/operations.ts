import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseResponse, type PaginationData } from '@/shared/utils/responseParser';
import OperationsService from '../services/OperationsService';
import type { Visitor, OperationAuditLog as AuditLog } from '@/modules/School/types';

interface OperationsState {
    visitors: Visitor[];
    auditLogs: AuditLog[];
    loading: boolean;
    error: string | null;
    pagination: PaginationData | null;
}

export const useOperationsStore = defineStore('operations', {
    state: (): OperationsState => ({
        visitors: [],
        auditLogs: [],
        loading: false,
        error: null,
        pagination: null,
    }),

    actions: {
        async fetchVisitors(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await OperationsService.getVisitors(params);
                const { data } = parseResponse<Visitor>(response);
                this.visitors = data;
            } catch (e: unknown) {
                this.error = (e as Error).message || 'Failed to fetch visitors';
                logger.error('Failed to fetch visitors:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchAuditLogs(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await OperationsService.getAuditLogs(params);
                const { data, pagination } = parseResponse<AuditLog>(response);
                this.auditLogs = data;
                this.pagination = pagination;
            } catch (e: unknown) {
                this.error = (e as Error).message || 'Failed to fetch audit logs';
                logger.error('Failed to fetch audit logs:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
