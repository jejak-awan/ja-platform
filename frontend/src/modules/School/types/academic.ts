export interface AcademicYear {
    id: string;
    name: string;
    start_date: string;
    end_date: string;
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
}

export interface Semester {
    id: string;
    academic_year_id: string;
    name: string;
    type: 'odd' | 'even';
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
}

export interface Subject {
    id: string;
    code: string;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface StudyGroup {
    id: string;
    workspace_id: string;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Schedule {
    id: string;
    study_group_id: string;
    subject_id: string;
    staff_id: string;
    day: number;
    start_time: string;
    end_time: string;
    room_id?: number;
    created_at?: string;
    updated_at?: string;
}

export interface Journal {
    id: string;
    schedule_id: string;
    date: string;
    topic: string;
    description?: string;
    attachment?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Department {
    id: string;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface StudyGroupMember {
    id: string;
    study_group_id: string;
    student_id: string;
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
