import api from '@/engine/api/client';
import { cmsPaths, systemPaths } from '@/engine/api/paths';
import type { AxiosResponse } from 'axios';

export const CmsService = {
    publicContents(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(cmsPaths.publicContents, { params });
    },

    publicContent(slug: string): Promise<AxiosResponse> {
        return api.get(cmsPaths.publicContent(slug));
    },

    publicCategories(): Promise<AxiosResponse> {
        return api.get(cmsPaths.publicCategories);
    },

    settingsGroup(group: string): Promise<AxiosResponse> {
        return api.get(systemPaths.settingsGroup(group));
    },

    manageContents(params: Record<string, unknown> = {}): Promise<AxiosResponse> {
        return api.get(cmsPaths.contents, { params });
    },

    manageContent(id: string | number): Promise<AxiosResponse> {
        return api.get(cmsPaths.content(id));
    },
};

export default CmsService;
