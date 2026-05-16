import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseResponse, parseSingleResponse, type PaginationData } from '@/shared/utils/responseParser';
import HRService from '../services/HRService';
import type { Staff, LeaveRequest, Shift } from '@/modules/School/types';

interface HRState {
    staff: Staff[];
    leaves: LeaveRequest[];
    shifts: Shift[];
    currentStaff: Staff | null;
    loading: boolean;
    pagination: PaginationData | null;
}

export const useHRStore = defineStore('hr', {
    state: (): HRState => ({
        staff: [],
        leaves: [],
        shifts: [],
        currentStaff: null,
        loading: false,
        pagination: null,
    }),

    actions: {
        async fetchStaff(params = {}) {
            this.loading = true;
            try {
                const response = await HRService.getStaff(params);
                const { data, pagination } = parseResponse<Staff>(response);
                this.staff = data;
                this.pagination = pagination;
            } catch (e: unknown) {
                logger.error('Failed to fetch staff:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchStaffDetail(id: string) {
            this.loading = true;
            try {
                const response = await HRService.getStaffDetail(id);
                this.currentStaff = parseSingleResponse<Staff>(response);
            } catch (e: unknown) {
                logger.error('Failed to fetch staff detail:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveStaff(data: Partial<Staff>, id: string | null = null) {
            try {
                if (id) await HRService.updateStaff(id, data);
                else await HRService.storeStaff(data);
                await this.fetchStaff();
            } catch (e: unknown) {
                logger.error('Failed to save staff:', e);
                throw e;
            }
        },

        async deleteStaff(id: string) {
            try {
                await HRService.deleteStaff(id);
                this.staff = this.staff.filter(s => s.id !== id);
            } catch (e: unknown) {
                logger.error('Failed to delete staff:', e);
                throw e;
            }
        },

        async fetchLeaves(params = {}) {
            this.loading = true;
            try {
                const response = await HRService.getLeaves(params);
                const { data } = parseResponse<LeaveRequest>(response);
                this.leaves = data;
            } catch (e: unknown) {
                logger.error('Failed to fetch leaves:', e);
            } finally {
                this.loading = false;
            }
        },

        async updateLeaveStatus(id: string, status: string) {
            try {
                await HRService.updateLeaveStatus(id, status);
                const leave = this.leaves.find(l => l.id === id);
                if (leave) leave.status = status as any;
            } catch (e: unknown) {
                logger.error('Failed to update leave status:', e);
                throw e;
            }
        },

        async fetchShifts() {
            this.loading = true;
            try {
                const response = await HRService.getShifts();
                const { data } = parseResponse<Shift>(response);
                this.shifts = data;
            } catch (e: unknown) {
                logger.error('Failed to fetch shifts:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveShift(data: Partial<Shift>, id: string | null = null) {
            try {
                if (id) {
                    await HRService.updateShift(id, data);
                } else {
                    await HRService.storeShift(data);
                }
                await this.fetchShifts();
            } catch (e: unknown) {
                logger.error('Failed to save shift:', e);
                throw e;
            }
        },

        async deleteShift(id: string) {
            try {
                await HRService.deleteShift(id);
                this.shifts = this.shifts.filter(s => s.id !== id);
            } catch (e: unknown) {
                logger.error('Failed to delete shift:', e);
                throw e;
            }
        }
    }
});

