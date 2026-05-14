import type { Student } from './student';
import type { AcademicYear } from './academic';

export interface FeeType {
    id: number;
    school_id: number;
    workspace_id: number;
    name: string;
    code?: string;
    amount: number;
    period: 'once' | 'monthly' | 'quarterly' | 'yearly';
    description?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface StudentBill {
    id: number;
    school_id: number;
    workspace_id: number;
    student_id: number;
    fee_type_id: number;
    academic_year_id: number;
    semester_id?: number;
    month?: number;
    amount: number;
    paid_amount: number;
    due_date?: string;
    status: 'unpaid' | 'partially_paid' | 'paid';
    notes?: string;
    student?: Student;
    fee_type?: FeeType;
    academic_year?: AcademicYear;
    created_at?: string;
    updated_at?: string;
}

/** @deprecated Use StudentBill instead */
export type Bill = StudentBill;

export interface PaymentTransaction {
    id: number;
    school_id: number;
    student_bill_id: number;
    amount: number;
    payment_date: string;
    payment_method: 'cash' | 'transfer' | 'digital_wallet' | string;
    reference_number?: string;
    verification_hash?: string;
    status: 'pending' | 'verified' | 'rejected';
    notes?: string;
    verified_by?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Expense {
    id: number;
    school_id: number;
    workspace_id: number;
    category: string;
    date: string;
    amount: number;
    description: string;
    attachment_path?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Budget {
    id: number;
    school_id: number;
    workspace_id: number;
    academic_year_id: number;
    category: string;
    planned_amount: number;
    actual_amount: number;
    is_warning?: boolean;
    notes?: string;
    academic_year?: AcademicYear;
    created_at?: string;
    updated_at?: string;
}

export interface FinancialSummary {
    total_receivables: number;
    total_collections: number;
    total_expenses: number;
    net_balance: number;
    collection_rate: number;
    unpaid_bills_count: number;
    paid_bills_count: number;
}
