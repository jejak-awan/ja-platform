<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        Cache Management
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        Manage and monitor system cache performance
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-indigo-500/10 rounded-xl">
              <Activity class="h-6 w-6 text-indigo-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Cache Status
              </p>
              <Badge :variant="cacheStats.status === 'Active' ? 'success' : 'secondary'">
                {{ cacheStats.status || 'Active' }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-emerald-500/10 rounded-xl">
              <Target class="h-6 w-6 text-emerald-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Cache Hits
              </p>
              <p class="text-2xl font-bold tracking-tight text-foreground tabular-nums">
                {{ cacheStats.hits || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-rose-500/10 rounded-xl">
              <XCircle class="h-6 w-6 text-rose-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Cache Misses
              </p>
              <p class="text-2xl font-bold tracking-tight text-foreground tabular-nums">
                {{ cacheStats.misses || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle class="text-lg font-semibold">
          Cache Actions
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Button
            variant="destructive"
            :disabled="clearing"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left"
            @click="clearAllCache"
          >
            <div class="flex items-center gap-2">
              <Trash2 class="w-4 h-4" />
              <span class="font-bold">Clear All Cache</span>
            </div>
            <span class="text-xs opacity-80 font-normal">Remove all cached data from the system</span>
          </Button>
          <Button
            variant="secondary"
            :disabled="clearing"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left bg-amber-500 hover:bg-amber-600 text-white border-0"
            @click="clearContentCache"
          >
            <div class="flex items-center gap-2">
              <FileText class="w-4 h-4" />
              <span class="font-bold">Clear Content Cache</span>
            </div>
            <span class="text-xs opacity-80 font-normal">Remove content-related cache only</span>
          </Button>
          <Button
            variant="secondary"
            :disabled="warming"
            class="h-auto py-4 px-6 flex flex-col items-start gap-1 justify-start text-left bg-emerald-500 hover:bg-emerald-600 text-white border-0"
            @click="warmUpCache"
          >
            <div class="flex items-center gap-2">
              <Zap class="w-4 h-4" />
              <span class="font-bold">Warm Up Cache</span>
            </div>
            <span class="text-xs opacity-80 font-normal">Preload frequently used data into cache</span>
          </Button>
        </div>
      </CardContent>
    </Card>

    <Card
      v-if="cacheStats.details"
      class="mt-6"
    >
      <CardHeader>
        <CardTitle class="text-lg font-semibold">
          Detailed Statistics
        </CardTitle>
      </CardHeader>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Key</TableHead>
              <TableHead>Value</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="(value, key) in cacheStats.details"
              :key="key"
            >
              <TableCell class="font-mono text-xs">
                {{ key }}
              </TableCell>
              <TableCell class="font-medium tabular-nums">
                {{ value }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import api from '@/core/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import { Badge, Button, Card, CardContent, CardHeader, CardTitle, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Target from 'lucide-vue-next/dist/esm/icons/target.js';
import XCircle from 'lucide-vue-next/dist/esm/icons/circle-x.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import toast from '@/shared/services/legacy-toast';
import { useConfirm } from '@/shared/composables/useConfirm';

interface CacheStats {
    status: string;
    hits: number;
    misses: number;
    details?: Record<string, string | number> | null;
}

const cacheStats = ref<CacheStats>({
    status: 'Active',
    hits: 0,
    misses: 0,
});
const clearing = ref(false);
const warming = ref(false);
const { confirm } = useConfirm();

const fetchCacheStats = async () => {
    try {
        // Try to get cache stats from SystemController
        const response = await api.get('/admin/core/system/cache-status');
        const data = parseSingleResponse<CacheStats>(response);
        if (data) {
            cacheStats.value = {
                status: data.status || 'Active',
                hits: data.hits || 0,
                misses: data.misses || 0,
                details: data.details || null,
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch cache stats:', error);
        // Keep default values on error
        cacheStats.value = {
            status: 'Active',
            hits: 0,
            misses: 0,
        };
    }
};

const clearAllCache = async () => {
    const confirmed = await confirm({
        title: 'Clear All Cache',
        message: 'Are you sure you want to clear all cache? This may affect performance temporarily.',
        variant: 'danger',
        confirmText: 'Clear All',
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('/admin/core/cache/clear');
        toast.success('All cache cleared successfully');
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to clear cache:', error);
        toast.error('Error', 'Failed to clear cache');
    } finally {
        clearing.value = false;
    }
};

const clearContentCache = async () => {
    const confirmed = await confirm({
        title: 'Clear Content Cache',
        message: 'Are you sure you want to clear content cache?',
        variant: 'warning',
        confirmText: 'Clear Content Cache',
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('/admin/core/cache/clear-content');
        toast.success('Content cache cleared successfully');
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to clear content cache:', error);
        toast.error('Error', 'Failed to clear content cache');
    } finally {
        clearing.value = false;
    }
};

const warmUpCache = async () => {
    warming.value = true;
    try {
        await api.post('/admin/core/cache/warm-up');
        toast.success('Cache warmed up successfully');
        await fetchCacheStats();
    } catch (error: unknown) {
        logger.error('Failed to warm up cache:', error);
        toast.error('Error', 'Failed to warm up cache');
    } finally {
        warming.value = false;
    }
};

onMounted(() => {
    fetchCacheStats();
});
</script>

