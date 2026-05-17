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
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';

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

onMounted(() => {
    fetchSystemInfo();
});
</script>
