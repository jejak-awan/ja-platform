export interface Visitor {
    id: string | number;
    school_id: string | number | number;
    name: string;
    purpose: string;
    check_in: string;
    check_out?: string;
    status: 'active' | 'completed';
    created_at: string;
    updated_at: string;
}

export interface UksVisit {
    id: string | number;
    patient_id: string | number | number;
    patient_type: string;
    complaint: string;
    treatment?: string;
    handled_by: string;
    visit_time: string;
    created_at: string;
    updated_at: string;
}

export interface Attendance {
    id: string | number;
    student_id?: string | number;
    staff_id?: string | number;
    date: string;
    check_in: string;
    check_out?: string;
    status: 'present' | 'absent' | 'late' | 'sick' | 'permit';
    notes?: string;
}

export interface OperationAuditLog {
    id: string | number;
    user_id: string | number | number;
    action: string;
    model_type: string;
    model_id: string | number | number;
    fields?: Record<string, unknown>;
    ip_address: string;
    user_agent?: string;
    created_at: string;
}

export interface LibraryBook {
    id: string | number | string;
    title: string;
    author?: string;
    quantity: number;
    available?: number;
}

export interface Borrower {
    id: string | number | string;
    full_name: string;
    identity_number?: string;
}

export interface Graduate {
    id: string | number | string;
    graduation_year: string | number;
    student?: {
        full_name: string;
    };
    status?: string;
}
