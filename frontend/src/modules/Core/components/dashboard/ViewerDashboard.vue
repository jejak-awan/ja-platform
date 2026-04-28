<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          Welcome back, {{ authStore.user?.name }}!
        </h1>
        <p class="text-muted-foreground">
          Explore the latest content updates.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Recent Updates -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Newspaper class="w-5 h-5 text-primary" />
            Latest Published Content
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div
            v-if="loading"
            class="space-y-3"
          >
            <div
              v-for="i in 3"
              :key="i"
              class="h-12 bg-muted/50 rounded"
            />
          </div>
          <div
            v-else-if="recentContent.length === 0"
            class="text-center py-8 text-muted-foreground"
          >
            No content published yet.
          </div>
          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="item in recentContent"
              :key="item.id"
              class="flex items-center justify-between p-3 rounded-lg border bg-card hover:bg-accent/50"
            >
              <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 text-primary rounded-full">
                  <FileText class="w-4 h-4" />
                </div>
                <div>
                  <p class="font-medium text-sm">
                    {{ item.title }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    Published {{ formatDate(item.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Help & Resources -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <HelpCircle class="w-5 h-5 text-indigo-500" />
            Quick Resources
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4">
            <a
              href="/"
              target="_blank"
              class="flex items-center p-3 rounded-lg border hover:bg-accent group"
            >
              <Home class="w-5 h-5 text-muted-foreground group-hover:text-primary mr-3" />
              <div>
                <p class="font-medium text-sm">Visit Public Site</p>
                <p class="text-xs text-muted-foreground">View the live website</p>
              </div>
            </a>
            <!-- Add more links as needed -->
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/modules/Core/stores/auth';
import api from '@/services/api';
import { parseSingleResponse, ensureArray } from '@/utils/responseParser';
import dayjs from 'dayjs';

import {
    Card,
    CardHeader,
    CardTitle,
    CardContent
} from '@/components/ui';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Newspaper from 'lucide-vue-next/dist/esm/icons/newspaper.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Home from 'lucide-vue-next/dist/esm/icons/house.js';

interface ContentItem {
    id: number | string;
    title: string;
    created_at: string;
    [key: string]: unknown;
}

const authStore = useAuthStore();
const recentContent = ref<ContentItem[]>([]);
const loading = ref(false);

const fetchDashboard = async () => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/viewer');
        const data = parseSingleResponse<Record<string, unknown>>(response);
        if (data && 'recentContent' in data) {
             recentContent.value = ensureArray<ContentItem>(data.recentContent as ContentItem[]);
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch viewer dashboard:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date: string) => {
    return dayjs(date).format('MMM D, YYYY');
};

onMounted(() => {
    fetchDashboard();
});
</script>
