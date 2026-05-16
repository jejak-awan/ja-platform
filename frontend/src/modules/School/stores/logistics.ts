import { defineStore } from 'pinia';
import { logger } from '@/shared/utils/logger';
import { parseResponse, type PaginationData } from '@/shared/utils/responseParser';
import LogisticsService from '../services/LogisticsService';
import type { Asset, InventoryItem, HostelRoom, TransportRoute } from '@/modules/School/types';

interface LogisticsState {
    assets: Asset[];
    inventoryItems: InventoryItem[];
    hostelRooms: HostelRoom[];
    transportRoutes: TransportRoute[];
    loading: boolean;
    pagination: PaginationData | null;
}

export const useLogisticsStore = defineStore('logistics', {
    state: (): LogisticsState => ({
        assets: [],
        inventoryItems: [],
        hostelRooms: [],
        transportRoutes: [],
        loading: false,
        pagination: null,
    }),

    actions: {
        async fetchAssets() {
            this.loading = true;
            try {
                const response = await LogisticsService.getAssets();
                const { data, pagination } = parseResponse<Asset>(response);
                this.assets = data;
                this.pagination = pagination;
            } catch (e: unknown) {
                logger.error('Failed to fetch assets:', e);
            } finally {
                this.loading = false;
            }
        },

        async saveAsset(data: Partial<Asset>, id: string | null = null) {
            try {
                if (id) await LogisticsService.updateSarprasData('asset', id, data);
                else await LogisticsService.storeSarprasData('asset', data);
                await this.fetchAssets();
            } catch (e: unknown) {
                logger.error('Failed to save asset:', e);
                throw e;
            }
        },

        async deleteAsset(id: string) {
            try {
                await LogisticsService.deleteAsset(id);
                this.assets = this.assets.filter(a => a.id !== id);
            } catch (e: unknown) {
                logger.error('Failed to delete asset:', e);
                throw e;
            }
        },

        async fetchInventoryItems(params = {}) {
            this.loading = true;
            try {
                const response = await LogisticsService.getInventoryItems(params);
                const { data } = parseResponse<InventoryItem>(response);
                this.inventoryItems = data;
            } catch (e: unknown) {
                logger.error('Failed to fetch inventory items:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchHostelRooms(params = {}) {
            this.loading = true;
            try {
                const response = await LogisticsService.getHostelRooms(params);
                const { data } = parseResponse<HostelRoom>(response);
                this.hostelRooms = data;
            } catch (e: unknown) {
                logger.error('Failed to fetch hostel rooms:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchTransportRoutes(params = {}) {
            this.loading = true;
            try {
                const response = await LogisticsService.getTransportRoutes(params);
                const { data } = parseResponse<TransportRoute>(response);
                this.transportRoutes = data;
            } catch (e: unknown) {
                logger.error('Failed to fetch transport routes:', e);
            } finally {
                this.loading = false;
            }
        }
    }
});
