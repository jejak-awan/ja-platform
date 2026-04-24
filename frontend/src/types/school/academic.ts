export interface AcademicYear {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
}

export interface Semester {
    id: number;
    academic_year_id: number;
    name: string;
    type: 'odd' | 'even';
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
}

export interface Subject {
    id: number;
    code: string;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface StudyGroup {
    id: number;
    school_level_id: number;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Schedule {
    id: number;
    study_group_id: number;
    subject_id: number;
    staff_id: number;
    day: number;
    start_time: string;
    end_time: string;
    room_id?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Journal {
    id: number;
    schedule_id: number;
    date: string;
    topic: string;
    description?: string;
    attachment?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Department {
    id: number;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface StudyGroupMember {
    id: number;
    study_group_id: number;
    student_id: number;
    joined_at: string;
    student?: {
        full_name: string;
        nis: string;
    };
}

export interface AcademicOverview {
    total_students: number;
    total_staff: number;
    total_classes: number;
    total_subjects: number;
}
