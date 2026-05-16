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
          variant="ghost"
          size="sm"
          :disabled="loadingVisits"
          class="bg-muted/40 border border-border/40 hover:bg-muted/60"
          :aria-label="$t('common.actions.refresh')"
          @click="refreshDashboard"
        >
          <RefreshCw
            class="w-4 h-4 mr-2"
            :class="{ '': loadingVisits }"
          />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Contents Card -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.totalContents') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.contents?.total || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-primary font-medium">
                <FileText class="w-3 h-3" />
                <span>{{ stats.contents?.published || 0 }} {{ $t('features.dashboard.stats.published') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
              <Library class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Media Card -->
      <Card class="border-border/40 bg-card shadow-none rounded-xl">
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.mediaFiles') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.media?.total || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-success font-medium">
                <Image class="w-3 h-3" />
                <span>{{ $t('common.status.online') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-success/10 text-success">
              <FolderOpen class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Users Card -->
      <Card
        v-if="authStore.hasPermission('manage users')"
        class="border-border/40 bg-card shadow-none rounded-xl"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.totalUsers') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.users?.total || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-primary font-medium">
                <Users class="w-3 h-3" />
                <span>{{ $t('features.dashboard.stats.activeUsers') || $t('features.dashboard.stats.activeUsersFallback') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
              <UserCheck class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pending Card -->
      <Card
        v-if="authStore.hasPermission('approve content')"
        class="border-border/40 bg-card shadow-none rounded-xl"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.dashboard.stats.pendingContent') }}
              </p>
              <p class="text-3xl font-bold text-foreground">
                {{ stats.contents?.pending || 0 }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-warning font-medium">
                <AlertCircle class="w-3 h-3" />
                <span>{{ $t('features.dashboard.stats.requiresReview') }}</span>
              </div>
            </div>
            <div class="p-2.5 rounded-xl bg-warning/10 text-warning">
              <Clock3 class="w-5 h-5" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Row 2: Traffic Chart (Full Width) -->
    <div
      v-if="authStore.hasPermission('view analytics')"
      class="w-full"
    >
      <Card class="col-span-1 border-border/40 bg-card shadow-none rounded-xl">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="space-y-1">
            <h2 class="text-lg font-semibold flex items-center gap-2">
              <BarChart3 class="w-5 h-5 text-primary" />
              {{ $t('features.dashboard.traffic.title') }}
            </h2>
            <CardDescription>{{ $t('features.dashboard.traffic.overview') }}</CardDescription>
          </div>
          <!-- Time Range Filter -->
          <div class="w-[180px]">
            <Select v-model="timeRange">
              <SelectTrigger
                class="w-full"
                :aria-label="$t('features.dashboard.traffic.filters.last7Days')"
              >
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
          <div class="h-[250px] mt-4 relative">
            <div
              v-if="loadingVisits"
              class="absolute inset-0 flex items-center justify-center bg-card/50 z-20 backdrop-blur-[1px]"
            >
              <Loader2 class="h-8 w-8 text-primary animate-spin" />
            </div>
            
            <AsyncLineChart
              v-if="visitsDesktop.length > 0"
              :data="visitsDesktop"
              :label="$t('features.dashboard.traffic.visits')"
            />
            
            <div
              v-if="!loadingVisits && visitsDesktop.length === 0"
              class="h-full flex flex-col items-center justify-center text-muted-foreground space-y-2"
            >
              <AreaChart class="w-10 h-10 opacity-20" />
              <p>{{ $t('features.dashboard.traffic.noData') }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Row 3: Widgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <!-- Recent Activity -->
      <div
        v-if="authStore.hasPermission('view users')"
        class="col-span-1"
      >
        <AsyncRecentActivityWidget ref="recentActivityWidget" />
      </div>

      <!-- Email Status -->
      <div
        v-if="authStore.hasPermission('manage settings')"
        class="col-span-1"
      >
        <AsyncEmailStatusWidget />
      </div>

      <!-- System Health -->
      <div
        v-if="authStore.hasPermission('manage system')"
        class="col-span-1"
      >
        <AsyncSystemHealthWidget class="h-full" />
      </div>

      <!-- Quick Actions -->
      <div class="col-span-1">
        <AsyncQuickActions :show-recent="false" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { defineAsyncComponent, onMounted, ref, watch } from 'vue';
import { useAuthStore } from '@/modules/System/stores/auth';
import api from '@/engine/api/client';
import { parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import type { SystemStats, TrafficItem, TrafficDataPoint, DashboardData } from '@/engine/types/dashboard';

import {
    Card,
    CardHeader,
    CardDescription,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import AreaChart from 'lucide-vue-next/dist/esm/icons/chart-area.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Library from 'lucide-vue-next/dist/esm/icons/library.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import UserCheck from 'lucide-vue-next/dist/esm/icons/user-check.js';
import Clock3 from 'lucide-vue-next/dist/esm/icons/clock-3.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const authStore = useAuthStore();
const AsyncQuickActions = defineAsyncComponent(() => import('@/modules/System/components/admin/QuickActions.vue'));
const AsyncSystemHealthWidget = defineAsyncComponent(() => import('@/modules/System/components/admin/SystemHealthWidget.vue'));
const AsyncRecentActivityWidget = defineAsyncComponent(() => import('@/modules/System/components/admin/RecentActivityWidget.vue'));
const AsyncEmailStatusWidget = defineAsyncComponent(() => import('@/modules/System/components/admin/EmailStatusWidget.vue'));
const AsyncLineChart = defineAsyncComponent(() => import('@/modules/System/components/charts/LineChart.vue'));

const stats = ref<SystemStats>({
    contents: { total: 0, published: 0, pending: 0 },
    media: { total: 0 },
    users: { total: 0 },
});

const visitsDesktop = ref<TrafficDataPoint[]>([]);
const loadingVisits = ref(false);
const timeRange = ref('7'); 
const recentActivityWidget = ref<{ fetchActivities?: () => Promise<void> } | null>(null);

// Removed unused formatCurrency function



const refreshDashboard = async () => {
    if (loadingVisits.value) return;
    loadingVisits.value = true;
    try {
        await Promise.allSettled([
            fetchDashboardData(true),
            recentActivityWidget.value?.fetchActivities?.() ?? Promise.resolve()
        ]);
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to refresh dashboard:', error);
            }
        } else {
            logger.error('Failed to refresh dashboard:', error);
        }
    } finally {
        loadingVisits.value = false;
    }
};

const fetchDashboardData = async (skipLoading = false) => {
    if (!skipLoading) loadingVisits.value = true;
    try {
        let endpoint = '/dashboard/viewer';
        if (authStore.hasPermission('manage users') || authStore.hasPermission('manage settings')) {
            endpoint = '/dashboard/admin';
        } else if (authStore.hasPermission('create content') || authStore.hasPermission('edit content')) {
            endpoint = '/dashboard/creator';
        }

        const response = await api.get(endpoint, {
            params: { days: timeRange.value }
        });
        const rawData = parseSingleResponse<Record<string, unknown>>(response);

        // Handle potential double wrapping from Laravel Resources + BaseController
        const data = (rawData?.data as DashboardData) || (rawData as DashboardData);

        if (data) {
            // Update stats
            if (data.stats) {
                stats.value = {
                    contents: {
                        total: data.stats.contents?.total ?? data.stats.myContents?.total ?? 0,
                        published: data.stats.contents?.published ?? data.stats.myContents?.published ?? 0,
                        pending: data.stats.contents?.pending ?? data.stats.myContents?.pending ?? 0,
                    },
                    media: {
                        total: data.stats.media?.total ?? data.stats.myMedia?.total ?? 0,
                    },
                    users: {
                        total: data.stats.users?.total ?? 0,
                    },
                };
            }

            // Site traffic (admin): prefer contentTraffic from analytics_visits; legacy fallback userActivity
            const chartTraffic = data.charts?.contentTraffic;
            const legacyUsers = data.charts?.userActivity;
            const trafficRaw =
                Array.isArray(chartTraffic) && chartTraffic.length > 0
                    ? chartTraffic
                    : Array.isArray(legacyUsers) && legacyUsers.length > 0
                        ? legacyUsers
                        : chartTraffic ?? legacyUsers;

            if (Array.isArray(trafficRaw) && trafficRaw.length > 0) {
                const traffic = ensureArray<TrafficItem | { date: string; count: number }>(trafficRaw);
                visitsDesktop.value = traffic
                    .map(item => {
                        const period =
                            'period' in item && item.period != null && item.period !== ''
                                ? String(item.period)
                                : 'date' in item && item.date != null
                                    ? String(item.date)
                                    : '';
                        const rawVisits =
                            'visits' in item && item.visits != null ? item.visits : 'count' in item ? item.count : 0;
                        const visits = typeof rawVisits === 'number' ? rawVisits : Number(rawVisits) || 0;
                        return { period, visits };
                    })
                    .filter(v => v.period !== '');
            } else {
                visitsDesktop.value = [];
            }
        }
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'code' in error && 'response' in error) {
            const err = error as { code?: string; response?: { status?: number } };
            if (err.code !== 'ERR_CANCELED' && err.response?.status !== 401) {
                logger.error('Failed to fetch dashboard data:', error);
            }
        } else {
            logger.error('Failed to fetch dashboard data:', error);
        }
    } finally {
        if (!skipLoading) loadingVisits.value = false;
    }
};

watch(timeRange, () => {
    fetchDashboardData();
});

onMounted(() => {
    fetchDashboardData();
});
</script>
