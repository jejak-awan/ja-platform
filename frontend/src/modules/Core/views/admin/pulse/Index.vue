<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ t('features.journalDashboard.title') }}
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        {{ t('features.journalDashboard.description') }}
      </p>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Activity Logs Card -->
      <router-link 
        to="/dash/activity-journal" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-indigo-500/20 dark:bg-indigo-500/10 flex items-center justify-center">
            <ClipboardList class="w-6 h-6 text-indigo-500 dark:text-indigo-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('features.journalDashboard.cards.activity.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('features.journalDashboard.cards.activity.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.activity?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('features.journalDashboard.cards.activity.total') }}</span>
        </div>
      </router-link>

      <!-- Security Logs Card -->
      <router-link 
        to="/dash/security-journal" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-red-500/20 dark:bg-red-500/10 flex items-center justify-center">
            <ShieldAlert class="w-6 h-6 text-red-500 dark:text-red-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('features.journalDashboard.cards.security.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('features.journalDashboard.cards.security.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.security?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('features.journalDashboard.cards.security.total') }}</span>
        </div>
      </router-link>

      <!-- Login History Card -->
      <router-link 
        to="/dash/access-journal" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-green-500/20 dark:bg-green-500/10 flex items-center justify-center">
            <Users class="w-6 h-6 text-green-500 dark:text-green-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('features.journalDashboard.cards.login.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('features.journalDashboard.cards.login.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.login?.total || 0 }}</span>
          <span class="text-muted-foreground">{{ t('features.journalDashboard.cards.login.total') }}</span>
        </div>
      </router-link>

      <!-- System Logs Card -->
      <router-link 
        to="/dash/system-journal" 
        class="bg-card border border-border rounded-lg p-6 hover:border-primary/50 hover:shadow-md group"
      >
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-full bg-yellow-500/20 dark:bg-yellow-500/10 flex items-center justify-center">
            <Cpu class="w-6 h-6 text-yellow-500 dark:text-yellow-400" />
          </div>
          <ChevronRight class="w-5 h-5 text-muted-foreground group-hover:text-primary" />
        </div>
        <h3 class="font-semibold text-foreground mb-1">
          {{ t('features.journalDashboard.cards.system.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-3">
          {{ t('features.journalDashboard.cards.system.description') }}
        </p>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-foreground font-medium">{{ stats.system?.files || 0 }}</span>
          <span class="text-muted-foreground">{{ t('features.journalDashboard.cards.system.files') }}</span>
        </div>
      </router-link>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity Logs -->
      <div class="bg-card border border-border rounded-lg">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
          <h2 class="font-semibold text-foreground">
            {{ t('features.journalDashboard.recent.activity') }}
          </h2>
          <router-link
            to="/dash/activity-journal"
            class="text-sm text-primary hover:underline"
          >
            {{ t('features.journalDashboard.viewAll') }}
          </router-link>
        </div>
        <div
          v-if="loading"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('features.journalDashboard.loading') }}
        </div>
        <div
          v-else-if="recentActivity.length === 0"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('features.journalDashboard.empty') }}
        </div>
        <div
          v-else
          class="divide-y divide-border"
        >
          <div
            v-for="log in recentActivity"
            :key="log.id"
            class="px-6 py-3 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span
                :class="getActionBadgeClass(log.action)"
                class="text-xs px-2 py-1 rounded-full"
              >
                {{ getActionLabel(log.action) }}
              </span>
              <span class="text-sm text-foreground">{{ log.description || log.model_type }}</span>
            </div>
            <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Recent Security Events -->
      <div class="bg-card border border-border rounded-lg">
        <div class="px-6 py-4 border-b border-border flex items-center justify-between">
          <h2 class="font-semibold text-foreground">
            {{ t('features.journalDashboard.recent.security') }}
          </h2>
          <router-link
            to="/dash/security-journal"
            class="text-sm text-primary hover:underline"
          >
            {{ t('features.journalDashboard.viewAll') }}
          </router-link>
        </div>
        <div
          v-if="loading"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('features.journalDashboard.loading') }}
        </div>
        <div
          v-else-if="recentSecurity.length === 0"
          class="p-6 text-center text-muted-foreground"
        >
          {{ t('features.journalDashboard.empty') }}
        </div>
        <div
          v-else
          class="divide-y divide-border"
        >
          <div
            v-for="log in recentSecurity"
            :key="log.id"
            class="px-6 py-3 flex items-center justify-between"
          >
            <div class="flex items-center gap-3">
              <span
                :class="getSecurityBadgeClass(log.event_type)"
                class="text-xs px-2 py-1 rounded-full"
              >
                {{ getSecurityEventLabel(log.event_type) }}
              </span>
              <span class="text-sm text-foreground">{{ log.ip_address }}</span>
            </div>
            <span class="text-xs text-muted-foreground">{{ formatDate(log.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import ClipboardList from 'lucide-vue-next/dist/esm/icons/clipboard-list.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import Users from 'lucide-vue-next/dist/esm/icons/users.js';
import Cpu from 'lucide-vue-next/dist/esm/icons/cpu.js';

interface ActivityStats {
    total?: number;
}

interface SecurityStats {
    total?: number;
}

interface LoginStats {
    total?: number;
}

interface SystemStats {
    files?: number;
}

interface Stats {
    activity?: ActivityStats;
    security?: SecurityStats;
    login?: LoginStats;
    system?: SystemStats;
}

interface ActivityLog {
    id: number;
    action: string;
    description: string;
    model_type: string;
    created_at: string;
}

interface SecurityLog {
    id: number;
    event_type: string;
    ip_address: string;
    created_at: string;
}

const { t } = useI18n();

const loading = ref(true);
const stats = ref<Stats>({});
const recentActivity = ref<ActivityLog[]>([]);
const recentSecurity = ref<SecurityLog[]>([]);

const fetchStats = async () => {
    try {
        // Fetch activity logs stats
        const activityStats = await api.get('/admin/core/activity-journal/statistics').catch(() => ({ data: {} }));
        stats.value.activity = activityStats.data?.data || activityStats.data || {};

        // Fetch security stats
        const securityStats = await api.get('/admin/core/security/stats').catch(() => ({ data: {} }));
        const secData = securityStats.data?.data || securityStats.data || {};
        stats.value.security = {
            ...secData,
            total: secData.total_events || 0
        };

        // Fetch login history stats
        const loginStats = await api.get('/admin/core/access-journal/statistics').catch(() => ({ data: {} }));
        const logData = loginStats.data?.data || loginStats.data || {};
        stats.value.login = {
            ...logData,
            total: (logData.total_logins || 0) + (logData.failed_logins || 0)
        };

        const systemLogs = await api.get('/admin/core/system-journal').catch(() => ({ data: { data: [] } }));
        stats.value.system = { files: (systemLogs.data?.data || []).length };
    } catch (error: unknown) {
        logger.error('Failed to fetch stats:', error);
    }
};

const fetchRecentLogs = async () => {
    try {
        // Fetch recent activity
        const activityResponse = await api.get('/admin/core/activity-journal/recent?limit=10').catch(() => ({ data: {} }));
        const activityData = activityResponse.data;
        recentActivity.value = Array.isArray(activityData)
            ? activityData
            : (activityData?.data || []);

        const securityResponse = await api.get('/admin/core/security/journal?per_page=10').catch(() => ({ data: { data: { data: [] } } }));
        const secData = securityResponse.data?.data?.data || securityResponse.data?.data || [];
        recentSecurity.value = Array.isArray(secData) ? secData : [];
    } catch (error: unknown) {
        logger.error('Failed to fetch recent logs:', error);
    }
};

const getActionBadgeClass = (action?: string) => {
    if (!action) return 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400';
    
    const classes: Record<string, string> = {
        created: 'bg-green-500/20 dark:bg-green-500/10 text-green-500 dark:text-green-400',
        updated: 'bg-blue-500/20 dark:bg-blue-500/10 text-blue-500 dark:text-blue-400',
        deleted: 'bg-red-500/20 dark:bg-red-500/10 text-red-500 dark:text-red-400',
        login: 'bg-indigo-500/20 dark:bg-indigo-500/10 text-indigo-500 dark:text-indigo-400',
        logout: 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400',
    };
    return classes[action] || 'bg-gray-500/20 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400';
};

const getSecurityBadgeClass = (eventType?: string) => {
    if (!eventType) return 'bg-yellow-500/20 dark:bg-yellow-500/10 text-yellow-500 dark:text-yellow-400';
    
    if (eventType.includes('failed') || eventType.includes('blocked')) {
        return 'bg-red-500/20 dark:bg-red-500/10 text-red-500 dark:text-red-400';
    }
    if (eventType.includes('success')) {
        return 'bg-green-500/20 dark:bg-green-500/10 text-green-500 dark:text-green-400';
    }
    return 'bg-yellow-500/20 dark:bg-yellow-500/10 text-yellow-500 dark:text-yellow-400';
};

const getActionLabel = (action?: string) => {
    if (!action) return '-';
    const key = `features.activityJournal.filters.types.${action}`;
    const translated = t(key);
    return translated !== key ? translated : action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getSecurityEventLabel = (eventType?: string) => {
    if (!eventType) return '-';
    // Use security event types if possible, fallback to activity logs types
    let key = `features.security.logs.eventTypes.${eventType}`;
    let translated = t(key);
    
    if (translated === key) {
        key = `features.activityJournal.filters.types.${eventType}`;
        translated = t(key);
    }
    
    return translated !== key ? translated : eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    
    if (diffMins < 1) return t('common.time.justNow') || 'Baru saja';
    if (diffMins < 60) return t('common.time.minsAgo', { count: diffMins }) || `${diffMins} menit lalu`;
    if (diffHours < 24) return t('common.time.hoursAgo', { count: diffHours }) || `${diffHours} jam lalu`;
    return date.toLocaleDateString();
};

onMounted(async () => {
    loading.value = true;
    await Promise.all([fetchStats(), fetchRecentLogs()]);
    loading.value = false;
});
</script>
