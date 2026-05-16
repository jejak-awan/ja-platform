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

    reindex(): Promise<AxiosResponse> {
        return api.post(searchPaths.reindex);
    },
};

export default SearchService;
