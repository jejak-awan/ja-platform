export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    parent_id?: number | null;
    created_at?: string;
    updated_at?: string;
    posts_count?: number;
    contents_count?: number;
    image?: string | null;
    is_active?: boolean;
    sort_order?: number;
    children?: Category[];
    all_children?: Category[];
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
    posts_count?: number;
    contents_count?: number;
    isNew?: boolean; // UI only
}
