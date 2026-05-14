<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">
          {{ $t('features.dashboard.title') }}
        </h1>
        <p class="text-muted-foreground text-sm font-medium">
          {{ $t('features.dashboard.welcome', { name: authStore.user?.name }) }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="loading"
          @click="refreshDashboard"
        >
          <RefreshCw
            class="w-4 h-4 mr-2"
            :class="{ '': loading }"
          />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
    </div>

    <!-- Row 1: Personal Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- My Contents -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.creator.myContent') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.myContents?.total || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-primary font-medium">
                <FileText class="w-3 h-3" />
                <span>{{ stats.myContents?.published || 0 }} {{ $t('features.dashboard.stats.creator.published') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
              <PenTool class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pending Review -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.creator.pendingReview') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.myContents?.pending || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-warning font-medium">
                <Clock3 class="w-3 h-3" />
                <span>{{ $t('features.dashboard.stats.creator.awaitingApproval') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-warning/10 text-warning">
              <Clock class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- My Media -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.creator.myMedia') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.myMedia?.total || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-info font-medium">
                <Image class="w-3 h-3" />
                <span>{{ $t('features.dashboard.stats.creator.uploadedFiles') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-info/10 text-info">
              <FolderOpen class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>
            
      <!-- Drafts -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.creator.drafts') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.myContents?.draft || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-success font-medium">
                <FileText class="w-3 h-3" />
                <span>{{ $t('features.dashboard.stats.creator.workInProgress') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
              <Edit3 class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Traffic Activity -->
    <Card class="border-border/40 bg-card shadow-none rounded-xl">
      <CardHeader class="flex flex-row items-center justify-between pb-2">
        <CardTitle>{{ $t('features.dashboard.traffic.visits') }}</CardTitle>
        <!-- Time Range Filter -->
        <div class="w-[180px]">
          <Select
            v-model="timeRange"
            @update:model-value="fetchStats"
          >
            <SelectTrigger class="w-full">
              <SelectValue :placeholder="$t('features.dashboard.traffic.filters.last7Days')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="7">
                {{ $t('features.dashboard.traffic.filters.last7Days') }}
              </SelectItem>
              <SelectItem value="30">
                {{ $t('features.dashboard.traffic.filters.last30Days') }}
              </SelectItem>
              <SelectItem value="90">
                {{ $t('features.dashboard.traffic.filters.last90Days') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardHeader>
      <CardContent>
        <div class="h-[250px] w-full">
          <LineChart 
            v-if="activityData.length > 0"
            :data="activityData" 
            :label="$t('features.dashboard.traffic.visits')"
          />
          <div
            v-else
            class="h-full flex flex-col items-center justify-center text-muted-foreground"
          >
            <Activity class="w-10 h-10 mb-2 opacity-50" />
            <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Row 3: Status Distribution & Top Content & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Status Distribution -->
      <Card class="col-span-1 lg:col-span-1 border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle>{{ $t('features.dashboard.stats.creator.contentStatus') }}</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="h-[250px] w-full flex items-center justify-center">
            <DoughnutChart 
              v-if="statusData.length > 0" 
              :data="statusData" 
              label-key="label"
              value-key="count"
            />
            <div
              v-else
              class="text-center text-muted-foreground"
            >
              <PieChart class="w-10 h-10 mb-2 opacity-50 mx-auto" />
              <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Top Content -->
      <Card class="col-span-1 lg:col-span-2 border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Trophy class="w-5 h-5 text-warning" />
            {{ $t('features.dashboard.stats.creator.topContent') }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Table v-if="topContent.length > 0">
            <TableHeader>
              <TableRow>
                <TableHead>{{ $t('features.dashboard.table.content') }}</TableHead>
                <TableHead class="text-right">
                  {{ $t('features.dashboard.table.views') }}
                </TableHead>
                <TableHead class="text-right">
                  {{ $t('features.dashboard.table.status') }}
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="content in topContent"
                :key="content.id"
              >
                <TableCell class="font-medium">
                  <div class="flex flex-col">
                    <span class="truncate max-w-[200px]">{{ content.title }}</span>
                    <span class="text-xs text-muted-foreground capitalize">{{ content.type }}</span>
                  </div>
                </TableCell>
                <TableCell class="text-right">
                  {{ content.views }}
                </TableCell>
                <TableCell class="text-right">
                  <Badge
                    variant="outline"
                    :class="getStatusColor(content.status)"
                    class="capitalize"
                  >
                    {{ mapStatusToLabel(content.status) }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div
            v-else
            class="h-[200px] flex flex-col items-center justify-center text-muted-foreground"
          >
            <FileText class="w-10 h-10 mb-2 opacity-50" />
            <span class="text-sm">{{ $t('features.dashboard.traffic.noData') }}</span>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Actions (Col 4) -->
      <div class="col-span-1 lg:col-span-1">
        <QuickActions />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';

import QuickActions from '@/modules/Core/components/admin/QuickActions.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Badge,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/shared/components/ui';
import DoughnutChart from '@/modules/Core/components/charts/DoughnutChart.vue';
import LineChart from '@/modules/Core/components/charts/LineChart.vue';

import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import Clock3 from 'lucide-vue-next/dist/esm/icons/clock-3.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import PenTool from 'lucide-vue-next/dist/esm/icons/pen-tool.js';
import Edit3 from 'lucide-vue-next/dist/esm/icons/pen-tool.js';
import PieChart from 'lucide-vue-next/dist/esm/icons/chart-pie.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Trophy from 'lucide-vue-next/dist/esm/icons/trophy.js';


import type { CreatorDashboardData, TrafficDataPoint, StatusDataPoint, TopContentItem } from '@/engine/types/dashboard';

const { t } = useI18n();
const authStore = useAuthStore();
const stats = ref<NonNullable<CreatorDashboardData['stats']>>({
    myContents: { total: 0, published: 0, pending: 0, draft: 0 },
    myMedia: { total: 0 }
});
const statusData = ref<StatusDataPoint[]>([]);
const activityData = ref<TrafficDataPoint[]>([]);
const topContent = ref<TopContentItem[]>([]);
const loading = ref(false);
const timeRange = ref('30'); // Default to 30 days

const mapStatusToLabel = (status: string) => {
    // Map status to translation key
    // assuming status are: published, draft, pending
    const map: Record<string, string> = {
        'published': t('features.dashboard.stats.creator.published'),
        'draft': t('features.dashboard.stats.creator.drafts'),
        'pending': t('features.dashboard.stats.creator.pendingReview'),
    };
    return map[status] || status; // Fallback to raw status
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'published': return 'bg-success/15 text-success border-0';
        case 'pending': return 'bg-warning/15 text-warning border-0';
        case 'draft': return 'bg-primary/15 text-primary border-0';
        default: return 'bg-muted text-muted-foreground border-0';
    }
};

const fetchStats = async () => {
    loading.value = true;
    try {
        const response = await api.get('/dashboard/creator', {
            params: { days: timeRange.value }
        });
        const data = parseSingleResponse<CreatorDashboardData>(response);
        
        if (data) {
            // Stats
            if (data.stats) {
                stats.value = data.stats;
            }
            
            // Content Status Chart
            if (data.charts && data.charts.myContentByStatus) {
                const rawStatus = ensureArray<{ status: string; count: number }>(data.charts.myContentByStatus);
                statusData.value = rawStatus.map(item => ({
                    label: mapStatusToLabel(item.status),
                    count: item.count
                }));
            }
            
            // Recent Activity Chart (Now Content Traffic)
            // Note: API key changed to 'contentTraffic'
            const rawTraffic = data.charts?.contentTraffic ? ensureArray<{ date: string; count: number }>(data.charts.contentTraffic) : (data.charts?.recentActivity ? ensureArray<{ date: string; count: number }>(data.charts.recentActivity) : []);
            
            activityData.value = rawTraffic.map(item => ({
                period: item.date,
                visits: item.count
            }));

            // Top Content
            if (data.topContent) {
                 topContent.value = ensureArray<TopContentItem>(data.topContent);
            }
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch creator stats:', error);
    } finally {
        loading.value = false;
    }
};

const refreshDashboard = () => {
    fetchStats();
};

onMounted(() => {
    fetchStats();
});
</script>
