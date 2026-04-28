<template>
  <Card class="email-status-widget h-full flex flex-col overflow-hidden border-border/40">
    <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Mail class="w-5 h-5 text-primary" />
        {{ t('features.dashboard.widgets.emailStatus.title', 'Email Status') }}
      </CardTitle>
      <Button
        variant="ghost"
        size="icon"
        :disabled="loading"
        class="h-8 w-8 text-muted-foreground hover:text-foreground"
        :aria-label="t('common.actions.refresh')"
        @click="refresh"
      >
        <RefreshCw
          class="w-4 h-4"
          :class="{ '': loading }"
        />
      </Button>
    </CardHeader>

    <CardContent class="flex-1 space-y-4 pt-2 overflow-y-auto">
      <!-- Top Stats Row -->
      <div class="grid grid-cols-2 gap-3">
        <div class="p-3 rounded-xl bg-warning/5 border border-warning/10 space-y-1">
          <p class="text-[10px] font-bold text-foreground">
            {{ t('features.dashboard.widgets.emailStatus.templates', 'Templates') }}
          </p>
          <p class="text-2xl font-bold text-foreground tabular-nums">
            {{ stats.templates || 0 }}
          </p>
        </div>
        <div class="p-3 rounded-xl bg-info/5 border border-info/10 space-y-1">
          <p class="text-[10px] font-bold text-foreground">
            {{ t('features.dashboard.widgets.emailStatus.subscribers', 'Subscribers') }}
          </p>
          <p class="text-2xl font-bold text-foreground tabular-nums">
            {{ stats.subscribers || 0 }}
          </p>
        </div>
      </div>

      <!-- Connection Status -->
      <div class="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-border/40">
        <div class="flex items-center gap-2">
          <Zap class="w-4 h-4 text-warning" />
          <span class="text-sm font-semibold text-foreground">{{ t('features.dashboard.widgets.emailStatus.smtpStatus', 'SMTP Connection') }}</span>
        </div>
        <Badge
          :class="statusBadgeClass"
          variant="outline"
          class="border-none font-bold text-[10px]"
        >
          {{ stats.smtp_status || 'UNKNOWN' }}
        </Badge>
      </div>

      <!-- Recent Logs -->
      <div class="space-y-2">
        <h4 class="text-xs font-bold text-muted-foreground">
          {{ t('features.dashboard.widgets.emailStatus.recentLogs', 'Recent Activity') }}
        </h4>
        <div
          v-if="logs.length === 0"
          class="text-center py-4 text-xs text-muted-foreground italic"
        >
          {{ t('features.dashboard.widgets.emailStatus.noLogs', 'No recent email activity') }}
        </div>
        <div
          v-else
          class="space-y-2"
        >
          <div
            v-for="(log, index) in logs.slice(0, 3)"
            :key="index"
            class="p-2 rounded-lg bg-muted/30 border border-border/20 text-[11px]"
          >
            <div class="flex justify-between items-start mb-1">
              <span class="font-bold text-foreground truncate max-w-[140px]">{{ log.to }}</span>
              <span class="text-[9px] text-muted-foreground">{{ formatTime(log.sent_at) }}</span>
            </div>
            <div class="text-muted-foreground truncate italic">
              {{ log.subject }}
            </div>
          </div>
        </div>
      </div>
    </CardContent>

    <CardFooter
      v-if="authStore.hasPermission('manage settings')"
      class="pb-4 pt-2 border-t border-border/10"
    >
      <router-link
        to="/dash/settings?tab=email"
        class="w-full"
      >
        <Button
          variant="ghost"
          size="sm"
          class="w-full text-xs font-bold gap-2"
        >
          {{ t('features.dashboard.widgets.emailStatus.manage', 'Manage Email Settings') }}
          <ChevronRight class="w-3 h-3" />
        </Button>
      </router-link>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { parseResponse, parseSingleResponse } from '@/utils/responseParser';
import { 
    Card, 
    CardHeader, 
    CardTitle, 
    CardContent, 
    CardFooter, 
    Button, 
    Badge 
} from '@/components/ui';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';

interface EmailStats {
    templates: number;
    subscribers: number;
    smtp_status: string;
}

interface EmailLog {
    to: string;
    subject: string;
    sent_at: string;
    [key: string]: unknown;
}

const { t } = useI18n();
const authStore = useAuthStore();

const loading = ref(false);
const stats = ref<EmailStats>({
  templates: 0,
  subscribers: 0,
  smtp_status: 'unknown'
});
const logs = ref<EmailLog[]>([]);

const statusBadgeClass = computed(() => {
  const status = stats.value.smtp_status?.toLowerCase();
  if (status === 'active' || status === 'healthy' || status === 'sent') return 'bg-success/10 text-foreground';
  if (status === 'warning') return 'bg-warning/10 text-foreground';
  if (status === 'critical' || status === 'error' || status === 'failed') return 'bg-destructive/10 text-foreground';
  return 'bg-muted text-muted-foreground';
});

const fetchStats = async () => {
  try {
    const response = await api.get('/admin/core/system/statistics');
    const data = parseSingleResponse(response) as { email?: EmailStats } | null;
    if (data && data.email) {
      stats.value = data.email;
    }
  } catch (error) {
    logger.error('Failed to fetch email stats:', error);
  }
};

const fetchLogs = async () => {
  try {
    const response = await api.get('/admin/core/email-test/recent-journal?limit=5');
    const { data } = parseResponse(response) as { data: { logs?: EmailLog[] } };
    if (data && Array.isArray(data.logs)) {
        logs.value = data.logs;
    } else {
        logs.value = [];
    }
  } catch (error) {
    logger.error('Failed to fetch email logs:', error);
  }
};

const refresh = async () => {
  loading.value = true;
  await Promise.all([fetchStats(), fetchLogs()]);
  loading.value = false;
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  refresh();
});
</script>
