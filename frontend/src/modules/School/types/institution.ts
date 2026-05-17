export interface School {
    id: string;
    npsn?: string;
    name: string;
    type: 'public' | 'private';
    is_multi_unit?: boolean;
    is_multi_branch?: boolean;
    level: string;
    address?: string;
    phone?: string;
    email?: string;
    website?: string;
    logo?: string;
    headmaster_name?: string;
    accreditation?: string;
    status: 'active' | 'inactive';
    created_at?: string;
    updated_at?: string;
}

export interface SchoolUnit {
    id: string;
    school_id: string;
    name: string; // e.g., Grade 1, Grade 2
    level: string;
    code: string;
    npsn?: string;
    accreditation?: string;
    settings?: any;
    created_at?: string;
    updated_at?: string;
}
