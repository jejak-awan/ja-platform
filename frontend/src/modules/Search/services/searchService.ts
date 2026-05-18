import api from '@/engine/api/client';
import { searchPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const SearchService = {
    search(params: Record<string, unknown>): Promise<AxiosResponse> {
        return api.get(searchPaths.public, { params });
    },

    stats(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(searchPaths.manageStats, { params });
    },

    queries(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(searchPaths.manageQueries, { params });
    },

    deleteQuery(id: string): Promise<AxiosResponse> {
        return api.delete(searchPaths.deleteQuery(id));
    },

    clearQueries(): Promise<AxiosResponse> {
        return api.post(searchPaths.clearQueries);
    },

    reindex(): Promise<AxiosResponse> {
        return api.post(searchPaths.reindex);
    },
};

export default SearchService;
