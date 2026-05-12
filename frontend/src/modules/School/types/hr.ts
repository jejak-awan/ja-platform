export interface Staff {
    id: number;
    school_id: number;
    nip?: string;
    name: string;
    email: string;
    phone?: string;
    position?: string;
    status: 'active' | 'inactive';
    photo?: string;
    created_at?: string;
    updated_at?: string;
}

export interface LeaveRequest {
    id: number;
    staff_id: number;
    type: 'sick' | 'annual' | 'special' | 'maternity';
    start_date: string;
    end_date: string;
    reason: string;
    status: 'pending' | 'approved' | 'rejected' | 'cancelled';
    approved_by?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Shift {
    id: number;
    school_id: number;
    name: string;
    start_time: string;
    end_time: string;
    days: number[];
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface SalaryStructure {
    id: number;
    staff_id: number;
    basic_salary: number;
    allowances?: Record<string, number>;
    deductions?: Record<string, number>;
    net_salary: number;
}

export interface Payroll {
    id: number;
    staff_id: number;
    period: string;
    basic_salary: number;
    total_allowance: number;
    total_deduction: number;
    net_salary: number;
    status: 'draft' | 'published' | 'paid';
    paid_at?: string;
    staff?: Staff;
}
