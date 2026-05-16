import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { logger } from '@/shared/utils/logger';
import { ensureArray } from '@/shared/utils/responseParser';

export interface MenuItem {
    id: string;
    label: string;
    url: string;
    parent_id?: string;
    order: number;
}

export interface LayoutState {
    menus: Record<string, MenuItem[]>;
    widgets: Record<string, any[]>;
    loading: boolean;
}

export const useLayoutStore = defineStore('layout', {
    state: (): LayoutState => ({
        menus: {},
        widgets: {},
        loading: false,
    }),

    actions: {
        async fetchMenu(location: string, module: string = 'cms') {
            this.loading = true;
            try {
                const response = await api.get(`/public/layout/menus/${location}`, { params: { module } });
                this.menus[location] = ensureArray(response.data);
                return this.menus[location];
            } catch (error) {
                logger.error(`[Layout Store] Error fetching menu ${location}:`, error);
                return [];
            } finally {
                this.loading = false;
            }
        },

        async fetchWidgets(location: string, module: string = 'cms') {
            this.loading = true;
            try {
                const response = await api.get(`/public/layout/widgets/${location}`, { params: { module } });
                this.widgets[location] = ensureArray(response.data);
                return this.widgets[location];
            } catch (error) {
                logger.error(`[Layout Store] Error fetching widgets ${location}:`, error);
                return [];
            } finally {
                this.loading = false;
            }
        }
    }
});
