<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <router-link
          to="/dash/journal-dashboard"
          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg"
        >
          <ArrowLeft class="w-5 h-5" />
        </router-link>
        <h1 class="text-2xl font-bold text-foreground">
          {{ t('features.activityJournal.title') }}
        </h1>
      </div>
      <Button
        variant="destructive"
        variant-type="outline"
        @click="clearLogs"
      >
        {{ t('features.system.logs.clear') }}
      </Button>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"
    >
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ClipboardList class="h-8 w-8 text-indigo-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.activityJournal.stats.total') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.total || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Clock class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.activityJournal.stats.today') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.today || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Users class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.activityJournal.stats.activeUsers') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.active_users || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <BarChart3 class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.activityJournal.stats.thisWeek') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.this_week || 0 }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-card border border-border rounded-lg">
      <div class="px-6 py-4 border-b border-border">
        <!-- Row 1: Search, Filters, Export -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
          <div class="flex flex-wrap items-center gap-3">
            <Input
              v-model="search"
              type="text"
              :placeholder="t('features.activityJournal.filters.search')"
              class="w-48"
            />
            <Select v-model="typeFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="t('features.activityJournal.filters.allTypes')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('features.activityJournal.filters.allTypes') }}
                </SelectItem>
                <SelectItem value="created">
                  {{ t('features.activityJournal.filters.types.created') }}
                </SelectItem>
                <SelectItem value="updated">
                  {{ t('features.activityJournal.filters.types.updated') }}
                </SelectItem>
                <SelectItem value="deleted">
                  {{ t('features.activityJournal.filters.types.deleted') }}
                </SelectItem>
                <SelectItem value="login">
                  {{ t('features.activityJournal.filters.types.login') }}
                </SelectItem>
                <SelectItem value="logout">
                  {{ t('features.activityJournal.filters.types.logout') }}
                </SelectItem>
                <SelectItem value="viewed">
                  {{ t('features.activityJournal.filters.types.viewed') }}
                </SelectItem>
                <SelectItem value="published">
                  {{ t('features.activityJournal.filters.types.published') }}
                </SelectItem>
                <SelectItem value="unpublished">
                  {{ t('features.activityJournal.filters.types.unpublished') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select v-model="userFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="t('features.activityJournal.filters.allUsers')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('features.activityJournal.filters.allUsers') }}
                </SelectItem>
                <SelectItem
                  v-for="user in users"
                  :key="user.id"
                  :value="String(user.id)"
                >
                  {{ user.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <Button
            :disabled="exporting"
            @click="exportLogs"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ exporting ? 'Exporting...' : 'Export CSV' }}
          </Button>
        </div>
                
        <!-- Row 2: Date Range & Per Page -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <label class="text-sm text-muted-foreground">{{ t('features.activityJournal.filters.dateFrom') || 'Dari' }}:</label>
              <Input
                v-model="dateFrom"
                type="date"
                class="w-36"
              />
            </div>
            <div class="flex items-center gap-2">
              <label class="text-sm text-muted-foreground">{{ t('features.activityJournal.filters.dateTo') || 'Sampai' }}:</label>
              <Input
                v-model="dateTo"
                type="date"
                class="w-36"
              />
            </div>
            <button
              v-if="dateFrom || dateTo"
              class="text-sm text-muted-foreground hover:text-foreground"
              @click="clearDateFilter"
            >
              Clear
            </button>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm text-muted-foreground">Per halaman:</label>
            <Select
              :model-value="String(perPage)"
              @update:model-value="perPage = Number($event); fetchLogs()"
            >
              <SelectTrigger class="w-[80px]">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="25">
                  25
                </SelectItem>
                <SelectItem value="50">
                  50
                </SelectItem>
                <SelectItem value="100">
                  100
                </SelectItem>
                <SelectItem value="200">
                  200
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.activityJournal.messages.loading') }}
        </p>
      </div>

      <div
        v-else-if="filteredLogs.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.activityJournal.messages.empty') }}
        </p>
      </div>

      <div
        v-if="filteredLogs.length > 0"
        class="divide-y divide-border"
      >
        <div
          v-for="log in filteredLogs"
          :key="log.id"
          class="px-6 py-4 hover:bg-muted"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2">
                <Badge
                  :variant="(log.action || log.type) === 'deleted' ? 'destructive' : 'default'"
                  :class="[ (log.action || log.type) === 'created' ? 'bg-green-500 hover:bg-green-600' : '', (log.action || log.type) === 'updated' ? 'bg-blue-500 hover:bg-blue-600' : '' ].filter(Boolean).join(' ')"
                >
                  {{ t(`features.activityJournal.filters.types.${log.action || log.type}`) || (log.action || log.type || t('features.activityJournal.messages.unknown')) }}
                </Badge>
                <span class="text-sm font-medium text-foreground">{{ log.description }}</span>
              </div>
              <div class="mt-1 flex items-center space-x-4 text-sm text-muted-foreground">
                <span>{{ log.user?.name || t('features.activityJournal.messages.system') }}</span>
                <span>{{ log.model_type || '' }}</span>
                <span>{{ formatDate(log.created_at) }}</span>
              </div>
              <div
                v-if="log.properties || log.changes"
                class="mt-2 text-xs text-muted-foreground"
              >
                <details class="group">
                  <summary class="cursor-pointer hover:text-foreground flex items-center gap-1">
                    <Eye class="w-3 h-3 group-open:hidden" />
                    <EyeOff class="w-3 h-3 hidden group-open:block" />
                    {{ t('features.activityJournal.messages.viewDetails') }}
                  </summary>
                                    
                  <div class="mt-2 p-3 bg-muted/50 rounded-lg border border-border overflow-x-auto">
                    <!-- Visual Diff for Updated Action -->
                    <div
                      v-if="log.action === 'updated' && log.changes?.before"
                      class="space-y-2"
                    >
                      <div
                        v-for="(val, key) in log.changes.after"
                        :key="key"
                        class="grid grid-cols-1 sm:grid-cols-2 gap-2 pb-2 border-b border-border/50 last:border-0 last:pb-0"
                      >
                        <div class="flex flex-col gap-1">
                          <span class="text-[10px] uppercase font-bold text-muted-foreground">{{ key }}</span>
                          <div
                            class="p-1 px-2 rounded bg-red-500/10 text-red-700 dark:text-red-400 line-through truncate"
                            :title="String(getRecursiveValue(log.changes.before, key))"
                          >
                            {{ formatValue(getRecursiveValue(log.changes.before, key)) }}
                          </div>
                        </div>
                        <div class="flex flex-col gap-1 pt-4 sm:pt-0">
                          <span class="hidden sm:inline-block text-[10px]">&nbsp;</span>
                          <div
                            class="p-1 px-2 rounded bg-green-500/10 text-green-700 dark:text-green-400 truncate"
                            :title="String(val)"
                          >
                            {{ formatValue(val) }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Generic JSON for other actions -->
                    <pre
                      v-else
                      class="text-[10px] font-mono whitespace-pre-wrap"
                    >{{ JSON.stringify(log.properties || log.changes, null, 2) }}</pre>
                  </div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="pagination && pagination.total > 0"
        :current-page="pagination.current_page"
        :total-items="pagination.total"
        :per-page="Number(perPage)"
        class="border-none shadow-none mt-4 px-6 py-4"
        @page-change="fetchLogs"
        @update:per-page="(val) => { perPage = val; fetchLogs(1); }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse, ensureArray, getResponseList, type PaginationData } from '@/utils/responseParser';
import {
    Button,
    Pagination,
    Input,
    Badge,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui';

import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import ClipboardList from 'lucide-vue-next/dist/esm/icons/clipboard-list.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';

interface User {
    id: number;
    name: string;
    email: string;
}

interface ActivityLog {
    id: number;
    action: string;
    type?: string;
    description: string;
    user?: User | null;
    model_type: string | null;
    properties?: Record<string, unknown>;
    changes?: {
        before?: Record<string, unknown>;
        after?: Record<string, unknown>;
    } | null;
    created_at: string;
}

interface ActivityStatistics {
    total: number;
    today: number;
    active_users: number;
    this_week: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const logs = ref<ActivityLog[]>([]);
const users = ref<User[]>([]);
const statistics = ref<ActivityStatistics | null>(null);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const userFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const pagination = ref<PaginationData | null>(null);
const exporting = ref(false);

const filteredLogs = computed(() => {
    if (!Array.isArray(logs.value)) {
        return [];
    }
    
    let filtered = logs.value;
    
    // Client-side filtering for search (server already filtered by action/user/date)
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(log => 
            log?.description?.toLowerCase().includes(searchLower) ||
            log?.model_type?.toLowerCase().includes(searchLower) ||
            log?.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchLogs = async (page: number = 1) => {
    loading.value = true;
    try {
        // Build query params for server-side filtering
        const params: Record<string, string | number> = {
            page,
            per_page: perPage.value
        };
        
        if (typeFilter.value && typeFilter.value !== 'all') params.action = typeFilter.value;
        if (userFilter.value && userFilter.value !== 'all') params.user_id = userFilter.value;
        if (dateFrom.value) params.date_from = dateFrom.value;
        if (dateTo.value) params.date_to = dateTo.value;
        if (search.value) params.search = search.value;
        
        const response = await api.get('/admin/core/activity-journal', { params });
        const { data, pagination: pag } = parseResponse<ActivityLog[]>(response);
        
        logs.value = ensureArray(data);
        pagination.value = pag;
        
        // Fetch statistics (only on first load or if needed)
        try {
            const statsResponse = await api.get('/admin/core/activity-journal/statistics');
            statistics.value = statsResponse.data?.data || statsResponse.data;
        } catch {
            // Fallback stats if endpoint fails
            statistics.value = {
                total: pagination.value?.total || logs.value.length,
                today: 0,
                this_week: 0,
                active_users: 0
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch activity logs:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        loading.value = false;
    }
};

const clearDateFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs();
};

const clearLogs = async () => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?',
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    try {
        await api.post('/admin/core/activity-journal/clear');
        toast.success.action(t('features.system.logs.messages.cleared'));
        fetchLogs();
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', (error as Error).message);
        toast.error.fromResponse(error);
    }
};

const exportLogs = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (typeFilter.value && typeFilter.value !== 'all') params.append('action', typeFilter.value);
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);
        
        const response = await api.get(`/admin/core/activity-journal/export?${params.toString()}`, {
            responseType: 'blob'
        });
        
        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `activity-logs-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success.action(t('features.analytics.export.success') || 'Export started');
    } catch (error: unknown) {
        logger.error('Failed to export activity logs:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const fetchUsers = async () => {
    try {
        const response = await api.get('/admin/core/users');
        users.value = getResponseList<User>(response.data);
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const formatDate = (date?: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const formatValue = (val: unknown): string => {
    if (val === null || val === undefined) return 'null';
    if (typeof val === 'boolean') return val ? 'true' : 'false';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};

const getRecursiveValue = (obj: Record<string, unknown> | null | undefined, key: string): unknown => {
    if (!obj) return undefined;
    if (key in obj) return obj[key];
    return undefined;
};

// Watch filters for auto-refresh
watch([typeFilter, userFilter, dateFrom, dateTo], () => {
    fetchLogs(1);
});

onMounted(() => {
    fetchLogs();
    fetchUsers();
});
</script>

