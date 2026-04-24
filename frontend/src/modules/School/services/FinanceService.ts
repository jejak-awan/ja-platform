import api from '@/services/api';
import type { AxiosResponse } from 'axios';
import type { FeeType, Bill, Expense, Budget, FinancialSummary } from '@/types';

export const FinanceService = {
    // Fee Types
    async getFeeTypes(params: Record<string, any> = {}): Promise<AxiosResponse<FeeType[]>> {
        return api.get('admin/finance/fee-types', { params });
    },

    async storeFeeType(data: Partial<FeeType>): Promise<AxiosResponse<FeeType>> {
        return api.post('admin/finance/fee-types', data);
    },

    async updateFeeType(id: number, data: Partial<FeeType>): Promise<AxiosResponse<FeeType>> {
        return api.put(`admin/finance/fee-types/${id}`, data);
    },

    async deleteFeeType(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/finance/fee-types/${id}`);
    },

    // Bills
    async getBills(params: Record<string, any> = {}): Promise<AxiosResponse<Bill[]>> {
        return api.get('admin/finance/bills', { params });
    },

    async payBill(id: number, data: { amount: number; payment_method: string }): Promise<AxiosResponse<Bill>> {
        return api.post(`admin/finance/bills/${id}/pay`, data);
    },

    async generateBills(data: Record<string, any>): Promise<AxiosResponse<{ message: string; count: number }>> {
        return api.post('admin/finance/bills/generate', data);
    },

    // Expenses
    async getExpenses(params: Record<string, any> = {}): Promise<AxiosResponse<Expense[]>> {
        return api.get('admin/finance/expenses', { params });
    },

    async getSummary(): Promise<AxiosResponse<FinancialSummary>> {
        return api.get('admin/finance/summary');
    },

    async storeExpense(data: Partial<Expense>): Promise<AxiosResponse<Expense>> {
        return api.post('admin/finance/expenses', data);
    },

    async updateExpense(id: number, data: Partial<Expense>): Promise<AxiosResponse<Expense>> {
        return api.put(`admin/finance/expenses/${id}`, data);
    },

    async deleteExpense(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/finance/expenses/${id}`);
    },

    // Budgets
    async getBudgets(params: Record<string, any> = {}): Promise<AxiosResponse<Budget[]>> {
        return api.get('admin/finance/budgets', { params });
    },

    async storeBudget(data: Partial<Budget>): Promise<AxiosResponse<Budget>> {
        return api.post('admin/finance/budgets', data);
    },

    async updateBudget(id: number, data: Partial<Budget>): Promise<AxiosResponse<Budget>> {
        return api.put(`admin/finance/budgets/${id}`, data);
    },

    async deleteBudget(id: number): Promise<AxiosResponse<void>> {
        return api.delete(`admin/finance/budgets/${id}`);
    }
};

export default FinanceService;
