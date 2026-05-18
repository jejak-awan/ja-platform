import api from '@/engine/api/client';
import type { AxiosResponse } from 'axios';
import type { 
    Asset, InventoryItem, TransportRoute, 
    HostelBlock, HostelRoom, HostelBed, 
    TransportVehicle, Vacancy, Application 
} from '@/modules/School/types';

export const LogisticsService = {
    async getSarprasData(type: string, params: Record<string, any> = {}): Promise<AxiosResponse<any>> {
        return api.get(`manage/school/sarpras/${type}`, { params });
    },

    async storeSarprasData(type: string, data: any): Promise<AxiosResponse<any>> {
        return api.post(`manage/school/sarpras/${type}`, data);
    },

    async updateSarprasData(type: string, id: string | string, data: any): Promise<AxiosResponse<any>> {
        return api.put(`manage/school/sarpras/${type}/${id}`, data);
    },

    async deleteSarprasData(type: string, id: string | string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/sarpras/${type}/${id}`);
    },

    // Specific helpers if needed
    async getRooms(): Promise<AxiosResponse<HostelRoom[]>> {
        return api.get('manage/school/sarpras/room');
    },

    async getAssets(): Promise<AxiosResponse<Asset[]>> {
        return api.get('manage/school/sarpras/asset');
    },

    async deleteAsset(id: string | string): Promise<AxiosResponse<void>> {
        return api.delete(`manage/school/sarpras/asset/${id}`);
    },

    async getBuildings(): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/sarpras/building');
    },

    async getLands(): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/sarpras/land');
    },

    // Inventory
    async getInventoryCategories(): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/logistics/inventory/categories');
    },

    async getInventoryItems(params: Record<string, any> = {}): Promise<AxiosResponse<InventoryItem[]>> {
        return api.get('manage/school/logistics/inventory/items', { params });
    },

    async getInventoryTransactions(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/logistics/inventory/transactions', { params });
    },

    // Career / Job Portal
    async getSarprasRooms(): Promise<AxiosResponse<HostelRoom[]>> {
        return api.get('manage/school/sarpras/room');
    },

    async getVacancies(params: Record<string, any> = {}): Promise<AxiosResponse<Vacancy[]>> {
        return api.get('manage/school/logistics/career/vacancies', { params });
    },

    async getApplications(params: Record<string, any> = {}): Promise<AxiosResponse<Application[]>> {
        return api.get('manage/school/logistics/career/applications', { params });
    },

    // Transport
    async getTransportVehicles(params: Record<string, any> = {}): Promise<AxiosResponse<TransportVehicle[]>> {
        return api.get('manage/school/logistics/transport/vehicles', { params });
    },

    async getTransportRoutes(params: Record<string, any> = {}): Promise<AxiosResponse<TransportRoute[]>> {
        return api.get('manage/school/logistics/transport/routes', { params });
    },

    async getTransportRegistrations(params: Record<string, any> = {}): Promise<AxiosResponse<any[]>> {
        return api.get('manage/school/logistics/transport/registrations', { params });
    },

    // Hostel
    async getHostelBlocks(): Promise<AxiosResponse<HostelBlock[]>> {
        return api.get('manage/school/logistics/hostel/blocks');
    },

    async getHostelRooms(blockId: string | Record<string, any>): Promise<AxiosResponse<HostelRoom[]>> {
        const id = typeof blockId === 'object' ? (blockId.block_id || '') : blockId;
        return api.get(`manage/school/logistics/hostel/blocks/${id}/rooms`);
    },

    async getHostelBeds(roomId: string): Promise<AxiosResponse<HostelBed[]>> {
        return api.get(`manage/school/logistics/hostel/rooms/${roomId}/beds`);
    },

    async releaseBed(allocationId: string): Promise<AxiosResponse<void>> {
        return api.post(`manage/school/logistics/hostel/release/${allocationId}`);
    },

    async allocateBed(data: Record<string, any>): Promise<AxiosResponse<any>> {
        return api.post('manage/school/logistics/hostel/allocate', data);
    },

    async registerTransport(data: Record<string, any>): Promise<AxiosResponse<any>> {
        return api.post('manage/school/logistics/transport/register', data);
    }
};

export default LogisticsService;
