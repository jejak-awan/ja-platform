import api from '@/core/api/client';
import type { AxiosResponse } from 'axios';
import type { 
    Asset, InventoryItem, TransportRoute, 
    HostelBlock, HostelRoom, HostelBed, 
    TransportVehicle, Vacancy, Application 
} from '@/modules/School/types';

export const LogisticsService = {
    async getSarprasData(type: string, params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get(`admin/sarpras/${type}`, { params });
    },

    async storeSarprasData(type: string, data: any): Promise<AxiosResponse<any>> {
        return api.post(`admin/sarpras/${type}`, data);
    },

    async updateSarprasData(type: string, id: number | string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`admin/sarpras/${type}/${id}`, data);
    },

    async deleteSarprasData(type: string, id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/sarpras/${type}/${id}`);
    },

    // Specific helpers if needed
    async getRooms(): Promise<AxiosResponse<HostelRoom[]>> {
        return api.get('admin/sarpras/room');
    },

    async getAssets(): Promise<AxiosResponse<Asset[]>> {
        return api.get('admin/sarpras/asset');
    },

    async deleteAsset(id: number | string): Promise<AxiosResponse<void>> {
        return api.delete(`admin/sarpras/asset/${id}`);
    },

    async getBuildings(): Promise<AxiosResponse<any[]>> {
        return api.get('admin/sarpras/building');
    },

    async getLands(): Promise<AxiosResponse<any[]>> {
        return api.get('admin/sarpras/land');
    },

    // Inventory
    async getInventoryCategories(): Promise<AxiosResponse<any[]>> {
        return api.get('admin/logistics/inventory/categories');
    },

    async getInventoryItems(params: Record<string, any> = {}): Promise<AxiosResponse<InventoryItem[]>> {
        return api.get('admin/logistics/inventory/items', { params });
    },

    async getInventoryTransactions(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('admin/logistics/inventory/transactions', { params });
    },

    // Career / Job Portal
    async getSarprasRooms(): Promise<AxiosResponse<HostelRoom[]>> {
        return api.get('admin/sarpras/room');
    },

    async getVacancies(params: Record<string, any> = {}): Promise<AxiosResponse<Vacancy[]>> {
        return api.get('admin/logistics/career/vacancies', { params });
    },

    async getApplications(params: Record<string, any> = {}): Promise<AxiosResponse<Application[]>> {
        return api.get('admin/logistics/career/applications', { params });
    },

    // Transport
    async getTransportVehicles(params: Record<string, any> = {}): Promise<AxiosResponse<TransportVehicle[]>> {
        return api.get('admin/logistics/transport/vehicles', { params });
    },

    async getTransportRoutes(params: Record<string, any> = {}): Promise<AxiosResponse<TransportRoute[]>> {
        return api.get('admin/logistics/transport/routes', { params });
    },

    async getTransportRegistrations(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('admin/logistics/transport/registrations', { params });
    },

    // Hostel
    async getHostelBlocks(): Promise<AxiosResponse<HostelBlock[]>> {
        return api.get('admin/logistics/hostel/blocks');
    },

    async getHostelRooms(blockId: number | string | Record<string, any>): Promise<AxiosResponse<HostelRoom[]>> {
        const id = typeof blockId === 'object' ? (blockId.block_id || '') : blockId;
        return api.get(`admin/logistics/hostel/blocks/${id}/rooms`);
    },

    async getHostelBeds(roomId: number | string): Promise<AxiosResponse<HostelBed[]>> {
        return api.get(`admin/logistics/hostel/rooms/${roomId}/beds`);
    },

    async releaseBed(allocationId: number | string): Promise<AxiosResponse<void>> {
        return api.post(`admin/logistics/hostel/release/${allocationId}`);
    },

    async allocateBed(data: Record<string, any>): Promise<AxiosResponse<any>> {
        return api.post('admin/logistics/hostel/allocate', data);
    },

    async registerTransport(data: Record<string, any>): Promise<AxiosResponse<any>> {
        return api.post('admin/logistics/transport/register', data);
    }
};

export default LogisticsService;
