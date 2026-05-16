import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import type { Content } from '@/modules/Cms/types/cms';

interface ContentState {
    currentContent: Content | null;
    loading: boolean;
}

export const useContentStore = defineStore('content', {
    state: (): ContentState => ({
        currentContent: null,
        loading: false,
    }),

    actions: {
        async fetchContent(slug: string) {
            this.loading = true;
            try {
                // Determine endpoint based on type? 
                // Currently code uses /public/cms/contents/{slug} generic endpoint
                const response = await api.get(`/public/cms/contents/${slug}`);
                this.currentContent = response.data;
            } catch (e) {
                logger.error('Failed to fetch content:', e);
                this.currentContent = null;
            } finally {
                this.loading = false;
            }
        },

        clearContent() {
            this.currentContent = null;
        }
    }
});
