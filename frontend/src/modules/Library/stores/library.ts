import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { logger } from '@/shared/utils/logger';
import { ensureArray, parseResponse } from '@/shared/utils/responseParser';

export interface Tag {
    id: string;
    name: string;
    slug: string;
    type: string;
    description?: string;
    contents_count?: number;
}

export interface LibraryState {
    tags: Tag[];
    pagination: any | null;
    statistics: Record<string, number> | null;
    loading: boolean;
}

export const useLibraryStore = defineStore('library', {
    state: (): LibraryState => ({
        tags: [],
        pagination: null,
        statistics: null,
        loading: false,
    }),

    actions: {
        async fetchTags(params: Record<string, any> = {}) {
            this.loading = true;
            try {
                const response = await api.get('/manage/library/tags', { params });
                const { data, pagination } = parseResponse(response);
                this.tags = ensureArray(data);
                this.pagination = pagination;
                return this.tags;
            } catch (error) {
                logger.error('[Library Store] Error fetching tags:', error);
                return [];
            } finally {
                this.loading = false;
            }
        },

        async fetchStatistics() {
            try {
                const response = await api.get('/manage/library/tags/statistics');
                this.statistics = response.data?.data || response.data;
                return this.statistics;
            } catch (error) {
                logger.error('[Library Store] Error fetching statistics:', error);
                return null;
            }
        },

        async saveTag(tag: Partial<Tag>) {
            this.loading = true;
            try {
                const method = tag.id ? 'put' : 'post';
                const url = tag.id ? `/manage/library/tags/${tag.id}` : '/manage/library/tags';
                const response = await api[method](url, tag);
                await this.fetchTags({ type: tag.type });
                return response.data;
            } catch (error) {
                logger.error('[Library Store] Error saving tag:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteTag(id: string, type: string = 'cms') {
            this.loading = true;
            try {
                await api.delete(`/manage/library/tags/${id}`);
                await this.fetchTags({ type });
            } catch (error) {
                logger.error('[Library Store] Error deleting tag:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
