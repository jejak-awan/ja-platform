export interface Student {
    id: string;
    school_id: string;
    workspace_id?: string;
    user_id?: string;
    nisn: string;
    nis: string;
    nik: string;
    full_name: string;
    name?: string; // Legacy fallback
    email: string;
    phone?: string;
    gender: 'male' | 'female' | 'Laki-laki' | 'Perempuan' | 'L' | 'P';
    place_of_birth?: string;
    date_of_birth?: string;
    address?: string;
    rt?: string;
    rw?: string;
    dusun?: string;
    desa_kelurahan?: string;
    kecamatan?: string;
    father_name?: string;
    father_nik?: string;
    father_birth_year?: number;
    father_education?: string;
    father_occupation?: string;
    father_income?: string;
    mother_name?: string;
    mother_nik?: string;
    mother_birth_year?: number;
    mother_education?: string;
    mother_occupation?: string;
    mother_income?: string;
    guardian_name?: string;
    guardian_nik?: string;
    guardian_birth_year?: number;
    guardian_education?: string;
    guardian_occupation?: string;
    guardian_income?: string;
    photo?: string;
    status: 'active' | 'graduated' | 'dropped_out' | 'moved';
    department_id?: string;
    department?: {
        id: string;
        name: string;
        code: string;
    };
    level?: {
        id: string;
        name: string;
    };
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface Violation {
    id: string;
    student_id: string;
    category_id: string;
    date: string;
    points: number;
    description: string;
    reporter_id: string;
    created_at?: string;
    updated_at?: string;
}

export interface Achievement {
    id: string;
    student_id: string;
    title: string;
    category: 'academic' | 'non-academic';
    level: 'school' | 'district' | 'province' | 'national' | 'international';
    date: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface CounselingRecord {
    id: string;
    student_id: string;
    staff_id: string;
    date: string;
    topic: string;
    content: string;
    status: 'open' | 'closed';
    created_at?: string;
    updated_at?: string;
}

export interface Enrollment {
    id: string;
    registration_number: string;
    full_name: string;
    email: string;
    phone?: string;
    gender: 'male' | 'female';
    status: 'pending' | 'process' | 'accepted' | 'rejected';
    created_at: string;
}

export interface AdmissionSettings {
    is_open: boolean;
    academic_year_id: string;
    start_date: string;
    end_date: string;
}
