import type { User } from '@/types/core/auth';

export type CommentStatus = 'pending' | 'approved' | 'rejected' | 'spam';

export interface Comment {
    id: number;
    parent_id?: number | null;
    content_id: number;
    user_id?: number | null;
    name?: string | null;
    email?: string | null;
    body: string;
    status: CommentStatus;
    ip_address?: string;
    user_agent?: string;
    created_at: string;
    updated_at: string;

    // Relations
    user?: User | null;
    content?: {
        id: number;
        title: string;
        slug: string;
    };
    replies?: Comment[];
    replies_count?: number;
    parent?: Comment | null;
}

export interface CommentStatistics {
    total: number;
    pending: number;
    approved: number;
    rejected: number;
    spam: number;
}
