import api from '@/services/api';
import type { AxiosResponse } from 'axios';
import type { Staff, LeaveRequest, Shift, SalaryStructure, Payroll } from '@/types';

export const HRService = {
    async getStaff(params: Record<string, any> = {}): Promise<AxiosResponse<Staff[]>> {
        return api.get('admin/staff', { params });
    },

    async getStaffDetail(id: number | string): Promise<AxiosResponse<Staff>> {
        return api.get(`admin/staff/${id}`);
    },

    async storeStaff(data: Partial<Staff>): Promise<AxiosResponse<Staff>> {
        return api.post('admin/staff', data);
    },

    async updateStaff(id: number | string, data: Partial<Staff>): Promise<AxiosResponse<Staff>> {
        return api.put(`admin/staff/${id}`, data);
    },

    async deleteStaff(id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/staff/${id}`);
    },

    // Leaves
    async getLeaves(params: Record<string, any> = {}): Promise<AxiosResponse<LeaveRequest[]>> {
        return api.get('admin/hr/leaves', { params });
    },

    async updateLeaveStatus(id: number, status: string): Promise<AxiosResponse<LeaveRequest>> {
        return api.patch(`admin/hr/leaves/${id}/status`, { status });
    },

    // Shifts
    async getTeachers(): Promise<AxiosResponse<Staff[]>> {
        return api.get('admin/hr/teachers');
    },

    async getShifts(): Promise<AxiosResponse<Shift[]>> {
        return api.get('admin/hr/shifts');
    },

    async storeShift(data: Partial<Shift>): Promise<AxiosResponse<Shift>> {
        return api.post('admin/hr/shifts', data);
    },

    async updateShift(id: number, data: Partial<Shift>): Promise<AxiosResponse<Shift>> {
        return api.put(`admin/hr/shifts/${id}`, data);
    },

    async deleteShift(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/hr/shifts/${id}`);
    },

    // Payroll
    async getPayrolls(params: Record<string, any> = {}): Promise<AxiosResponse<Payroll[]>> {
        return api.get('admin/hr/payrolls', { params });
    },

    async getSalaryStructures(staffId: number | string): Promise<AxiosResponse<SalaryStructure[]>> {
        return api.get('admin/hr/salary-structures', { params: { staff_id: staffId } });
    },

    async storeLeave(data: Partial<LeaveRequest>): Promise<AxiosResponse<LeaveRequest>> {
        return api.post('admin/hr/leaves', data);
    },

    async storeSalaryStructure(data: Partial<SalaryStructure>): Promise<AxiosResponse<SalaryStructure>> {
        return api.post('admin/hr/salary-structures', data);
    },

    async generatePayroll(data: { period: string; school_id: number; school_level_id: number }): Promise<AxiosResponse<{ message: string; generated_count: number }>> {
        return api.post('admin/hr/payrolls/generate', data);
    }
};

export default HRService;
