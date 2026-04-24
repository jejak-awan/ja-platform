import { defineStore } from 'pinia';
import { logger } from '@/utils/logger';
import { parseResponse } from '@/utils/responseParser';
import FinanceService from '../services/FinanceService';
import type { FeeType, Bill, Expense, Budget } from '@/types';

interface FinanceState {
    feeTypes: FeeType[];
    bills: Bill[];
    expenses: Expense[];
    budgets: Budget[];
    summary: {
        total_receivables: number;
        total_collections: number;
        total_expenses: number;
        net_balance: number;
        collection_rate: number;
    } | null;
    loading: boolean;
    error: string | null;
}

export const useFinanceStore = defineStore('finance', {
    state: (): FinanceState => ({
        feeTypes: [],
        bills: [],
        expenses: [],
        budgets: [],
        summary: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchSummary() {
            this.error = null;
            try {
                const response = await FinanceService.getSummary();
                this.summary = response.data;
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to fetch summary';
                logger.error('Failed to fetch finance summary:', e);
            }
        },

        async fetchFeeTypes(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await FinanceService.getFeeTypes(params);
                const { data } = parseResponse<FeeType>(response);
                this.feeTypes = data;
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to fetch fee types';
                logger.error('Failed to fetch fee types:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveFeeType(data: Partial<FeeType>, id: number | null = null) {
            this.error = null;
            try {
                if (id) await FinanceService.updateFeeType(id, data);
                else await FinanceService.storeFeeType(data);
                await this.fetchFeeTypes();
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to save fee type';
                logger.error('Failed to save fee type:', e);
                throw e;
            }
        },

        async deleteFeeType(id: number) {
            this.error = null;
            try {
                await FinanceService.deleteFeeType(id);
                this.feeTypes = this.feeTypes.filter(f => f.id !== id);
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to delete fee type';
                logger.error('Failed to delete fee type:', e);
                throw e;
            }
        },

        async fetchBills(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await FinanceService.getBills(params);
                const { data } = parseResponse<Bill>(response);
                this.bills = data;
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to fetch bills';
                logger.error('Failed to fetch bills:', e);
            } finally {
                this.loading = false;
            }
        },

        async payBill(id: number, amount: number, method: string) {
            this.error = null;
            try {
                await FinanceService.payBill(id, { amount, payment_method: method });
                await this.fetchBills();
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to pay bill';
                logger.error('Failed to pay bill:', e);
                throw e;
            }
        },

        async fetchExpenses(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await FinanceService.getExpenses(params);
                const { data } = parseResponse<Expense>(response);
                this.expenses = data;
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to fetch expenses';
                logger.error('Failed to fetch expenses:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveExpense(data: Partial<Expense>, id: number | null = null) {
            this.error = null;
            try {
                if (id) await FinanceService.updateExpense(id, data);
                else await FinanceService.storeExpense(data);
                await this.fetchExpenses();
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to save expense';
                logger.error('Failed to save expense:', e);
                throw e;
            }
        },

        async deleteExpense(id: number) {
            this.error = null;
            try {
                await FinanceService.deleteExpense(id);
                this.expenses = this.expenses.filter(e => e.id !== id);
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to delete expense';
                logger.error('Failed to delete expense:', e);
                throw e;
            }
        },

        async fetchBudgets(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await FinanceService.getBudgets(params);
                const { data } = parseResponse<Budget>(response);
                this.budgets = data;
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to fetch budgets';
                logger.error('Failed to fetch budgets:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveBudget(data: Partial<Budget>, id: number | null = null) {
            this.error = null;
            try {
                if (id) await FinanceService.updateBudget(id, data);
                else await FinanceService.storeBudget(data);
                await this.fetchBudgets();
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to save budget';
                logger.error('Failed to save budget:', e);
                throw e;
            }
        },

        async deleteBudget(id: number) {
            this.error = null;
            try {
                await FinanceService.deleteBudget(id);
                this.budgets = this.budgets.filter(b => b.id !== id);
            } catch (e: unknown) {
                this.error = ((e as Error).message) || 'Failed to delete budget';
                logger.error('Failed to delete budget:', e);
                throw e;
            }
        }
    }
});

