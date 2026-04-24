export interface Visitor {
    id: number;
    school_id: number;
    name: string;
    purpose: string;
    check_in: string;
    check_out?: string;
    status: 'active' | 'completed';
    created_at: string;
    updated_at: string;
}

export interface UksVisit {
    id: number;
    patient_id: number;
    patient_type: string;
    complaint: string;
    treatment?: string;
    handled_by: string;
    visit_time: string;
    created_at: string;
    updated_at: string;
}

export interface Attendance {
    id: number;
    student_id?: number;
    staff_id?: number;
    date: string;
    check_in: string;
    check_out?: string;
    status: 'present' | 'absent' | 'late' | 'sick' | 'permit';
    notes?: string;
}

export interface OperationAuditLog {
    id: number;
    user_id: number;
    action: string;
    model_type: string;
    model_id: number;
    fields?: Record<string, unknown>;
    ip_address: string;
    user_agent?: string;
    created_at: string;
}

export interface LibraryBook {
    id: number | string;
    title: string;
    author?: string;
    quantity: number;
    available?: number;
}

export interface Borrower {
    id: number | string;
    full_name: string;
    identity_number?: string;
}

export interface Graduate {
    id: number | string;
    graduation_year: string | number;
    student?: {
        full_name: string;
    };
    status?: string;
}
