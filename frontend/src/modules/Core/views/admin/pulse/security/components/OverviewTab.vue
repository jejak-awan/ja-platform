<template>
  <div class="space-y-6">
    <!-- Maintenance Mode Banner (Full Width) -->
    <Card
      v-if="maintenanceStatus"
      :class="[ 'border-2 ', maintenanceStatus.active ? 'border-yellow-500/50 bg-yellow-500/5 dark:bg-yellow-500/10' : 'border-border' ]"
    >
      <CardContent class="p-6">
        <div class="flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
          <div class="flex items-start gap-4">
            <div
              :class="[ 'p-3 rounded-xl', maintenanceStatus.active ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/20' : 'bg-muted text-muted-foreground' ]"
            >
              <Wrench class="h-6 w-6" />
            </div>
            <div class="space-y-1">
              <h3 class="text-lg font-bold flex items-center gap-2">
                {{ $t('features.security.maintenance.title') }}
                <Badge
                  v-if="maintenanceStatus.active"
                  variant="warning"
                  class=""
                >
                  {{ $t('features.security.maintenance.status.active') }}
                </Badge>
                <Badge
                  v-else
                  variant="outline"
                  class="opacity-50"
                >
                  {{ $t('features.security.maintenance.status.inactive') }}
                </Badge>
              </h3>
              <p class="text-sm text-muted-foreground max-w-xl">
                {{ maintenanceStatus.active 
                  ? $t('features.security.maintenance.warnings.active') 
                  : $t('features.security.maintenance.description') 
                }}
              </p>
                            
              <!-- Countdown and Progress -->
              <div
                v-if="maintenanceStatus.active"
                class="flex items-center gap-4 mt-2"
              >
                <div class="flex items-center gap-1.5 text-yellow-600 dark:text-yellow-400 font-mono text-sm font-bold bg-yellow-500/10 px-2 py-0.5 rounded border border-yellow-500/20">
                  <Clock class="h-3.5 w-3.5" />
                  {{ formatRemainingTime(localRemainingSeconds) }}
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ $t('features.security.maintenance.warnings.autoResync') }}
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Panel -->
          <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Module Selection (Only when inactive) -->
            <div
              v-if="!maintenanceStatus.active"
              class="flex flex-wrap gap-2 items-center mr-2"
            >
              <Select v-model="selectedDuration">
                <SelectTrigger class="w-32 h-9">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="15">
                    {{ $t('features.security.maintenance.duration.15min') }}
                  </SelectItem>
                  <SelectItem value="30">
                    {{ $t('features.security.maintenance.duration.30min') }}
                  </SelectItem>
                  <SelectItem value="60">
                    {{ $t('features.security.maintenance.duration.1hour') }}
                  </SelectItem>
                  <SelectItem value="120">
                    {{ $t('features.security.maintenance.duration.2hours') }}
                  </SelectItem>
                  <SelectItem value="240">
                    {{ $t('features.security.maintenance.duration.4hours') }}
                  </SelectItem>
                </SelectContent>
              </Select>
                            
              <Button 
                variant="outline" 
                size="sm" 
                class="h-9 gap-2"
                @click="toggleModuleSelector"
              >
                <Settings2 class="h-4 w-4" />
                {{ $t('features.security.maintenance.modules.title', 'Modules') }}
                <Badge
                  variant="secondary"
                  class="h-5 px-1 min-w-[20px]"
                >
                  {{ selectedModules.includes('all') ? $t('features.security.maintenance.modules.all') : selectedModules.length }}
                </Badge>
              </Button>
            </div>

            <Button 
              v-if="!maintenanceStatus.active"
              variant="default"
              class="h-9 shadow-md shadow-yellow-500/10 bg-yellow-600 hover:bg-yellow-700 text-white border-transparent"
              :disabled="maintenanceActivating || selectedModules.length === 0"
              @click="handleActivate"
            >
              <Loader2
                v-if="maintenanceActivating"
                class="mr-2 h-4 w-4"
              />
              <Play
                v-else
                class="mr-2 h-4 w-4"
              />
              {{ $t('features.security.maintenance.activate') }}
            </Button>

            <Button 
              v-else
              variant="destructive"
              class="h-9 shadow-md shadow-red-500/10"
              :disabled="maintenanceActivating"
              @click="$emit('deactivate-maintenance')"
            >
              <Loader2
                v-if="maintenanceActivating"
                class="mr-2 h-4 w-4"
              />
              <Square
                v-else
                class="mr-2 h-4 w-4"
              />
              {{ $t('features.security.maintenance.deactivate') }}
            </Button>
          </div>
        </div>

        <!-- Module Selector Dropdown (Hidden by default) -->
        <div>
          <div
            v-if="showModuleSelector && !maintenanceStatus.active"
            class="mt-6 p-4 bg-muted/30 rounded-lg border border-border grid grid-cols-2 md:grid-cols-4 gap-4"
          >
            <div
              v-for="module in availableModules"
              :key="module"
              class="flex items-center space-x-2"
            >
              <Checkbox 
                :id="'module-' + module" 
                :checked="selectedModules.includes(module) || (selectedModules.includes('all') && module !== 'all')"
                :disabled="selectedModules.includes('all') && module !== 'all'"
                @update:checked="val => toggleModule(module, val)"
              />
              <Label
                :for="'module-' + module"
                class="text-sm cursor-pointer"
              >
                {{ $t(`features.security.maintenance.modules.${module}`) }}
              </Label>
            </div>
          </div>
        </div>

        <!-- Active Modules List (When active) -->
        <div
          v-if="maintenanceStatus.active"
          class="mt-4 flex flex-wrap gap-2"
        >
          <span class="text-xs text-muted-foreground mr-2 self-center font-medium">Pausing:</span>
          <Badge
            v-for="module in maintenanceStatus.modules"
            :key="module"
            variant="secondary"
            class="bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 border-yellow-500/20 text-[10px] uppercase tracking-wider"
          >
            {{ $t(`features.security.maintenance.modules.${module}`) }}
          </Badge>
        </div>
      </CardContent>
    </Card>

    <!-- Security Health and Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Health Score Gauge -->
      <Card class="lg:col-span-1 border-primary/20 bg-primary/5">
        <CardHeader>
          <CardTitle class="text-lg flex items-center gap-2">
            <Activity class="h-5 w-5 text-primary" />
            {{ $t('features.security.health.title', 'Security Health Score') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="flex flex-col items-center justify-center pb-8">
          <div class="relative w-48 h-48">
            <svg
              class="w-full h-full"
              viewBox="0 0 100 100"
            >
              <!-- Background Circle -->
              <circle
                class="text-muted-foreground/10"
                stroke-width="8"
                stroke="currentColor"
                fill="transparent"
                r="40"
                cx="50"
                cy="50"
              />
              <!-- Foreground Circle (Gauge) -->
              <circle
                :class="{ 'text-green-500': securityHealth.assessment.score >= 80, 'text-yellow-500': securityHealth.assessment.score >= 50 && securityHealth.assessment.score < 80, 'text-red-500': securityHealth.assessment.score < 50 }"
                stroke-width="8"
                :stroke-dasharray="251.2"
                :stroke-dashoffset="251.2 - (251.2 * securityHealth.assessment.score) / 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="transparent"
                r="40"
                cx="50"
                cy="50"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <span class="text-4xl font-black tracking-tighter">{{ securityHealth.assessment.score }}</span>
              <span class="text-xs uppercase font-bold tracking-widest text-muted-foreground">{{ securityHealth.assessment.level }}</span>
            </div>
          </div>
          <div class="mt-4 text-center">
            <Badge
              :variant="securityHealth.assessment.score >= 80 ? 'success' : (securityHealth.assessment.score >= 50 ? 'warning' : 'destructive')"
              class="px-4 py-1"
            >
              {{ securityHealth.assessment.status }}
            </Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Attack Trend Chart -->
      <Card class="lg:col-span-2 overflow-hidden">
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle class="text-lg flex items-center gap-2">
            <TrendingUp class="h-5 w-5 text-primary" />
            {{ $t('features.security.trends.title', 'Attack Trends (24h)') }}
          </CardTitle>
          <div class="flex gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              class="h-8 gap-2 border-primary/20 hover:bg-primary/5 text-primary"
              @click="$emit('test-notification')"
            >
              <Send class="h-3.5 w-3.5" />
              {{ $t('features.security.notifications.test', 'Test Alert') }}
            </Button>
          </div>
        </CardHeader>
        <CardContent class="h-[200px] p-0 pr-4">
          <LineChart
            :data="chartData"
            :label="$t('features.security.trends.events')"
          />
        </CardContent>
      </Card>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-4 gap-4"
    >
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-500/10 rounded-lg">
              <ShieldAlert class="h-6 w-6 text-red-500" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.security.statistics.events') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ statistics.total_events || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-500/10 rounded-lg">
              <ShieldX class="h-6 w-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.security.statistics.blockedIps') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ blocklistCount }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-3 bg-orange-500/10 rounded-lg">
              <UserX class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.security.statistics.failedLogins') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ statistics.failed_logins || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-500/10 rounded-lg">
              <ShieldCheck class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.security.whitelist.title') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ whitelistCount }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- KPI Snapshot -->
    <div
      v-if="securityKpi"
      class="grid grid-cols-1 md:grid-cols-4 gap-4"
    >
      <Card>
        <CardContent class="p-6">
          <p class="text-sm font-medium text-muted-foreground">
            {{ $t('features.security.kpi.passRate', 'Drill Pass Rate (30d)') }}
          </p>
          <p class="text-2xl font-bold text-foreground mt-1">
            {{ formatNumber(securityKpi.drills.pass_rate_percent, 2) }}%
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ securityKpi.drills.pass_count }}/{{ securityKpi.drills.count }} {{ $t('features.security.kpi.drills', 'drills') }}
          </p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <p class="text-sm font-medium text-muted-foreground">
            {{ $t('features.security.kpi.avgRto', 'Avg RTO') }}
          </p>
          <p class="text-2xl font-bold text-foreground mt-1">
            {{ formatNumber(securityKpi.drills.avg_rto_seconds, 3) }}s
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ $t('features.security.kpi.period', 'Period') }}: {{ securityKpi.period_days }}d
          </p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <p class="text-sm font-medium text-muted-foreground">
            {{ $t('features.security.kpi.avgRpo', 'Avg RPO') }}
          </p>
          <p class="text-2xl font-bold text-foreground mt-1">
            {{ formatNumber(securityKpi.drills.avg_rpo_minutes, 2) }}m
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ $t('features.security.kpi.targetNote', 'Lower is better') }}
          </p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <p class="text-sm font-medium text-muted-foreground">
            {{ $t('features.security.kpi.noiseRate', 'Detection Noise Rate') }}
          </p>
          <p class="text-2xl font-bold text-foreground mt-1">
            {{ formatNumber(securityKpi.detection.noise_rate_percent, 2) }}%
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            {{ securityKpi.detection.info_signals }} {{ $t('features.security.kpi.infoSignals', 'info') }} / {{ securityKpi.detection.critical_signals }} {{ $t('features.security.kpi.criticalSignals', 'critical') }}
          </p>
        </CardContent>
      </Card>
    </div>

    <!-- IP Management -->
    <Card>
      <CardHeader>
        <CardTitle class="text-lg">
          {{ $t('features.security.ipManagement.title') }}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <Label>{{ $t('features.security.ipManagement.block.label') }}</Label>
            <div class="flex space-x-2">
              <Input
                v-model="ipToBlock"
                type="text"
                :placeholder="$t('features.security.ipManagement.block.placeholder')"
              />
              <Button
                variant="destructive"
                :disabled="!ipToBlock.trim()"
                @click="handleBlockIP"
              >
                {{ $t('features.security.ipManagement.block.button') }}
              </Button>
            </div>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.security.ipManagement.check.label') }}</Label>
            <div class="flex space-x-2">
              <Input
                v-model="ipToCheck"
                type="text"
                :placeholder="$t('features.security.ipManagement.check.placeholder')"
              />
              <Button
                :disabled="!ipToCheck.trim()"
                @click="handleCheckIP"
              >
                {{ $t('features.security.ipManagement.check.button') }}
              </Button>
            </div>
            <div
              v-if="ipStatus"
              class="mt-2"
            >
              <Badge
                :variant="ipStatus.is_blocked ? 'destructive' : 'default'"
                class="w-full justify-center py-2 bg-muted/50 text-foreground"
              >
                {{ $t('features.security.ipManagement.status.label') }}: {{ ipStatus.is_blocked ? $t('features.security.ipManagement.status.blocked') : $t('features.security.ipManagement.status.allowed') }}
              </Badge>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Security Logs Table -->
    <Card>
      <CardHeader class="flex flex-row items-center justify-between pb-4">
        <div class="flex items-center space-x-4">
          <CardTitle class="text-lg">
            {{ $t('features.security.logs.title') }}
          </CardTitle>
          <Badge
            v-if="selectedLogIds.length > 0"
            variant="secondary"
            class="bg-muted/50 text-foreground"
          >
            {{ $t('features.security.bulkActions.selected', { count: selectedLogIds.length }) }}
          </Badge>
          <Button
            v-if="selectedLogIds.length > 0"
            variant="destructive"
            size="sm"
            @click="$emit('bulk-block', selectedLogIds)"
          >
            {{ $t('features.security.bulkActions.blockSelected') }}
          </Button>
        </div>
        <div class="flex items-center space-x-2">
          <Select v-model="logFilter">
            <SelectTrigger class="w-48">
              <SelectValue :placeholder="$t('features.security.logs.all')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('features.security.logs.all') }}
              </SelectItem>
              <SelectItem value="login_failed">
                {{ $t('features.security.logs.failedLogin') }}
              </SelectItem>
              <SelectItem value="ip_blocked">
                {{ $t('features.security.logs.blockedIp') }}
              </SelectItem>
              <SelectItem value="suspicious_activity">
                {{ $t('features.security.logs.suspiciousActivity') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <div class="relative w-64">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="logSearch"
              :placeholder="$t('features.security.logs.search')"
              class="pl-10"
            />
          </div>
        </div>
      </CardHeader>
      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="t('features.security.logs.empty')"
        />
      </CardContent>
      <Pagination
        v-if="filteredLogs.length > 0"
        v-model:per-page="perPage"
        :current-page="currentPage"
        :total-items="filteredLogs.length"
        class="border-none shadow-none px-6 py-4"
        @page-change="(val: number) => currentPage = val"
      />
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, type SortingState } from '@tanstack/vue-table';
import {
    Card, CardHeader, CardTitle, CardContent,
    Button, Input, Label, Badge, Checkbox, DataTable, Pagination,
    Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';

import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import ShieldX from 'lucide-vue-next/dist/esm/icons/shield-x.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import UserX from 'lucide-vue-next/dist/esm/icons/user-x.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Settings2 from 'lucide-vue-next/dist/esm/icons/settings-2.js';
import Wrench from 'lucide-vue-next/dist/esm/icons/wrench.js';
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import LineChart from '@/modules/Core/components/charts/LineChart.vue';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import Send from 'lucide-vue-next/dist/esm/icons/send.js';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Log {
    id: number;
    event_type: string;
    ip_address: string;
    user_id?: number | null;
    user?: User | null;
    details: string;
    created_at: string;
}

interface Statistics {
    total_events: number;
    failed_logins: number;
    blocked_ips: number;
}

interface SecurityKpiData {
    period_days: number;
    drills: {
        count: number;
        pass_count: number;
        pass_rate_percent: number;
        avg_rto_seconds: number;
        avg_rpo_minutes: number;
    };
    detection: {
        info_signals: number;
        critical_signals: number;
        noise_rate_percent: number;
    };
}

interface IpStatus {
    is_blocked: boolean;
    reason?: string | null;
}

interface TrendDataset {
    label: string;
    data: number[];
    borderColor?: string;
    backgroundColor?: string;
}

interface MaintenanceStatusData {
    active: boolean;
    modules: string[];
    started_at: string | null;
    expires_at: string | null;
    remaining_seconds: number;
}

const props = defineProps<{
    logs: Log[];
    statistics: Statistics | null;
    securityKpi: SecurityKpiData | null;
    loading: boolean;
    blocklistCount: number;
    whitelistCount: number;
    maintenanceStatus: MaintenanceStatusData | null;
    maintenanceLoading: boolean;
    maintenanceActivating: boolean;
    securityHealth: {
        assessment: { score: number; level: string; status: string; details: string[] };
        trend: { labels: string[]; datasets: TrendDataset[] };
    };
}>();

const emit = defineEmits<{
    'block-ip': [ip: string];
    'check-ip': [ip: string];
    'block-from-log': [ip: string];
    'bulk-block': [ips: string[]];
    'activate-maintenance': [modules: string[], duration: number];
    'deactivate-maintenance': [];
    'test-notification': [];
}>();

const { t } = useI18n();

// Local state
const ipToBlock = ref('');
const ipToCheck = ref('');
const ipStatus = ref<IpStatus | null>(null);
const logFilter = ref('all');
const logSearch = ref('');
const selectedLogIds = ref<string[]>([]);
const currentPage = ref(1);
const perPage = ref(25);
const sorting = ref<SortingState>([]);

// Maintenance Local State
const showModuleSelector = ref(false);
const selectedModules = ref<string[]>(['all']);
const selectedDuration = ref('60');
const availableModules = ['all', 'waf', 'scanner', 'shield', 'integrity', 'autotune', 'threatintel', 'notifications'];

const toggleModuleSelector = () => {
    showModuleSelector.value = !showModuleSelector.value;
};

const toggleModule = (module: string, checked: boolean) => {
    if (module === 'all') {
        selectedModules.value = checked ? ['all'] : [];
    } else {
        // Remove 'all' if we're picking specific modules
        selectedModules.value = selectedModules.value.filter(m => m !== 'all');
        
        if (checked) {
            if (!selectedModules.value.includes(module)) {
                selectedModules.value.push(module);
            }
        } else {
            selectedModules.value = selectedModules.value.filter(m => m !== module);
        }
    }
};

const handleActivate = () => {
    if (selectedModules.value.length === 0) return;
    emit('activate-maintenance', selectedModules.value, parseInt(selectedDuration.value));
    showModuleSelector.value = false;
};

const formatRemainingTime = (seconds: number) => {
    if (seconds <= 0) return '00:00';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

// Auto-refresh countdown (local purely for UI)
// Use a local ref to avoid mutating the prop directly
const localRemainingSeconds = ref(props.maintenanceStatus?.remaining_seconds ?? 0);
let timerInterval: ReturnType<typeof setInterval> | null = null;

watch(() => props.maintenanceStatus?.remaining_seconds, (val) => {
    localRemainingSeconds.value = val ?? 0;
});

watch(() => props.maintenanceStatus?.active, (active) => {
    if (active) {
        localRemainingSeconds.value = props.maintenanceStatus?.remaining_seconds ?? 0;
        if (!timerInterval) {
            timerInterval = setInterval(() => {
                if (localRemainingSeconds.value > 0) {
                    localRemainingSeconds.value--;
                }
            }, 1000);
        }
    } else {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }
}, { immediate: true });

// Chart configuration
const chartData = computed(() => {
    const trend = props.securityHealth?.trend;
    if (!trend || !trend.labels || !Array.isArray(trend.labels)) return [];
    
    const datasets = trend.datasets || [];
    const mainDataset = datasets[0] || { label: '', data: [] };
    
    return trend.labels.map((label: string, index: number) => ({
        period: label,
        visits: mainDataset.data?.[index] || 0
    }));
});

// Watch for filter changes to reset pagination
watch([logFilter, logSearch], () => {
    currentPage.value = 1;
});

const handleBlockIP = () => {
    if (ipToBlock.value.trim()) {
        emit('block-ip', ipToBlock.value);
        ipToBlock.value = '';
    }
};

const handleCheckIP = async () => {
    if (ipToCheck.value.trim()) {
        emit('check-ip', ipToCheck.value);
        // Parent will update ipStatus via exposed method or callback
    }
};

// Filtered logs
const filteredLogs = computed(() => {
    let filtered = props.logs;
    
    if (logFilter.value && logFilter.value !== 'all') {
        filtered = filtered.filter(log => log.event_type === logFilter.value);
    }
    
    if (logSearch.value) {
        const searchLower = logSearch.value.toLowerCase();
        filtered = filtered.filter(log => 
            log.ip_address?.toLowerCase().includes(searchLower) ||
            log.details?.toLowerCase().includes(searchLower) ||
            log.user?.name?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

// Paginated data
const paginatedLogs = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = Math.min(start + perPage.value, filteredLogs.value.length);
    return filteredLogs.value.slice(start, end);
});

// Helper functions
const getEventLabel = (eventType: string): string => {
    const key = `features.security.logs.eventTypes.${eventType}`;
    const translated = t(key);
    return translated !== key ? translated : eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getEventClass = (eventType: string): string => {
    const classes: Record<string, string> = {
        'login_failed': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
        'login_success': 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20',
        'ip_blocked': 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20',
        'ip_unblocked': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
        'suspicious_activity': 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
    };
    return classes[eventType] || 'bg-muted/50 text-muted-foreground border border-border';
};

const formatDate = (date: string | null | undefined): string => {
    if (!date) return '-';
    return new Date(date).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatNumber = (value: number | null | undefined, fractionDigits = 2): string => {
    if (value == null || Number.isNaN(value)) return '-';
    return Number(value).toFixed(fractionDigits);
};

// TanStack Table
const columnHelper = createColumnHelper<Log>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: () => h(Checkbox, {
            checked: selectedLogIds.value.length === paginatedLogs.value.length && paginatedLogs.value.length > 0,
            'onUpdate:checked': (val: boolean) => {
                selectedLogIds.value = val ? paginatedLogs.value.map(log => log.ip_address) : [];
            }
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: selectedLogIds.value.includes(row.original.ip_address),
            'onUpdate:checked': (val: boolean) => {
                if (val) {
                    selectedLogIds.value.push(row.original.ip_address);
                } else {
                    selectedLogIds.value = selectedLogIds.value.filter(id => id !== row.original.ip_address);
                }
            }
        })
    }),
    columnHelper.accessor('event_type', {
        header: t('features.security.logs.table.event'),
        cell: ({ row }) => h(Badge, { class: getEventClass(row.original.event_type), variant: 'outline' }, () => getEventLabel(row.original.event_type))
    }),
    columnHelper.accessor('ip_address', {
        header: t('features.security.logs.table.ip'),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address)
    }),
    columnHelper.accessor(row => row.user?.name, {
        id: 'user',
        header: t('features.security.logs.table.user'),
        cell: ({ row }) => h('span', { class: 'text-sm' }, row.original.user?.name || '-')
    }),
    columnHelper.accessor('details', {
        header: t('features.security.logs.table.details'),
        cell: ({ row }) => h('span', { class: 'max-w-xs truncate text-muted-foreground text-sm', title: row.original.details }, row.original.details)
    }),
    columnHelper.accessor('created_at', {
        header: t('features.security.logs.table.date'),
        cell: ({ row }) => h('span', { class: 'text-muted-foreground whitespace-nowrap text-sm' }, formatDate(row.original.created_at))
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, t('features.security.logs.table.actions')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h(Button, {
                variant: 'ghost',
                size: 'sm',
                class: 'text-destructive hover:text-destructive hover:bg-destructive/10 h-8',
                onClick: () => emit('block-from-log', row.original.ip_address)
            }, () => t('features.security.logs.actions.blockIp'))
        ])
    })
];

const table = useVueTable({
    get data() { return paginatedLogs.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

// Expose methods for parent
defineExpose({
    setIpStatus: (status: IpStatus | null) => { ipStatus.value = status; },
    clearSelection: () => { selectedLogIds.value = []; }
});
</script>
