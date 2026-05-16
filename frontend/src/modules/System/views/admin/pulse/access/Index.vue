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
        <div>
          <h1 class="text-2xl font-bold text-foreground">
            {{ t('features.accessJournal.title') }}
          </h1>
          <p class="text-sm text-muted-foreground mt-1">
            {{ t('features.accessJournal.description') }}
          </p>
        </div>
      </div>
      <Button
        variant="destructive"
        variant-type="outline"
        @click="clearLogs"
      >
        {{ t('modules.core.system.logs.clear') }}
      </Button>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6"
    >
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.totalLogins') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.total_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.failedLogins') }}
        </p>
        <p class="text-2xl font-semibold text-red-500">
          {{ statistics.failed_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.todayLogins') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.today_logins || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.uniqueIps') }}
        </p>
        <p class="text-2xl font-semibold text-foreground">
          {{ statistics.unique_ips_today || 0 }}
        </p>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.activeSessions') }}
        </p>
        <div class="flex items-center justify-between">
          <p class="text-2xl font-semibold text-green-500">
            {{ statistics.active_sessions || 0 }}
          </p>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-4">
        <p class="text-sm font-medium text-muted-foreground">
          {{ t('features.accessJournal.stats.suspiciousCount') }}
        </p>
        <p class="text-2xl font-semibold text-orange-500">
          {{ statistics.suspicious_count || 0 }}
        </p>
      </div>
    </div>

    <!-- Suspicious Activity Alerts -->
    <div
      v-if="suspiciousAlerts.length > 0"
      class="mb-6 space-y-4"
    >
      <Card class="border-orange-500/50 bg-orange-500/5">
        <CardHeader class="pb-2">
          <div class="flex items-center gap-2">
            <AlertTriangle class="w-5 h-5 text-orange-500" />
            <CardTitle class="text-lg text-orange-700 dark:text-orange-400">
              {{ t('features.accessJournal.alerts.title') }}
            </CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="(alert, index) in suspiciousAlerts" 
              :key="index"
              class="p-3 rounded-md border flex flex-col gap-2"
              :class="[ alert.severity === 'high' ? 'bg-red-500/10 border-red-500/30' : alert.severity === 'medium' ? 'bg-orange-500/10 border-orange-500/30' : 'bg-yellow-500/10 border-yellow-500/30' ]"
            >
              <div class="flex items-center justify-between">
                <Badge
                  :variant="alert.severity === 'high' ? 'destructive' : 'default'"
                  class="text-[10px] h-4"
                >
                  {{ t(`features.accessJournal.alerts.severity.${alert.severity}`) }}
                </Badge>
                <span class="text-[10px] text-muted-foreground">{{ alert.detected_at ? formatDate(alert.detected_at) : '' }}</span>
              </div>
              <div>
                <h4 class="text-sm font-bold flex items-center gap-1">
                  {{ t(`features.accessJournal.alerts.types.${alert.type}`) }}
                </h4>
                <p class="text-xs text-muted-foreground mt-1">
                  {{ alert.details }}
                </p>
              </div>
              <div class="flex items-center justify-between mt-auto pt-2 border-t border-border/50">
                <span class="text-xs font-medium">{{ alert.ip_address }}</span>
                <span
                  v-if="alert.user"
                  class="text-xs uppercase"
                >{{ alert.user.name }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="bg-card border border-border rounded-lg">
      <div class="px-6 py-4 border-b border-border">
        <!-- Filters Row -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex flex-wrap items-center gap-3">
            <Select
              v-model="userFilter"
              @update:model-value="fetchHistory()"
            >
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="t('features.accessJournal.filters.allUsers')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('features.accessJournal.filters.allUsers') }}
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
            <Select
              v-model="statusFilter"
              @update:model-value="fetchHistory()"
            >
              <SelectTrigger class="w-[180px]">
                <SelectValue :placeholder="t('features.accessJournal.filters.allStatus')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('features.accessJournal.filters.allStatus') }}
                </SelectItem>
                <SelectItem value="success">
                  {{ t('features.accessJournal.status.success') }}
                </SelectItem>
                <SelectItem value="failed">
                  {{ t('features.accessJournal.status.failed') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <div class="flex items-center gap-2">
              <label class="text-sm text-muted-foreground">{{ t('features.accessJournal.filters.dateFrom') }}:</label>
              <Input
                v-model="dateFrom"
                type="date"
                class="w-36"
                @change="fetchHistory"
              />
            </div>
            <div class="flex items-center gap-2">
              <label class="text-sm text-muted-foreground">{{ t('features.accessJournal.filters.dateTo') }}:</label>
              <Input
                v-model="dateTo"
                type="date"
                class="w-36"
                @change="fetchHistory"
              />
            </div>
          </div>
          <Button
            :disabled="exporting"
            @click="exportHistory"
          >
            <Download class="w-4 h-4 mr-2" />
            {{ exporting ? t('features.accessJournal.export.exporting') : t('features.accessJournal.export.button') }}
          </Button>
        </div>
      </div>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.accessJournal.messages.loading') }}
        </p>
      </div>

      <div
        v-else-if="history.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.accessJournal.messages.empty') }}
        </p>
      </div>

      <div
        v-else
        class="divide-y divide-border"
      >
        <div
          v-for="entry in history"
          :key="entry.id"
          class="px-6 py-4 hover:bg-muted/50"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <!-- Status Icon -->
              <div
                :class="[ 'w-10 h-10 rounded-full flex items-center justify-center', entry.status === 'success' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' ]"
              >
                <Check
                  v-if="entry.status === 'success'"
                  class="w-5 h-5"
                />
                <X
                  v-else
                  class="w-5 h-5"
                />
              </div>
              <!-- Details -->
              <div>
                <p class="font-medium text-foreground">
                  {{ entry.user?.name || 'Unknown User' }}
                </p>
                <p class="text-sm text-muted-foreground">
                  {{ entry.user?.email || '' }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-6">
              <!-- Device Info (parsed UA) -->
              <div
                v-if="entry.user_agent"
                class="text-right hidden md:block"
              >
                <p class="text-xs text-muted-foreground">
                  {{ parseUA(entry.user_agent).browser }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ parseUA(entry.user_agent).os }}
                </p>
              </div>
              <!-- Session Duration -->
              <div class="text-center min-w-[80px] hidden sm:block">
                <span
                  v-if="entry.status === 'success' && !entry.logout_at"
                  class="inline-flex items-center rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-600"
                >
                  Active
                </span>
                <span
                  v-else-if="entry.login_at && entry.logout_at"
                  class="text-xs text-muted-foreground"
                >
                  {{ formatDuration(entry.login_at, entry.logout_at) }}
                </span>
                <span
                  v-else
                  class="text-xs text-muted-foreground"
                >—</span>
              </div>
              <!-- IP + Time -->
              <div class="text-right">
                <p class="text-sm text-foreground">
                  {{ entry.ip_address }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ formatDate(entry.login_at) }}
                </p>
                <p
                  v-if="entry.failure_reason"
                  class="text-xs text-destructive"
                >
                  {{ entry.failure_reason }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <Pagination
        v-if="totalRecords > 0"
        :current-page="currentPage"
        :total-items="totalRecords"
        :per-page="perPage"
        class="border-none shadow-none"
        @page-change="fetchHistory"
        @update:per-page="(val) => { perPage = val; fetchHistory("1"); }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { getResponseList } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import {
    Button,
    Pagination,
    Input,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';

import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import { Card, CardHeader, CardTitle, CardContent } from '@/shared/components/ui';

interface User {
    id: string;
    name: string;
    email: string;
}

interface LoginEntry {
    id: string;
    user?: User | null;
    ip_address: string;
    user_agent?: string | null;
    status: 'success' | 'failed';
    login_at: string;
    logout_at?: string | null;
    failure_reason: string | null;
}

interface LoginStatistics {
    total_logins: number;
    failed_logins: number;
    today_logins: number;
    unique_ips_today: number;
    active_sessions: number;
    suspicious_count: number;
}

interface SuspiciousAlert {
    type: 'brute_force' | 'new_ip' | 'shared_ip';
    severity: 'high' | 'medium' | 'low';
    user?: { id: string; name: string; email: string } | null;
    ip_address: string;
    details: string;
    count?: number;
    detected_at?: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const history = ref<LoginEntry[]>([]);
const users = ref<User[]>([]);
const statistics = ref<LoginStatistics | null>(null);
const suspiciousAlerts = ref<SuspiciousAlert[]>([]);
const loading = ref(false);
const exporting = ref(false);
const userFilter = ref('');
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const perPage = ref(25);
const currentPage = ref(1);
const totalRecords = ref(0);

const fetchHistory = async (page: number = 1) : Promise<void> => {
    currentPage.value = page;
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('page', String(page));
        params.append('per_page', String(perPage.value));
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/manage/access-journal?${params.toString()}`);
        
        const payload = response.data;
        const data = getResponseList<LoginEntry>(payload);
        totalRecords.value = (payload && typeof payload === 'object' && 'total' in payload)
            ? Number((payload as { total?: number }).total ?? data.length)
            : data.length;
        history.value = data;
    } catch (error: unknown) {
        logger.error('Failed to fetch login history:', (error as Error).message);
    } finally {
        loading.value = false;
    }
};

const fetchStatistics = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/access-journal/statistics');
        statistics.value = response.data;
    } catch (error: unknown) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const fetchSuspicious = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/access-journal/suspicious');
        const payload = response.data as { alerts?: SuspiciousAlert[] } | null;
        const alerts = payload?.alerts;
        suspiciousAlerts.value = Array.isArray(alerts) ? alerts : [];
    } catch (error: unknown) {
        logger.error('Failed to fetch suspicious alerts:', error);
    }
};

const fetchUsers = async () : Promise<void> => {
    try {
        const response = await api.get('/manage/users');
        users.value = getResponseList(response.data);
    } catch (error: unknown) {
        logger.error('Failed to fetch users:', error);
        users.value = [];
    }
};

const clearLogs = async () : Promise<void> => {
    const confirmed = await confirm({
        title: t('modules.core.system.logs.actions.clear'),
        message: t('modules.core.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?',
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    try {
        await api.post('/manage/access-journal/clear');
        await fetchHistory();
        await fetchStatistics();
        toast.success.action(t('modules.core.system.logs.messages.cleared') || 'Logs cleared successfully');
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', (error as Error).message);
        toast.error.fromResponse(error);
    }
};

const exportHistory = async () : Promise<void> => {
    exporting.value = true;
    try {
        const params = new URLSearchParams();
        if (userFilter.value && userFilter.value !== 'all') params.append('user_id', userFilter.value);
        if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
        if (dateFrom.value) params.append('date_from', dateFrom.value);
        if (dateTo.value) params.append('date_to', dateTo.value);

        const response = await api.get(`/manage/access-journal/export?${params.toString()}`, {
            responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `login-history-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        toast.success.action(t('modules.core.analytics.export.success') || 'Export started');
    } catch (error: unknown) {
        logger.error('Failed to export:', (error as Error).message);
        toast.error.fromResponse(error);
    } finally {
        exporting.value = false;
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString();
};

const parseUA = (ua: string): { browser: string; os: string } => {
    let browser = 'Unknown';
    let os = 'Unknown';

    // Browser detection
    if (ua.includes('Firefox/')) browser = 'Firefox';
    else if (ua.includes('Edg/')) browser = 'Edge';
    else if (ua.includes('OPR/') || ua.includes('Opera/')) browser = 'Opera';
    else if (ua.includes('Chrome/') && ua.includes('Safari/')) browser = 'Chrome';
    else if (ua.includes('Safari/') && !ua.includes('Chrome')) browser = 'Safari';
    else if (ua.includes('bot') || ua.includes('Bot')) browser = 'Bot';

    // OS detection
    if (ua.includes('Windows NT 10')) os = 'Windows 10/11';
    else if (ua.includes('Windows')) os = 'Windows';
    else if (ua.includes('Mac OS X')) os = 'macOS';
    else if (ua.includes('Android')) os = 'Android';
    else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS';
    else if (ua.includes('Linux')) os = 'Linux';

    return { browser, os };
};

const formatDuration = (loginAt: string, logoutAt: string): string => {
    const login = new Date(loginAt).getTime();
    const logout = new Date(logoutAt).getTime();
    const diff = Math.max(0, logout - login);
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `${days}d ${hours % 24}h`;
    if (hours > 0) return `${hours}h ${minutes % 60}m`;
    return `${minutes}m`;
};

onMounted(() => {
    fetchHistory();
    fetchStatistics();
    fetchSuspicious();
    fetchUsers();
});
</script>
