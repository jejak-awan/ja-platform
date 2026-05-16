<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          as-child
        >
          <router-link to="/dash/journal-dashboard">
            <ArrowLeft class="w-5 h-5" />
          </router-link>
        </Button>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.security.title') }}
        </h1>
      </div>
      <div class="flex items-center space-x-2">
        <Button 
          v-if="showClearButton" 
          variant="destructive" 
          @click="handleClearLogs"
        >
          <Trash2 class="w-4 h-4 mr-2" />
          {{ $t('modules.core.system.logs.clear') }}
        </Button>
        <Button
          :disabled="loading"
          @click="refreshAll"
        >
          <Loader2
            v-if="loading"
            class="w-4 h-4 mr-2"
          />
          <RefreshCw
            v-else
            class="w-4 h-4 mr-2"
          />
          <span>{{ $t('common.actions.refresh') }}</span>
        </Button>
      </div>
    </div>

    <div class="w-full">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger
              value="overview"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <BarChart3 class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.overview') }}
            </TabsTrigger>
            <TabsTrigger
              value="blocklist"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldX class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.blocklist') }}
            </TabsTrigger>
            <TabsTrigger
              value="whitelist"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldCheck class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.whitelist') }}
            </TabsTrigger>
            <TabsTrigger
              value="csp-reports"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <FileWarning class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.cspReports') }}
            </TabsTrigger>
            <TabsTrigger
              value="slow-queries"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <Timer class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.slowQueries') }}
            </TabsTrigger>
            <TabsTrigger
              value="vulnerabilities"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldAlert class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.vulnerabilities') }}
            </TabsTrigger>
            <TabsTrigger
              value="shield-journal"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <ShieldCheck class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.shieldJournal') }}
            </TabsTrigger>
            <TabsTrigger
              value="threat-analysis"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <Activity class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.threatAnalysis') }}
            </TabsTrigger>
            <TabsTrigger
              value="file-integrity"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <FileCheck class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.fileIntegrity') }}
            </TabsTrigger>
            <TabsTrigger
              value="settings"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <SettingsIcon class="w-4 h-4 mr-2" />
              {{ $t('features.security.tabs.settings') }}
            </TabsTrigger>
          </TabsList>
        </div>

        <!-- Overview Tab -->
        <TabsContent value="overview">
          <OverviewTab
            ref="overviewTabRef"
            :logs="logs"
            :statistics="statistics"
            :security-kpi="securityKpi"
            :loading="loading"
            :blocklist-count="blocklist.length"
            :whitelist-count="whitelist.length"
            :maintenance-status="maintenanceStatus"
            :maintenance-loading="maintenanceLoading"
            :maintenance-activating="maintenanceActivating"
            :security-health="securityHealth"
            @block-ip="blockIP"
            @check-ip="checkIPStatus"
            @block-from-log="blockIPFromLog"
            @bulk-block="bulkBlockFromLogs"
            @activate-maintenance="activateMaintenance"
            @deactivate-maintenance="deactivateMaintenance"
            @test-notification="testNotification"
          />
        </TabsContent>

        <!-- Blocklist Tab -->
        <TabsContent value="blocklist">
          <BlocklistTab
            ref="blocklistTabRef"
            :blocklist="blocklist"
            :loading="loading"
            @remove="removeFromBlocklist"
            @move-to-whitelist="moveToWhitelist"
            @bulk-unblock="bulkUnblock"
          />
        </TabsContent>

        <!-- Whitelist Tab -->
        <TabsContent value="whitelist">
          <WhitelistTab
            ref="whitelistTabRef"
            :whitelist="whitelist"
            :loading="loading"
            @add="addToWhitelist"
            @remove="removeFromWhitelist"
            @bulk-remove="bulkRemoveWhitelist"
          />
        </TabsContent>

        <!-- CSP Reports Tab -->
        <TabsContent value="csp-reports">
          <CspReportsTab
            :reports="cspReports"
            :stats="cspStats"
            :loading="cspLoading"
            :pagination="cspPagination"
            :filters="cspFilters"
            @refresh="fetchCspReports"
            @apply-filters="applyCspFilters"
            @reset-filters="resetCspFilters"
            @bulk-action="cspBulkAction"
            @page-change="(val) => { cspFilters.page = val; fetchCspReports(); }"
            @per-page-change="(val) => { cspFilters.per_page = val; cspFilters.page = 1; fetchCspReports(); }"
          />
        </TabsContent>

        <!-- Slow Queries Tab -->
        <TabsContent value="slow-queries">
          <SlowQueriesTab
            :queries="slowQueries"
            :stats="slowQueryStats"
            :loading="slowQueryLoading"
            :pagination="slowQueryPagination"
            :filters="slowQueryFilters"
            @refresh="fetchSlowQueries"
            @apply-filters="applySlowQueryFilters"
            @reset-filters="resetSlowQueryFilters"
            @page-change="(val) => { slowQueryFilters.page = val; fetchSlowQueries(); }"
            @per-page-change="(val) => { slowQueryFilters.per_page = val; slowQueryFilters.page = 1; fetchSlowQueries(); }"
          />
        </TabsContent>

        <!-- Vulnerabilities Tab -->
        <TabsContent value="vulnerabilities">
          <VulnerabilitiesTab
            :vulnerabilities="vulnerabilities"
            :stats="vulnStats"
            :loading="vulnLoading"
            :audit-running="auditRunning"
            :pagination="vulnPagination"
            :filters="vulnFilters"
            @refresh="fetchVulnerabilities"
            @run-audit="runDependencyAudit"
            @apply-filters="applyVulnFilters"
            @reset-filters="resetVulnFilters"
            @update-status="updateVulnStatus"
            @page-change="(val) => { vulnFilters.page = val; fetchVulnerabilities(); }"
            @per-page-change="(val) => { vulnFilters.per_page = val; vulnFilters.page = 1; fetchVulnerabilities(); }"
          />
        </TabsContent>

        <!-- Shield Journal Tab -->
        <TabsContent value="shield-journal">
          <ShieldJournalTab
            :logs="shieldLogs"
            :stats="shieldStats"
            :loading="shieldLoading"
            :pagination="shieldPagination"
            @refresh="fetchShieldLogs"
            @page-change="(val) => { shieldPage = val; fetchShieldLogs(); }"
            @block-ip="blockIP"
          />
        </TabsContent>

        <!-- Threat Analysis Tab -->
        <TabsContent value="threat-analysis">
          <ThreatAnalysisTab
            :analysis="threatAnalysisData"
            :auto-tune-logs="autoTuneLogsData"
            :loading="threatLoading"
            @refresh="fetchThreatAnalysis"
          />
        </TabsContent>

        <!-- File Integrity Tab -->
        <TabsContent value="file-integrity">
          <FileIntegrityTab
            :integrity="integrityData"
            :loading="integrityLoading"
            :resync-submitting="resyncSubmitting"
            :resync-cooldown-seconds="resyncCooldownSeconds"
            @refresh="fetchFileIntegrity"
            @run-check="runIntegrityCheck"
            @resync="resyncIntegrityBaseline"
          />
        </TabsContent>

        <!-- Settings Tab -->
        <TabsContent value="settings">
          <SettingsTab
            :settings="securitySettings"
            :saving="settingsSaving"
            @save="saveSecuritySettings"
          />
        </TabsContent>
      </Tabs>
    </div>

    <Dialog
      :open="resyncDialogOpen"
      @update:open="(v) => { resyncDialogOpen = v; }"
    >
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Authorize Integrity Update</DialogTitle>
          <DialogDescription>
            Isi alasan perubahan terotorisasi untuk audit trail. Sistem akan memperbarui baseline file integrity.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-2">
          <div class="space-y-2">
            <Label for="integrity-reason">Alasan Perubahan (Wajib)</Label>
            <Textarea
              id="integrity-reason"
              v-model="resyncReason"
              rows="4"
              placeholder="Contoh: Deploy hotfix login + update middleware security pada 2026-04-22 oleh tim infra."
            />
          </div>

          <label class="flex items-start gap-3 rounded-md border border-border/50 p-3">
            <Checkbox
              :checked="resyncClearHistory"
              @update:checked="(v) => { resyncClearHistory = v === true; }"
            />
            <div class="space-y-1">
              <p class="text-sm font-medium">
                Bersihkan catatan pelanggaran integrity lama
              </p>
              <p class="text-xs text-muted-foreground">
                Direkomendasikan setelah update terotorisasi agar dashboard merefleksikan baseline terbaru.
              </p>
            </div>
          </label>
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            :disabled="resyncSubmitting"
            @click="resyncDialogOpen = false"
          >
            Batal
          </Button>
          <Button
            :disabled="resyncSubmitting"
            @click="submitIntegrityResync"
          >
            <Loader2
              v-if="resyncSubmitting"
              class="w-4 h-4 mr-2"
            />
            Authorize & Re-sync
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse, getResponseList, getResponseObject } from '@/shared/utils/responseParser';
import {
    Tabs, TabsList, TabsTrigger, TabsContent, Button,
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Label, Textarea, Checkbox
} from '@/shared/components/ui';

// Tab Components
import OverviewTab from './components/OverviewTab.vue';
import BlocklistTab from './components/BlocklistTab.vue';
import WhitelistTab from './components/WhitelistTab.vue';
import CspReportsTab from './components/CspReportsTab.vue';
import SlowQueriesTab from './components/SlowQueriesTab.vue';
import VulnerabilitiesTab from './components/VulnerabilitiesTab.vue';
import ShieldJournalTab from './components/ShieldJournalTab.vue';
import ThreatAnalysisTab from './components/ThreatAnalysisTab.vue';
import FileIntegrityTab from './components/FileIntegrityTab.vue';
import SettingsTab from './components/SettingsTab.vue';

// Icons
import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import ShieldX from 'lucide-vue-next/dist/esm/icons/shield-x.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import FileWarning from 'lucide-vue-next/dist/esm/icons/file-x.js';
import Timer from 'lucide-vue-next/dist/esm/icons/timer.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import FileCheck from 'lucide-vue-next/dist/esm/icons/file-check.js';
import SettingsIcon from 'lucide-vue-next/dist/esm/icons/settings.js';

import type { ShieldLog, ShieldStats, PaginationInfo as SecurityPaginationInfo } from '@/engine/types/security';

// Types
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

interface SecurityKpi {
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

interface IpManagementItem {
    id: number | string;
    ip_address: string;
    reason?: string | null;
    creator?: User | null;
    created_at: string;
}

interface IpStatus {
    is_blocked: boolean;
    reason?: string | null;
}

interface CspReport {
    id: number;
    violated_directive: string;
    blocked_uri: string | null;
    document_uri: string;
    ip_address: string;
    status: string;
    created_at: string;
}

interface CspStats {
    total?: number;
    new?: number;
    by_directive?: { violated_directive: string; count: number }[];
    recent_trend?: { date: string; count: number }[];
}

interface SlowQuery {
    id: number;
    route: string | null;
    duration: number;
    user_id?: number | null;
    user?: User | null;
    query: string;
    created_at: string;
}

interface SlowQueryStats {
    total?: number;
    avg_duration?: number;
    max_duration?: number;
    today?: number;
}

interface Vulnerability {
    id: number;
    package_name: string;
    version: string;
    severity: string;
    cve: string | null;
    source: string;
    status: string;
}

interface VulnStats {
    total: number;
    critical: number;
    high: number;
    medium: number;
    low: number;
}

interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

// Refs for child components
const overviewTabRef = ref<InstanceType<typeof OverviewTab> | null>(null);
const blocklistTabRef = ref<InstanceType<typeof BlocklistTab> | null>(null);
const whitelistTabRef = ref<InstanceType<typeof WhitelistTab> | null>(null);

// Core Data
const logs = ref<Log[]>([]);
const statistics = ref<Statistics | null>(null);
const securityKpi = ref<SecurityKpi | null>(null);
const blocklist = ref<IpManagementItem[]>([]);
const whitelist = ref<IpManagementItem[]>([]);
const loading = ref(false);
const activeTab = ref('overview');

// CSP Reports
const cspReports = ref<CspReport[]>([]);
const cspStats = ref<CspStats | null>(null);
const cspLoading = ref(false);
const cspPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Slow Queries
const slowQueries = ref<SlowQuery[]>([]);
const slowQueryStats = ref<SlowQueryStats | null>(null);
const slowQueryLoading = ref(false);
const slowQueryPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Vulnerabilities
const vulnerabilities = ref<Vulnerability[]>([]);
const vulnStats = ref<VulnStats>({ total: 0, critical: 0, high: 0, medium: 0, low: 0 });
const vulnLoading = ref(false);
const auditRunning = ref(false);
const vulnPagination = ref<PaginationInfo>({ total: 0, current_page: 1, last_page: 1 });

// Shield Journal
const shieldLogs = ref<ShieldLog[]>([]);
const shieldStats = ref<ShieldStats>({ verifications: 0, failures: 0, honeypot: 0, scannersBlocked: 0, extensionsBlocked: 0, currentDifficulty: 4, isScaling: false });
const shieldLoading = ref(false);
const shieldPagination = ref<SecurityPaginationInfo>({ total: 0, current_page: 1, last_page: 1 });
const shieldPage = ref(1);

// Threat Analysis
const threatAnalysisData = ref(null);
const autoTuneLogsData = ref([]);
const threatLoading = ref(false);

// File Integrity
const integrityData = ref(null);
const integrityLoading = ref(false);
const resyncDialogOpen = ref(false);
const resyncSubmitting = ref(false);
const resyncReason = ref('');
const resyncClearHistory = ref(true);
const resyncCooldownUntil = ref(0);
const resyncCooldownSeconds = computed(() => Math.max(0, Math.ceil((resyncCooldownUntil.value - Date.now()) / 1000)));

// Security Settings
const securitySettings = ref({ 
    security_log_retention_days: 90, 
    activity_log_retention_days: 90,
    login_history_retention_days: 180,
    security_autotune_frequency: 'weekly' 
});
const settingsSaving = ref(false);
const settingsLoaded = ref(false);

// Maintenance Mode
const maintenanceStatus = ref({
    active: false,
    modules: [],
    started_at: null,
    expires_at: null,
    remaining_seconds: 0
});
const maintenanceLoading = ref(false);
const maintenanceActivating = ref(false);

// Security health and trend
const securityHealth = ref({
    assessment: { score: 100, level: 'safe', status: 'Healthy', details: [] },
    trend: { labels: [] as string[], datasets: [] as { label: string; data: number[]; borderColor?: string; backgroundColor?: string }[] }
});

// Filters state
const cspFilters = reactive({ status: 'new', directive: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const slowQueryFilters = reactive({ route: '', min_duration: '', date_from: '', date_to: '', page: 1, per_page: 50 });
const vulnFilters = reactive({ source: 'all', severity: 'all', status: 'all', package: '', page: 1, per_page: 50 });

// ========================================
// CORE FETCH FUNCTIONS
// ========================================
const fetchShieldLogs = async (): Promise<void> => {
    shieldLoading.value = true;
    try {
        const response = await api.get('/manage/security/shield/journal', { params: { page: shieldPage.value } });
        const result = response.data;
        shieldLogs.value = (result.data as ShieldLog[]) || [];
        shieldPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch shield logs:', _error);
    } finally {
        shieldLoading.value = false;
    }
};

const fetchShieldStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/shield/stats');
        shieldStats.value = (response.data as ShieldStats) || { verifications: 0, failures: 0, honeypot: 0, scannersBlocked: 0, extensionsBlocked: 0, currentDifficulty: 4, isScaling: false };
    } catch (_error: unknown) {
        logger.error('Failed to fetch shield stats:', _error);
    }
};
const fetchLogs = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/security/journal', { params: { per_page: 100 } });
        const { data } = parseResponse<Log[]>(response);
        logs.value = ensureArray(data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch logs:', _error);
    } finally {
        loading.value = false;
    }
};

const clearLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('modules.core.system.logs.actions.clear'),
        message: t('modules.core.system.logs.confirm.clear'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });
    if (!confirmed) return;
    try {
        await api.delete('/manage/security/journal');
        toast.success.action(t('modules.core.system.logs.messages.cleared'));
        fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to clear logs:', _error);
        toast.error.fromResponse(_error);
    }
};

const clearShieldLogs = async (): Promise<void> => {
    const confirmed = await confirm({
        title: t('common.actions.clear'),
        message: t('common.dialogs.confirmDelete', 'Are you sure you want to clear all shield logs?'),
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });
    if (!confirmed) return;

    try {
        await api.post('/manage/security/shield/clear');
        toast.success.default(t('features.security.logs.cleared', 'Shield logs cleared successfully'));
        fetchShieldLogs();
    } catch (_error) {
        toast.error.default(t('common.errors.generic')); // _error
    }
};

const handleClearLogs = () => {
    if (activeTab.value === 'shield-journal') {
        clearShieldLogs();
    } else {
        clearLogs();
    }
};

const showClearButton = computed(() => {
    return ['overview', 'shield-journal'].includes(activeTab.value);
});

const fetchStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/stats');
        statistics.value = parseSingleResponse<Statistics>(response) as Statistics || null;
    } catch (_error: unknown) {
        logger.error('Failed to fetch stats:', _error);
    }
};

const fetchSecurityKpi = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/kpi', { params: { days: 30 } });
        securityKpi.value = parseSingleResponse<SecurityKpi>(response) as SecurityKpi || null;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security KPI:', _error);
    }
};

const fetchBlocklist = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/blocklist');
        blocklist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (_error: unknown) {
        logger.error('Failed to fetch blocklist:', _error);
    }
};

const fetchWhitelist = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/whitelist');
        whitelist.value = ensureArray(parseSingleResponse<IpManagementItem[]>(response)) as IpManagementItem[];
    } catch (_error: unknown) {
        logger.error('Failed to fetch whitelist:', _error);
    }
};

// ========================================
// IP ACTIONS (from OverviewTab)
// ========================================
const blockIP = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.ipManagement.block.button'),
        message: t('features.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('features.security.ipManagement.block.button'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/block-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to block IP:', _error);
        toast.error.fromResponse(_error);
    }
};

const checkIPStatus = async (ip: string): Promise<void> => {
    try {
        const response = await api.get('/manage/security/check-ip', { params: { ip_address: ip } });
        const status = parseSingleResponse<IpStatus>(response) as IpStatus || null;
        overviewTabRef.value?.setIpStatus(status);
    } catch (_error: unknown) {
        logger.error('Failed to check IP status:', _error);
        toast.error.fromResponse(_error);
    }
};

const blockIPFromLog = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.logs.actions.blockIp'),
        message: t('features.security.messages.confirmBlock', { ip }),
        variant: 'danger',
        confirmText: t('features.security.logs.actions.blockIp'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/block-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.blockSuccess'));
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to block IP:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkBlockFromLogs = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.blockSelected'),
        message: t('features.security.messages.confirmBulkBlock', { count: ips.length }),
        variant: 'danger',
        confirmText: t('features.security.bulkActions.blockSelected'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-block', { ip_addresses: ips });
        toast.success.action(t('features.security.messages.bulkBlockSuccess'));
        overviewTabRef.value?.clearSelection();
        await fetchBlocklist();
        await fetchLogs();
    } catch (_error: unknown) {
        logger.error('Failed to bulk block:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// BLOCKLIST ACTIONS
// ========================================
const removeFromBlocklist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.unblock'),
        message: t('features.security.messages.confirmUnblock', { ip }),
        variant: 'warning',
        confirmText: t('features.security.blocklist.actions.unblock'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/unblock-ip', { ip_address: ip });
        toast.success.action(t('features.security.messages.unblockSuccess'));
        await fetchBlocklist();
    } catch (_error: unknown) {
        logger.error('Failed to remove from blocklist:', _error);
        toast.error.fromResponse(_error);
    }
};

const moveToWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.blocklist.actions.moveToWhitelist'),
        message: t('features.security.messages.confirmMoveToWhitelist', { ip }),
        variant: 'info',
        confirmText: t('common.actions.move'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/unblock-ip', { ip_address: ip });
        await api.post('/manage/security/whitelist', { ip_address: ip });
        toast.success.action(t('features.security.messages.movedToWhitelist'));
        await fetchBlocklist();
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to move to whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkUnblock = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.unblockSelected'),
        message: t('features.security.messages.confirmBulkUnblock', { count: ips.length }),
        variant: 'warning',
        confirmText: t('features.security.bulkActions.unblockSelected'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-unblock', { ip_addresses: ips });
        toast.success.action(t('features.security.messages.bulkUnblockSuccess'));
        blocklistTabRef.value?.clearSelection();
        await fetchBlocklist();
    } catch (_error: unknown) {
        logger.error('Failed to bulk unblock:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// WHITELIST ACTIONS
// ========================================
const addToWhitelist = async (ip: string): Promise<void> => {
    try {
        await api.post('/manage/security/whitelist', { ip_address: ip });
        toast.success.action(t('features.security.messages.whitelistSuccess'));
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to add to whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const removeFromWhitelist = async (ip: string): Promise<void> => {
    const confirmed = await confirm({
        title: t('features.security.whitelist.actions.remove'),
        message: t('features.security.messages.confirmRemoveWhitelist', { ip }),
        variant: 'danger',
        confirmText: t('common.actions.remove'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/remove-whitelist', { ip_address: ip });
        toast.success.action(t('features.security.messages.whitelistRemoveSuccess'));
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to remove from whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

const bulkRemoveWhitelist = async (ips: string[]): Promise<void> => {
    if (ips.length === 0) return;
    const confirmed = await confirm({
        title: t('features.security.bulkActions.removeSelected'),
        message: t('features.security.messages.confirmBulkRemoveWhitelist', { count: ips.length }),
        variant: 'danger',
        confirmText: t('common.actions.remove'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/bulk-remove-whitelist', { ip_addresses: ips });
        toast.success.action(t('features.security.messages.bulkWhitelistRemoveSuccess'));
        whitelistTabRef.value?.clearSelection();
        await fetchWhitelist();
    } catch (_error: unknown) {
        logger.error('Failed to bulk remove whitelist:', _error);
        toast.error.fromResponse(_error);
    }
};

// ========================================
// CSP REPORTS
// ========================================
const fetchCspReports = async (): Promise<void> => {
    cspLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...cspFilters };
        if (params.status === 'all') params.status = '';
        const response = await api.get('/manage/security/csp-reports', { params });
        const result = response.data;
        cspReports.value = (result.data as CspReport[]) || [];
        cspPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch CSP reports:', _error);
    } finally {
        cspLoading.value = false;
    }
};

const fetchCspStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/csp-reports/statistics');
        cspStats.value = (response.data as CspStats) || {};
    } catch (_error: unknown) {
        logger.error('Failed to fetch CSP stats:', _error);
    }
};

const applyCspFilters = (): void => { cspFilters.page = 1; fetchCspReports(); };
const resetCspFilters = (): void => {
    cspFilters.status = 'all';
    cspFilters.directive = '';
    cspFilters.page = 1;
    fetchCspReports();
};

const cspBulkAction = async (action: string, ids: number[]): Promise<void> => {
    if (ids.length === 0) return;
    const confirmed = await confirm({
        title: t('common.actions.confirm'),
        message: t('features.security.cspReports.confirmBulkAction', { count: ids.length, action: action.replace('_', ' ') }),
        variant: 'danger',
        confirmText: t('common.actions.confirm'),
    });
    if (!confirmed) return;
    try {
        await api.post('/manage/security/csp-reports/bulk-action', { ids, action });
        toast.success.action(t('common.messages.success.actionSuccess', { item: 'Reports', action: action.replace('_', ' ') }));
        fetchCspReports();
        fetchCspStats();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
};

// ========================================
// SLOW QUERIES
// ========================================
const fetchSlowQueries = async (): Promise<void> => {
    slowQueryLoading.value = true;
    try {
        const response = await api.get('/manage/security/slow-queries', { params: slowQueryFilters });
        const payload = response.data as { data?: SlowQuery[]; total?: number; current_page?: number; last_page?: number };
        slowQueries.value = getResponseList<SlowQuery>(payload);
        slowQueryPagination.value = { total: payload.total || 0, current_page: payload.current_page || 1, last_page: payload.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch slow queries:', _error);
    } finally {
        slowQueryLoading.value = false;
    }
};

const fetchSlowQueryStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/slow-queries/statistics');
        slowQueryStats.value = (response.data as SlowQueryStats) || {};
    } catch (_error: unknown) {
        logger.error('Failed to fetch slow query stats:', _error);
    }
};

const applySlowQueryFilters = (): void => {
    slowQueryFilters.page = 1;
    fetchSlowQueries();
};
const resetSlowQueryFilters = (): void => {
    Object.assign(slowQueryFilters, { route: '', min_duration: '', date_from: '', date_to: '', page: 1 });
    fetchSlowQueries();
};

// ========================================
// VULNERABILITIES
// ========================================
const fetchVulnerabilities = async (): Promise<void> => {
    vulnLoading.value = true;
    try {
        const params: Record<string, string | number> = { ...vulnFilters };
        if (params.source === 'all') params.source = '';
        if (params.severity === 'all') params.severity = '';
        if (params.status === 'all') params.status = '';
        const response = await api.get('/manage/security/dependency-vulnerabilities', { params });
        const result = response.data;
        vulnerabilities.value = (result.data as Vulnerability[]) || [];
        vulnPagination.value = { total: result.total || 0, current_page: result.current_page || 1, last_page: result.last_page || 1 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch vulnerabilities:', _error);
    } finally {
        vulnLoading.value = false;
    }
};

const fetchVulnStats = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/dependency-vulnerabilities/statistics');
        vulnStats.value = (response.data as VulnStats) || { total: 0, critical: 0, high: 0, medium: 0, low: 0 };
    } catch (_error: unknown) {
        logger.error('Failed to fetch vulnerability stats:', _error);
    }
};

const runDependencyAudit = async (): Promise<void> => {
    auditRunning.value = true;
    try {
        await api.post('/manage/security/run-dependency-audit');
        toast.success.action(t('features.security.vulnerabilities.auditCompleted'));
        fetchVulnerabilities();
        fetchVulnStats();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    } finally {
        auditRunning.value = false;
    }
};

const updateVulnStatus = async (vuln: Vulnerability, status: string): Promise<void> => {
    try {
        await api.put(`/manage/security/dependency-vulnerabilities/${vuln.id}`, { status });
        vuln.status = status;
        toast.success.action(t('common.messages.success.updated', { item: 'Status' }));
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
};

const applyVulnFilters = (): void => {
    vulnFilters.page = 1;
    fetchVulnerabilities();
};
const resetVulnFilters = (): void => {
    Object.assign(vulnFilters, { source: 'all', severity: 'all', status: 'all', package: '', page: 1 });
    fetchVulnerabilities();
};

// ========================================
// THREAT ANALYSIS & AUTO-TUNE
// ========================================
const fetchThreatAnalysis = async (): Promise<void> => {
    threatLoading.value = true;
    try {
        const [analysisRes, logsRes] = await Promise.all([
            api.get('/manage/security/threat-analysis'),
            api.get('/manage/security/auto-tune/logs')
        ]);
        threatAnalysisData.value = getResponseObject(analysisRes.data);
        autoTuneLogsData.value = getResponseList(logsRes.data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch threat analysis:', _error);
    } finally {
        threatLoading.value = false;
    }
};

// ========================================
// FILE INTEGRITY
// ========================================
const fetchFileIntegrity = async (): Promise<void> => {
    integrityLoading.value = true;
    try {
        const response = await api.get('/manage/security/file-integrity');
        integrityData.value = getResponseObject(response.data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch file integrity:', _error);
    } finally {
        integrityLoading.value = false;
    }
};

const runIntegrityCheck = async (): Promise<void> => {
    integrityLoading.value = true;
    try {
        await api.post('/manage/security/run-integrity-check'); 
        toast.success.action('File integrity check started');
        fetchFileIntegrity();
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    } finally {
        integrityLoading.value = false;
    }
};

const resyncIntegrityBaseline = async (): Promise<void> => {
    if (resyncCooldownSeconds.value > 0) {
        toast.error.default(`Rate limit aktif. Coba lagi dalam ${resyncCooldownSeconds.value} detik.`);
        return;
    }

    const now = new Date();
    const timestamp = now.toLocaleString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });

    resyncReason.value = `Authorized update: deploy/config change validated on ${timestamp}`;
    resyncClearHistory.value = true;
    resyncDialogOpen.value = true;
};

const submitIntegrityResync = async (): Promise<void> => {
    const reason = resyncReason.value.trim();
    if (reason.length < 8) {
        toast.error.default('Alasan audit minimal 8 karakter');
        return;
    }

    resyncSubmitting.value = true;
    integrityLoading.value = true;
    try {
        await api.post('/manage/security/file-integrity/resync', {
            reason,
            clear_history: resyncClearHistory.value,
        });
        toast.success.action('Baseline integrity diotorisasi & catatan lama dibersihkan');
        resyncDialogOpen.value = false;
        await Promise.all([fetchFileIntegrity(), fetchSecurityHealth()]);
    } catch (_error: unknown) {
        const errorObj = _error as {
            response?: {
                status?: number;
                headers?: Record<string, string | number | undefined>;
                data?: { retry_after?: number };
            };
        };
        const status = errorObj?.response?.status;
        if (status === 429) {
            const fromBody = errorObj?.response?.data?.retry_after;
            const retryAfterRaw = errorObj?.response?.headers?.['retry-after'];
            const retryAfter = Number.isFinite(Number(fromBody)) && Number(fromBody) > 0
                ? Number(fromBody)
                : Number(retryAfterRaw);
            const waitSeconds = Number.isFinite(retryAfter) && retryAfter > 0 ? Math.ceil(retryAfter) : 60;
            resyncCooldownUntil.value = Date.now() + (waitSeconds * 1000);
            toast.error.default(`Terlalu banyak request resync. Tunggu ${waitSeconds} detik lalu coba lagi.`);
        } else {
            toast.error.fromResponse(_error);
        }
    } finally {
        resyncSubmitting.value = false;
        integrityLoading.value = false;
    }
};

// ========================================
// SECURITY SETTINGS
// ========================================
const fetchSecuritySettings = async (): Promise<void> => {
    try {
        const response = await api.get('/manage/security/settings');
        const data = (response.data || {}) as Partial<typeof securitySettings.value>;
        securitySettings.value = {
            security_log_retention_days: data.security_log_retention_days ?? 90,
            activity_log_retention_days: data.activity_log_retention_days ?? 90,
            login_history_retention_days: data.login_history_retention_days ?? 180,
            security_autotune_frequency: data.security_autotune_frequency ?? 'weekly',
        };
        settingsLoaded.value = true;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security settings:', _error);
    }
};

const saveSecuritySettings = async (settings: { 
    security_log_retention_days: number; 
    activity_log_retention_days: number;
    login_history_retention_days: number;
    security_autotune_frequency: string 
}): Promise<void> => {
    settingsSaving.value = true;
    try {
        await api.put('/manage/security/settings', settings);
        securitySettings.value = { ...settings };
        toast.success.default(t('features.security.settings.saved'));
    } catch (_error: unknown) {
        logger.error('Failed to save security settings:', _error);
        toast.error.fromResponse(_error);
    } finally {
        settingsSaving.value = false;
    }
};

// ========================================
// MAINTENANCE MODE
// ========================================
const fetchMaintenanceStatus = async (): Promise<void> => {
    maintenanceLoading.value = true;
    try {
        const response = await api.get('/manage/security/maintenance');
        maintenanceStatus.value = (response.data || maintenanceStatus.value) as typeof maintenanceStatus.value;
    } catch (_error: unknown) {
        logger.error('Failed to fetch maintenance status:', _error);
    } finally {
        maintenanceLoading.value = false;
    }
};

const activateMaintenance = async (modules: string[], duration: number): Promise<void> => {
    maintenanceActivating.value = true;
    try {
        await api.post('/manage/security/maintenance/activate', { modules, duration });
        toast.success.default(t('features.security.maintenance.notifications.activated', { duration }));
        await fetchMaintenanceStatus();
    } catch (_error: unknown) {
        logger.error('Failed to activate maintenance mode:', _error);
        toast.error.fromResponse(_error);
    } finally {
        maintenanceActivating.value = false;
    }
};

const deactivateMaintenance = async (): Promise<void> => {
    maintenanceActivating.value = true;
    try {
        await api.post('/manage/security/maintenance/deactivate');
        toast.success.default(t('features.security.maintenance.notifications.deactivated'));
        await fetchMaintenanceStatus();
        // Refresh integrity after maintenance ends as it triggers re-baseline
        setTimeout(() => fetchFileIntegrity(), 1000);
    } catch (_error: unknown) {
        logger.error('Failed to deactivate maintenance mode:', _error);
        toast.error.fromResponse(_error);
    } finally {
        maintenanceActivating.value = false;
    }
};

// ========================================
// SECURITY DASHBOARD ACTIONS
// ========================================
async function fetchSecurityHealth(): Promise<void> {
    try {
        const response = await api.get('/manage/security/health');
        securityHealth.value = (response.data || securityHealth.value) as typeof securityHealth.value;
    } catch (_error: unknown) {
        logger.error('Failed to fetch security health:', _error);
    }
}

async function testNotification(): Promise<void> {
    try {
        await api.post('/manage/security/test-notification');
        toast.success.default(t('features.security.notifications.test_sent', 'Test notification sent! Check your Telegram.'));
    } catch (_error: unknown) {
        toast.error.fromResponse(_error);
    }
}

// ========================================
// LIFECYCLE & WATCHERS
// ========================================
const refreshAll = async (): Promise<void> => {
    await Promise.all([fetchLogs(), fetchStats(), fetchSecurityKpi(), fetchBlocklist(), fetchWhitelist(), fetchSecurityHealth()]);
    if (activeTab.value === 'shield-journal') {
        await Promise.all([fetchShieldLogs(), fetchShieldStats()]);
    }
};

watch(activeTab, (newTab: string) => {
    if (newTab === 'csp-reports' && cspReports.value.length === 0) {
        fetchCspReports();
        fetchCspStats();
    } else if (newTab === 'slow-queries' && slowQueries.value.length === 0) {
        fetchSlowQueries();
        fetchSlowQueryStats();
    } else if (newTab === 'vulnerabilities' && vulnerabilities.value.length === 0) {
        fetchVulnerabilities();
        fetchVulnStats();
    } else if (newTab === 'shield-journal' && shieldLogs.value.length === 0) {
        fetchShieldLogs();
        fetchShieldStats();
    } else if (newTab === 'threat-analysis' && !threatAnalysisData.value) {
        fetchThreatAnalysis();
    } else if (newTab === 'file-integrity' && !integrityData.value) {
        fetchFileIntegrity();
    } else if (newTab === 'settings' && !settingsLoaded.value) {
        fetchSecuritySettings();
    }
});

onMounted(() => {
    fetchLogs();
    fetchStats();
    fetchSecurityKpi();
    fetchBlocklist();
    fetchWhitelist();
    fetchMaintenanceStatus();
    fetchSecurityHealth();
});
</script>
