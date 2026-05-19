<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ t('modules.system.system.info.title') }}
      </h1>
    </div>

    <!-- Loading Skeleton -->
    <div
      v-if="loading"
      class="space-y-6"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
        <div class="bg-card border border-border rounded-lg p-6 h-24" />
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border border-border rounded-lg p-6 h-64" />
        <div class="bg-card border border-border rounded-lg p-6 h-64" />
      </div>
    </div>

    <!-- Main Content (only show when loaded) -->
    <template v-else>
      <!-- System Health -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.system.system.info.health.title') }}
              </p>
              <p
                class="text-2xl font-semibold mt-1"
                :class="systemHealth === 'healthy' ? 'text-green-600' : systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'"
              >
                {{ systemHealth === 'healthy' ? t('modules.system.system.info.health.healthy') : systemHealth === 'warning' ? t('modules.system.system.info.health.warning') : t('modules.system.system.info.health.critical') }}
              </p>
            </div>
            <div>
              <CheckCircle
                v-if="systemHealth === 'healthy'"
                class="h-12 w-12 text-green-600"
              />
              <AlertTriangle
                v-else
                class="h-12 w-12"
                :class="systemHealth === 'warning' ? 'text-yellow-600' : 'text-red-600'"
              />
            </div>
          </div>
        </div>
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Zap class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.system.system.info.cache.title') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ cacheStatus || t('modules.system.system.info.cache.active') }}
              </p>
            </div>
          </div>
        </div>
        <div class="bg-card border border-border rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ShieldCheck class="h-8 w-8 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.system.system.info.uptime') }}
              </p>
              <p class="text-2xl font-semibold text-foreground">
                {{ formatUptime(systemInfo.uptime) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- System Info -->
        <div class="lg:col-span-2 bg-card border border-border rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">
              {{ t('modules.system.system.info.title') }}
            </h2>
            <router-link
              to="/dash/settings?tab=performance"
              class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/80 text-sm"
            >
              <RotateCcw class="h-4 w-4" />
              {{ t('modules.system.system.info.cache.manage') }}
            </router-link>
          </div>
          <div class="grid grid-cols-1 gap-6">
            <div>
              <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">
                {{ t('modules.system.system.info.sections.application') }}
              </h3>
              <dl class="space-y-2">
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.phpVersion') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ systemInfo.php_version || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.laravelVersion') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ systemInfo.laravel_version || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.environment') }}
                  </dt>
                  <dd class="text-sm text-foreground capitalize">
                    {{ systemInfo.environment || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.debugMode') }}
                  </dt>
                  <dd
                    class="text-sm"
                    :class="systemInfo.debug_mode ? 'text-red-500' : 'text-green-500'"
                  >
                    {{ systemInfo.debug_mode ? t('modules.system.system.info.sections.enabled') : t('modules.system.system.info.sections.disabled') }}
                  </dd>
                </div>
              </dl>
            </div>
            <div>
              <h3 class="text-sm font-medium text-foreground mb-3 font-bold border-b pb-1">
                {{ t('modules.system.system.info.sections.server') }}
              </h3>
              <dl class="space-y-2">
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.serverSoftware') }}
                  </dt>
                  <dd
                    class="text-sm text-foreground truncate max-w-[200px]"
                    :title="systemInfo.server_software"
                  >
                    {{ systemInfo.server_software || '-' }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.memoryUsage') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ displayMemory }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.diskUsage') }}
                  </dt>
                  <dd class="text-sm text-foreground font-mono">
                    {{ displayDisk }}
                  </dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-muted-foreground">
                    {{ t('modules.system.system.info.sections.database') }}
                  </dt>
                  <dd class="text-sm text-foreground font-semibold">
                    {{ systemInfo.database || '-' }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-card border border-border rounded-lg p-6">
          <h2 class="text-lg font-semibold text-foreground mb-4">
            {{ t('modules.system.system.info.quickActions.title') }}
          </h2>
          <div class="grid grid-cols-2 gap-3">
            <router-link
              to="/dash/settings"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Settings class="h-8 w-8 text-primary mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.settings') }}</span>
            </router-link>
                    
            <router-link
              to="/dash/backups"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Download class="h-8 w-8 text-green-600 dark:text-green-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.backups') }}</span>
            </router-link>
                    
            <router-link
              to="/dash/redis"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Database class="h-8 w-8 text-red-500 dark:text-red-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.redis') }}</span>
            </router-link>
                    
            <router-link
              to="/dash/scheduled-tasks"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Clock class="h-8 w-8 text-blue-500 dark:text-blue-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.scheduledTasks') }}</span>
            </router-link>
                    
            <router-link
              to="/dash/scheduled-tasks?action=run_command"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Terminal class="h-8 w-8 text-yellow-500 dark:text-yellow-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.commandRunner') }}</span>
            </router-link>

            <router-link
              to="/dash/system/notifications"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Bell class="h-8 w-8 text-purple-500 dark:text-purple-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.notifications') }}</span>
            </router-link>

            <router-link
              to="/dash/settings?tab=email"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <Mail class="h-8 w-8 text-orange-500 dark:text-orange-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.emailSettings') }}</span>
            </router-link>

            <router-link
              to="/dash/email-templates"
              class="flex flex-col items-center p-4 rounded-lg hover:bg-accent/50"
            >
              <FileText class="h-8 w-8 text-sky-500 dark:text-sky-400 mb-2" />
              <span class="text-xs font-medium text-foreground text-center">{{ t('modules.system.system.info.quickActions.emailTemplates') }}</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- System Care & Maintenance Centre -->
      <div class="mt-6 bg-card border border-border/80 rounded-xl p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
          <Settings class="h-6 w-6 text-primary animate-spin-slow" />
          <div>
            <h2 class="text-lg font-bold text-foreground">
              OS System Care & Maintenance Centre
            </h2>
            <p class="text-sm text-muted-foreground">
              Kelola kesehatan kernel mikro, optimalisasi database, bersihkan file sampah virtual, dan reset sistem.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
          <!-- Clean Junk Files -->
          <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <Trash2 class="h-5 w-5 text-red-500" />
                <h3 class="font-semibold text-foreground text-sm">Pembersih File Sampah</h3>
              </div>
              <p class="text-xs text-muted-foreground mb-4">
                Menghapus file temporary ZIP, sisa scaffolds dev, dan cache sandbox lama.
              </p>
            </div>
            <button
              @click="handleCleanJunk"
              :disabled="actionLoading"
              class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
            >
              <Trash2 class="h-3.5 w-3.5" />
              Bersihkan Junk
            </button>
          </div>

          <!-- Optimize Database -->
          <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <Database class="h-5 w-5 text-indigo-500" />
                <h3 class="font-semibold text-foreground text-sm">Optimalisasi Database</h3>
              </div>
              <p class="text-xs text-muted-foreground mb-4">
                Melakukan defragmentasi indeks skema dan membersihkan data CCK yatim piatu.
              </p>
            </div>
            <button
              @click="handleOptimizeDb"
              :disabled="actionLoading"
              class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
            >
              <Database class="h-3.5 w-3.5" />
              Optimasi Database
            </button>
          </div>

          <!-- Performance Boost -->
          <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <Zap class="h-5 w-5 text-amber-500" />
                <h3 class="font-semibold text-foreground text-sm">Boost Kinerja (Cache)</h3>
              </div>
              <p class="text-xs text-muted-foreground mb-4">
                Mengompilasi ulang config, route, dan view framework Laravel secara instan.
              </p>
            </div>
            <button
              @click="handleBoostPerf"
              :disabled="actionLoading"
              class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
            >
              <Zap class="h-3.5 w-3.5" />
              Akselerasi Kinerja
            </button>
          </div>

          <!-- Factory Reset -->
          <div class="bg-accent/25 border border-border rounded-lg p-5 flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <RefreshCw class="h-5 w-5 text-rose-600" />
                <h3 class="font-semibold text-foreground text-sm">Factory Reset Sistem</h3>
              </div>
              <p class="text-xs text-muted-foreground mb-4">
                Menghapus seluruh data kustom dan plugin, membersihkan sandbox, dan memuat ulang database.
              </p>
            </div>
            <button
              @click="openResetModal"
              :disabled="actionLoading"
              class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-xs font-semibold disabled:opacity-50 transition"
            >
              <RefreshCw class="h-3.5 w-3.5" />
              Reset Sistem
            </button>
          </div>
        </div>

        <!-- Operations Console Output -->
        <div v-if="consoleOutput" class="mt-6 border border-border bg-black rounded-lg p-4 font-mono text-xs text-green-400 max-h-48 overflow-y-auto">
          <div class="flex justify-between items-center mb-2 border-b border-green-900 pb-2">
            <span>[SYS LOGS ENGINE OUTPUT]</span>
            <button @click="consoleOutput = ''" class="text-red-400 hover:underline">Clear</button>
          </div>
          <pre class="whitespace-pre-wrap">{{ consoleOutput }}</pre>
        </div>
      </div>

      <!-- Factory Reset Password Confirmation Modal -->
      <div v-if="showResetModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-card border border-border/80 max-w-md w-full rounded-xl p-6 shadow-xl relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-tr from-rose-500/5 to-transparent pointer-events-none" />
          
          <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg bg-rose-500/10 text-rose-500">
              <RefreshCw class="h-6 w-6 animate-spin" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-foreground">Factory Reset Konfirmasi</h3>
              <p class="text-xs text-muted-foreground">Tindakan destruktif ini tidak dapat dibatalkan.</p>
            </div>
          </div>

          <p class="text-xs text-foreground mb-4 leading-relaxed">
            Ini akan menghapus seluruh data custom sandboxed, mereset struktur tabel database ke kondisi awal pabrik, menghapus plugin pihak ketiga, dan menyetel ulang credentials Administrator.
          </p>

          <div class="mb-4">
            <label class="block text-xs font-semibold text-muted-foreground mb-1.5">
              Masukkan Password Super-Admin Anda
            </label>
            <input
              type="password"
              v-model="resetPassword"
              placeholder="••••••••"
              class="w-full px-3 py-2 bg-accent/20 border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-rose-500"
            />
          </div>

          <div class="flex justify-end gap-2 mt-6">
            <button
              @click="showResetModal = false"
              class="px-4 py-2 bg-accent hover:bg-accent/80 text-foreground rounded-lg text-xs font-semibold transition"
            >
              Batal
            </button>
            <button
              @click="handleFactoryReset"
              :disabled="!resetPassword || actionLoading"
              class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold disabled:opacity-50 transition"
            >
              Reset Sekarang
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Terminal from 'lucide-vue-next/dist/esm/icons/terminal.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';

interface DiskUsage {
    used: string;
    total: string;
    percent?: number;
}

interface SystemInfo {
    uptime: number;
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    server_software: string;
    memory_usage: string;
    memory_usage_percent: number;
    disk_usage: DiskUsage | string;
    disk_usage_percent: number;
    database: string;
}

interface CacheData {
    status: string;
}

const { t } = useI18n();

const loading = ref(true);
const systemInfo = ref<Partial<SystemInfo>>({});
const cacheStatus = ref('Active');

const systemHealth = computed(() => {
    if (!systemInfo.value) return 'healthy';
    
    const memoryUsage = systemInfo.value.memory_usage_percent || 0;
    const diskUsage = systemInfo.value.disk_usage_percent || 0;
    
    if (memoryUsage > 90 || diskUsage > 90) return 'critical';
    if (memoryUsage > 75 || diskUsage > 75) return 'warning';
    return 'healthy';
});

// Computed for display - handle pre-formatted strings from backend
const displayMemory = computed(() => {
    if (!systemInfo.value.memory_usage) return '-';
    // Backend now sends formatted string like "2.44 GB"
    if (typeof systemInfo.value.memory_usage === 'string') {
        return systemInfo.value.memory_usage;
    }
    return formatBytes(systemInfo.value.memory_usage as number);
});

const displayDisk = computed(() => {
    const usage = systemInfo.value.disk_usage;
    if (!usage) return '-';
    if (typeof usage === 'object') {
        // Backend sends: { used: "30.85 GB", total: "97.87 GB", percent: 31.52 }
        return `${usage.used} / ${usage.total} (${usage.percent || 0}%)`;
    }
    return usage;
});

const fetchSystemInfo = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await api.get('/manage/system/info');
        systemInfo.value = parseSingleResponse<SystemInfo>(response) || {};

        // Fetch cache status
        try {
            const cacheResponse = await api.get('/manage/system/cache-status');
            cacheStatus.value = parseSingleResponse<CacheData>(cacheResponse)?.status || 'Active';
        } catch (error: unknown) {
            logger.warning('Failed to fetch cache status:', error);
            cacheStatus.value = 'Active';
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch system info:', error);
    } finally {
        loading.value = false;
    }
};

const formatUptime = (seconds?: number) : string => {
    if (!seconds) return '-';
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
};

const formatBytes = (bytes: number) : string => {
    if (!bytes || typeof bytes !== 'number') return '-';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

// Maintenance Centre State & Handlers
const actionLoading = ref(false);
const consoleOutput = ref('');
const showResetModal = ref(false);
const resetPassword = ref('');

const appendConsole = (msg: string): void => {
    const timestamp = new Date().toLocaleTimeString();
    consoleOutput.value += `[${timestamp}] ${msg}\n`;
};

const handleCleanJunk = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole('Memulai Pembersihan File Sampah (Temp & Dev Artifacts)...');
    try {
        const res = await api.post('/manage/system/maintenance/clean-junk');
        const data = parseSingleResponse<{ deleted_files: number; freed_bytes: number }>(res);
        appendConsole(`SUKSES: File sampah dibersihkan!`);
        appendConsole(`  - Jumlah file dihapus: ${data?.deleted_files || 0}`);
        appendConsole(`  - Total kapasitas yang dibebaskan: ${formatBytes(data?.freed_bytes || 0)}`);
    } catch (err: any) {
        logger.error('Failed to clean junk:', err);
        appendConsole(`ERROR: Gagal membersihkan junk: ${err?.message || err}`);
    } finally {
        actionLoading.value = false;
    }
};

const handleOptimizeDb = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole('Memulai Optimalisasi Database & Indeks CCK...');
    try {
        const res = await api.post('/manage/system/maintenance/optimize-db');
        const data = parseSingleResponse<{ optimized_tables: number; purged_orphans: number }>(res);
        appendConsole(`SUKSES: Indeks database berhasil didefragmentasi!`);
        appendConsole(`  - Total tabel dioptimasi: ${data?.optimized_tables || 0}`);
        appendConsole(`  - Total baris CCK yatim piatu yang dipurge: ${data?.purged_orphans || 0}`);
    } catch (err: any) {
        logger.error('Failed to optimize database:', err);
        appendConsole(`ERROR: Gagal optimalisasi database: ${err?.message || err}`);
    } finally {
        actionLoading.value = false;
    }
};

const handleBoostPerf = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole('Memulai Prekompilasi (Config, Route, & View Warming)...');
    try {
        await api.post('/manage/system/maintenance/boost');
        appendConsole(`SUKSES: Cache internal kernel mikro dihangatkan!`);
        appendConsole(`  - Mode cache: Router & Views Cache Warmup.`);
    } catch (err: any) {
        logger.error('Failed to boost performance:', err);
        appendConsole(`ERROR: Gagal warming cache: ${err?.message || err}`);
    } finally {
        actionLoading.value = false;
    }
};

const openResetModal = (): void => {
    resetPassword.value = '';
    showResetModal.value = true;
};

const handleFactoryReset = async (): Promise<void> => {
    actionLoading.value = true;
    appendConsole('Memulai Pemulihan Default Pabrik (FACTORY RESET)...');
    showResetModal.value = false;
    try {
        await api.post('/manage/system/maintenance/factory-reset', {
            password: resetPassword.value,
        });
        appendConsole('SUKSES: Seluruh data sandbox telah dibersihkan secara total.');
        appendConsole('SUKSES: Database bermigrasi kembali dari nol secara aman.');
        appendConsole('Sistem akan meredireksi Anda ke halaman login dalam 5 detik...');
        setTimeout(() => {
            window.location.href = '/login';
        }, 5000);
    } catch (err: any) {
        logger.error('Failed to factory reset:', err);
        appendConsole(`ERROR: Otorisasi gagal atau terjadi kesalahan: ${err?.message || err}`);
    } finally {
        actionLoading.value = false;
    }
};

onMounted(() => {
    fetchSystemInfo();
});
</script>
