export interface Student {
    id: number;
    school_id: number;
    school_unit_id?: number;
    user_id?: number;
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
    department_id?: number;
    department?: {
        id: number;
        name: string;
        code: string;
    };
    level?: {
        id: number;
        name: string;
    };
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface Violation {
    id: number;
    student_id: number;
    category_id: number;
    date: string;
    points: number;
    description: string;
    reporter_id: number;
    created_at?: string;
    updated_at?: string;
}

export interface Achievement {
    id: number;
    student_id: number;
    title: string;
    category: 'academic' | 'non-academic';
    level: 'school' | 'district' | 'province' | 'national' | 'international';
    date: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface CounselingRecord {
    id: number;
    student_id: number;
    staff_id: number;
    date: string;
    topic: string;
    content: string;
    status: 'open' | 'closed';
    created_at?: string;
    updated_at?: string;
}

export interface Enrollment {
    id: number;
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
    academic_year_id: number;
    start_date: string;
    end_date: string;
}
