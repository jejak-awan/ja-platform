export interface Staff {
    id: string;
    school_id: string;
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
    id: string;
    staff_id: string;
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
    id: string;
    school_id: string;
    name: string;
    start_time: string;
    end_time: string;
    days: number[];
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface SalaryStructure {
    id: string;
    staff_id: string;
    basic_salary: number;
    allowances?: Record<string, number>;
    deductions?: Record<string, number>;
    net_salary: number;
}

export interface Payroll {
    id: string;
    staff_id: string;
    period: string;
    basic_salary: number;
    total_allowance: number;
    total_deduction: number;
    net_salary: number;
    status: 'draft' | 'published' | 'paid';
    paid_at?: string;
    staff?: Staff;
}
