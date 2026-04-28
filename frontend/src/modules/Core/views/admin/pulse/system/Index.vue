<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <Button
          variant="ghost"
          size="icon"
          as-child
          class="h-9 w-9"
        >
          <router-link to="/dash/journal-dashboard">
            <ArrowLeft class="w-5 h-5" />
          </router-link>
        </Button>
        <h1 class="text-2xl font-bold text-foreground">
          {{ t('features.system.logs.title') }}
        </h1>
      </div>
      <div class="flex items-center space-x-2">
        <Button
          :disabled="clearing"
          variant="destructive"
          variant-type="outline"
          @click="clearLogs"
        >
          {{ clearing ? t('features.system.logs.clearing') : t('features.system.logs.clear') }}
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Log Files List -->
      <div class="lg:col-span-1">
        <div class="bg-card border border-border rounded-lg">
          <div class="px-6 py-4 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">
              {{ t('features.system.logs.files') }}
            </h2>
          </div>
          <div class="divide-y divide-border">
            <Button
              v-for="logFile in logFiles"
              :key="logFile.name"
              variant="ghost"
              class="w-full justify-start h-auto px-6 py-4 rounded-none border-b border-border last:border-0 hover:bg-muted"
              :class="[ selectedLogFile?.name === logFile.name ? 'bg-muted border-l-4 border-l-primary' : '' ]"
              @click="selectLogFile(logFile)"
            >
              <div class="flex items-center justify-between w-full">
                <div class="text-left">
                  <p class="text-sm font-medium text-foreground">
                    {{ logFile.name }}
                  </p>
                  <p class="text-xs text-muted-foreground mt-1">
                    {{ formatFileSize(logFile.size) }}
                  </p>
                </div>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-primary hover:text-primary/80"
                  @click.stop="downloadLog(logFile)"
                >
                  <Download class="w-5 h-5" />
                </Button>
              </div>
            </Button>
          </div>
        </div>
      </div>

      <!-- Log Viewer -->
      <div class="lg:col-span-2">
        <div class="bg-card border border-border rounded-lg">
          <div class="px-6 py-4 border-b border-border flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground">
              {{ selectedLogFile ? selectedLogFile.name : t('features.system.logs.select') }}
            </h2>
            <div
              v-if="selectedLogFile"
              class="flex items-center space-x-2"
            >
              <Input
                v-model="logSearch"
                type="text"
                :placeholder="t('features.system.logs.search')"
                class="h-8 w-48"
              />
              <Button
                variant="outline"
                size="sm"
                @click="refreshLog"
              >
                {{ t('features.system.logs.refresh') }}
              </Button>
            </div>
          </div>
          <div class="p-6">
            <div
              v-if="!selectedLogFile"
              class="text-center py-12"
            >
              <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
              <p class="mt-4 text-muted-foreground">
                {{ t('features.system.logs.empty') }}
              </p>
            </div>
            <div
              v-else-if="loadingLog"
              class="text-center py-12"
            >
              <p class="text-muted-foreground">
                {{ t('features.system.logs.loading') }}
              </p>
            </div>
            <div
              v-else
              class="bg-background rounded-lg p-4 overflow-x-auto max-h-[600px] overflow-y-auto"
            >
              <SafeHtml
                tag="pre"
                class="text-xs font-mono text-muted-foreground"
                :html="highlightedLogContent"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, onMounted, computed } from 'vue';
import SafeHtml from '@/modules/Core/components/ui/SafeHtml.vue';
import { useI18n } from 'vue-i18n';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '@/utils/responseParser';
import { Button, Input } from '@/components/ui';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';

interface LogFile {
    name: string;
    size: number;
    modified_at?: string;
}

interface LogResponse {
    content: string;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const logFiles = ref<LogFile[]>([]);
const selectedLogFile = ref<LogFile | null>(null);
const logContent = ref('');
const loadingLog = ref(false);
const clearing = ref(false);
const logSearch = ref('');

const highlightedLogContent = computed(() => {
    if (!logContent.value) return '';
    
    let content = logContent.value;
    
    // Highlight error lines
    content = content.replace(/\[ERROR\]/g, '<span class="text-destructive font-bold">[ERROR]</span>');
    content = content.replace(/\[WARNING\]/g, '<span class="text-yellow-400 font-bold">[WARNING]</span>');
    content = content.replace(/\[INFO\]/g, '<span class="text-blue-400 font-bold">[INFO]</span>');
    
    // Highlight search term
    if (logSearch.value) {
        const regex = new RegExp(`(${logSearch.value})`, 'gi');
        content = content.replace(regex, '<span class="bg-yellow-500 text-black">$1</span>');
    }
    
    return content;
});

const fetchLogFiles = async () : Promise<void> => {
    try {
        const response = await api.get('admin/core/system-journal');
        const { data } = parseResponse<LogFile[]>(response);
        logFiles.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch log files:', error);
        logFiles.value = [];
    }
};

const selectLogFile = async (logFile: LogFile) : Promise<void> => {
    selectedLogFile.value = logFile;
    loadingLog.value = true;
    try {
        const response = await api.get(`admin/core/system-journal/${logFile.name}`);
        const data = parseSingleResponse<LogResponse>(response) || { content: '' };
        logContent.value = data.content || '';
    } catch (error: unknown) {
        logger.error('Failed to fetch log content:', error);
        logContent.value = t('features.system.logs.failed_load') || 'Failed to load log content';
    } finally {
        loadingLog.value = false;
    }
};

const refreshLog = () => {
    if (selectedLogFile.value) {
        selectLogFile(selectedLogFile.value);
    }
};

const downloadLog = async (logFile: LogFile) : Promise<void> => {
    try {
        const response = await api.get(`admin/core/system-journal/${logFile.name}/download`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', logFile.name || 'system.log');
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (error: unknown) {
        logger.error('Failed to download logs:', error);
        toast.error.fromResponse(error);
    }
};

const clearLogs = async () : Promise<void> => {
    const confirmed = await confirm({
        title: t('features.system.logs.actions.clear'),
        message: t('features.system.logs.confirm.clear') || 'Are you sure you want to clear all logs?',
        variant: 'danger',
        confirmText: t('common.actions.clear'),
    });

    if (!confirmed) return;

    clearing.value = true;
    try {
        await api.post('admin/core/system-journal/clear', {
            reason: `Manual clear from system journal at ${new Date().toISOString()}`,
        });
        toast.success.action(t('features.system.logs.messages.cleared'));
        logContent.value = '';
        await fetchLogFiles();
        selectedLogFile.value = null;
    } catch (error: unknown) {
        logger.error('Failed to clear logs:', error);
        toast.error.fromResponse(error);
    } finally {
        clearing.value = false;
    }
};

const formatFileSize = (bytes?: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchLogFiles();
});
</script>

