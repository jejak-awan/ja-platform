export interface Exam {
    id: number;
    title: string;
    description?: string;
    subject_id: number;
    start_time: string;
    end_time: string;
    duration_minutes: number;
    status: 'draft' | 'published' | 'completed';
    created_at: string;
    updated_at: string;
}

export interface QuestionBank {
    id: number;
    title: string;
    subject_id: number;
    question_count: number;
    created_at: string;
    updated_at: string;
}

export interface Course {
    id: number;
    title: string;
    description?: string;
    teacher_id: number;
    status: 'active' | 'archived';
    created_at: string;
    updated_at: string;
}

export interface ExamResult {
    id: number;
    exam_id: number;
    student_id: number;
    score: number;
    passed: boolean;
    completed_at: string;
}

export interface Question {
    id: number;
    bank_id: number;
    type: 'multiple_choice' | 'essay';
    content: string;
    options?: Record<string, unknown> | unknown[];
    answer?: string | number | string[] | number[];
    points: number;
}

export interface LmsOverview {
    total_courses: number;
    total_exams: number;
    total_question_banks: number;
    active_students: number;
}
