import type { Exam } from './lms';

export interface CbtSession {
    id: string;
    exam_id: string;
    name: string;
    token?: string;
    start_time: string;
    end_time: string;
    status: 'pending' | 'active' | 'completed';
    exam?: Exam;
    created_at: string;
    updated_at: string;
}
