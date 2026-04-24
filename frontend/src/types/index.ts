export * from './core/auth';
export * from './cms/cms';

export * from './cms/menu';

export * from './core/dashboard';
export * from './cms/theme';
export * from './core/assets';
export * from './cms/file-manager';
export * from './cms/forms';
export * from './cms/comments';
export * from './cms/custom-fields';
export * from './core/security';

export * from './school/academic';
export * from './school/student';
export * from './school/hr';
export * from './school/logistics';
export * from './school/finance';
export * from './school/institution';
export * from './school/lms';
export * from './school/operations';
export * from './school/cbt';

export interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

export interface BaseApiResponse<T> {
    success: boolean;
    message?: string;
    data: T;
}
