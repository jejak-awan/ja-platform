export interface ShieldLog {
    id: number;
    event_type: string;
    ip_address: string;
    details: string;
    user_agent: string;
    created_at: string;
}

export interface ShieldStats {
    verifications: number;
    failures: number;
    honeypot: number;
    scannersBlocked: number;
    extensionsBlocked: number;
    currentDifficulty: number;
    isScaling: boolean;
}

export interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}
export interface AuditLog {
    id: number;
    user_id?: number;
    user_name?: string;
    event: string;
    auditable_type: string;
    auditable_id: number;
    old_values?: Record<string, unknown>;
    new_values?: Record<string, unknown>;
    url?: string;
    ip_address: string;
    user_agent?: string;
    tags?: string;
    created_at: string;
}
