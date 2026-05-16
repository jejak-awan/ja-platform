export interface Asset {
    id: string;
    school_id: string;
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
    id: string;
    school_id: string;
    category_id: string;
    code: string;
    name: string;
    stock: number;
    unit: string;
    min_stock: number;
    created_at?: string;
    updated_at?: string;
}

export interface HostelBlock {
    id: string;
    name: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface HostelRoom {
    id: string;
    block_id: string;
    number: string;
    capacity: number;
    occupied: number;
    status: 'available' | 'full' | 'maintenance';
    created_at?: string;
    updated_at?: string;
}

export interface TransportRoute {
    id: string;
    school_id: string;
    name: string;
    description?: string;
    vehicle_id?: number;
    staff_id?: number;
    created_at?: string;
    updated_at?: string;
}

export interface HostelBed {
    id: string;
    room_id: string;
    bed_number: string;
    status: 'available' | 'occupied' | 'maintenance';
    allocation?: {
        id: string;
        student_id: string;
        student_name: string;
        start_date: string;
    };
    created_at?: string;
    updated_at?: string;
}

export interface TransportVehicle {
    id: string;
    plate_number: string;
    model?: string;
    capacity?: number;
    status: 'active' | 'maintenance' | 'inactive';
}

export interface Vacancy {
    id: string;
    position: string;
    company_name: string;
    description?: string;
    status: 'open' | 'closed';
    created_at: string;
}

export interface Application {
    id: string;
    vacancy_id: string;
    student_id: string;
    status: string;
    created_at: string;
    student?: {
        full_name: string;
        nis: string;
    };
    vacancy?: Vacancy;
}
