export interface Asset {
    id: number;
    school_id: number;
    code: string;
    name: string;
    category?: string;
    condition: 'good' | 'fair' | 'poor' | 'broken';
    location?: string;
    purchase_date?: string;
    price?: number;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface InventoryItem {
    id: number;
    school_id: number;
    category_id: number;
    code: string;
    name: string;
    stock: number;
    unit: string;
    min_stock: number;
    created_at?: string;
    updated_at?: string;
}

export interface HostelBlock {
    id: number;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface HostelRoom {
    id: number;
    block_id: number;
    number: string;
    capacity: number;
    occupied: number;
    status: 'available' | 'full' | 'maintenance';
    created_at?: string;
    updated_at?: string;
}

export interface TransportRoute {
    id: number;
    school_id: number;
    name: string;
    description?: string;
    vehicle_id?: number;
    staff_id?: number;
    created_at?: string;
    updated_at?: string;
}

export interface HostelBed {
    id: number;
    room_id: number;
    bed_number: string;
    status: 'available' | 'occupied' | 'maintenance';
    allocation?: {
        id: number;
        student_id: number;
        student_name: string;
        start_date: string;
    };
    created_at?: string;
    updated_at?: string;
}

export interface TransportVehicle {
    id: number;
    plate_number: string;
    model?: string;
    capacity?: number;
    status: 'active' | 'maintenance' | 'inactive';
}

export interface Vacancy {
    id: number;
    position: string;
    company_name: string;
    description?: string;
    status: 'open' | 'closed';
    created_at: string;
}

export interface Application {
    id: number;
    vacancy_id: number;
    student_id: number;
    status: string;
    created_at: string;
    student?: {
        full_name: string;
        nis: string;
    };
    vacancy?: Vacancy;
}
