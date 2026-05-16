import api from '@/engine/api/client';
import { aiPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const AiService = {
    providers(): Promise<AxiosResponse> {
        return api.get(aiPaths.providers);
    },

    models(provider: string, params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(aiPaths.models(provider), { params });
    },

    generate(payload: Record<string, unknown>): Promise<AxiosResponse> {
        return api.post(aiPaths.generate, payload);
    },
};

export default AiService;
