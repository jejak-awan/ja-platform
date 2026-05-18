import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { Staff, LeaveRequest, Shift } from '@/modules/School/types';

export const HRService = {
    async getStaff(params: Record<string, any> = {}): Promise<AxiosResponse<Staff[]>> {
        return api.get('manage/school/staff', { params });
    },

    async getStaffDetail(id: string | string): Promise<AxiosResponse<Staff>> {
        return api.get(`manage/school/staff/${id}`);
    },

    async storeStaff(data: Partial<Staff>): Promise<AxiosResponse<Staff>> {
        return api.post('manage/school/staff', data);
    },

    async updateStaff(id: string | string, data: Partial<Staff>): Promise<AxiosResponse<Staff>> {
        return api.put(`manage/school/staff/${id}`, data);
    },

    async deleteStaff(id: string | string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/staff/${id}`);
    },

    // Leaves
    async getLeaves(params: Record<string, any> = {}): Promise<AxiosResponse<LeaveRequest[]>> {
        return api.get('manage/school/hr/leaves', { params });
    },

    async updateLeaveStatus(id: string, status: string): Promise<AxiosResponse<LeaveRequest>> {
        return api.patch(`manage/school/hr/leaves/${id}/status`, { status });
    },

    // Shifts
    async getTeachers(): Promise<AxiosResponse<Staff[]>> {
        return api.get('manage/school/hr/teachers');
    },

    async getShifts(): Promise<AxiosResponse<Shift[]>> {
        return api.get('manage/school/hr/shifts');
    },

    async storeShift(data: Partial<Shift>): Promise<AxiosResponse<Shift>> {
        return api.post('manage/school/hr/shifts', data);
    },

    async updateShift(id: string, data: Partial<Shift>): Promise<AxiosResponse<Shift>> {
        return api.put(`manage/school/hr/shifts/${id}`, data);
    },

    async deleteShift(id: string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/hr/shifts/${id}`);
    },

    /* 
    // Payroll & Salary (Disabled for BOS compliance)
    async getPayrolls(params: Record<string, any> = {}): Promise<AxiosResponse<Payroll[]>> {
        return api.get('manage/school/hr/payrolls', { params });
    },

    async getSalaryStructures(staffId: string): Promise<AxiosResponse<SalaryStructure[]>> {
        return api.get('manage/school/hr/salary-structures', { params: { staff_id: staffId } });
    },
    */

    async storeLeave(data: Partial<LeaveRequest>): Promise<AxiosResponse<LeaveRequest>> {
        return api.post('manage/school/hr/leaves', data);
    },

    /*
    async storeSalaryStructure(data: Partial<SalaryStructure>): Promise<AxiosResponse<SalaryStructure>> {
        return api.post('manage/school/hr/salary-structures', data);
    },

    async generatePayroll(data: { period: string; school_id: string; workspace_id: string }): Promise<AxiosResponse<{ message: string; generated_count: number }>> {
        return api.post('manage/school/hr/payrolls/generate', data);
    }
    */
};

export default HRService;
