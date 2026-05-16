<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
          {{ t('modules.core.system.backups.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ t('modules.core.system.backups.description') }}
        </p>
      </div>
      <Button
        :disabled="creating"
        class="min-w-[140px]"
        @click="createBackup"
      >
        <Loader2
          v-if="creating"
          class="w-4 h-4 mr-2"
        />
        <Plus
          v-else
          class="w-4 h-4 mr-2"
        />
        {{ creating ? t('modules.core.system.backups.creating') : t('modules.core.system.backups.create') }}
      </Button>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
    >
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-indigo-500/10 rounded-xl">
              <Database class="h-6 w-6 text-indigo-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.core.system.backups.stats.total') }}
              </p>
              <p class="text-2xl font-bold tracking-tight text-foreground">
                {{ statistics.total || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-emerald-500/10 rounded-xl">
              <HardDrive class="h-6 w-6 text-emerald-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.core.system.backups.stats.size') }}
              </p>
              <p class="text-2xl font-bold tracking-tight text-foreground">
                {{ formatFileSize(statistics.total_size || 0) }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-blue-500/10 rounded-xl">
              <Clock class="h-6 w-6 text-blue-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.core.system.backups.stats.last') }}
              </p>
              <p
                class="text-xl font-bold tracking-tight text-foreground truncate max-w-[150px]"
                :title="formatDate(statistics.last_backup)"
              >
                {{ formatDate(statistics.last_backup) || t('modules.core.system.backups.stats.never') }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="p-2.5 bg-amber-500/10 rounded-xl">
              <Calendar class="h-6 w-6 text-amber-500" />
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                {{ t('modules.core.system.backups.stats.auto') }}
              </p>
              <Badge :variant="statistics.schedule?.enabled ? 'success' : 'secondary'">
                {{ statistics.schedule?.enabled ? t('modules.core.system.backups.stats.enabled') : t('modules.core.system.backups.stats.disabled') }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Schedule Settings -->
    <Card class="mb-6">
      <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <CardTitle class="text-lg font-semibold">
          {{ t('modules.core.system.backups.schedule.title') }}
        </CardTitle>
        <Button
          variant="ghost"
          size="sm"
          class="h-8 text-primary hover:text-primary hover:bg-primary/10"
          @click="openScheduleModal"
        >
          <Settings class="w-4 h-4 mr-2" />
          {{ t('modules.core.system.backups.schedule.configure') }}
        </Button>
      </CardHeader>
      <CardContent>
        <div
          v-if="statistics?.schedule"
          class="grid grid-cols-2 md:grid-cols-4 gap-6"
        >
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('modules.core.system.backups.schedule.status') }}
            </p>
            <div class="flex items-center gap-2">
              <span
                class="w-2 h-2 rounded-full"
                :class="statistics.schedule.enabled ? 'bg-emerald-500' : 'bg-muted-foreground/30'"
              />
              <p
                class="text-sm font-medium"
                :class="statistics.schedule.enabled ? 'text-emerald-600' : 'text-muted-foreground'"
              >
                {{ statistics.schedule.enabled ? t('modules.core.system.backups.stats.enabled') : t('modules.core.system.backups.stats.disabled') }}
              </p>
            </div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('modules.core.system.backups.schedule.frequency') }}
            </p>
            <p class="text-sm font-medium text-foreground capitalize">
              {{ t(`modules.core.system.backups.schedule.frequencies.${statistics.schedule.frequency}`) || 'Daily' }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('modules.core.system.backups.schedule.time') }}
            </p>
            <p class="text-sm font-medium text-foreground">
              {{ statistics.schedule.time || '02:00' }}
            </p>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {{ t('modules.core.system.backups.schedule.retention') }}
            </p>
            <p class="text-sm font-medium text-foreground">
              {{ statistics.schedule.retention_days || 30 }} {{ t('modules.core.system.backups.schedule.days') }}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader class="pb-0 border-b-0 space-y-4">
        <div class="flex items-center gap-4">
          <div class="relative flex-1 max-w-sm">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              v-model="search"
              :placeholder="t('modules.core.system.backups.search')"
              class="pl-9"
            />
          </div>
        </div>
      </CardHeader>
      <CardContent class="p-0">
        <div
          v-if="loading"
          class="p-12 text-center"
        >
          <Loader2 class="w-8 h-8 mx-auto text-muted-foreground mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('modules.core.system.backups.loading') }}
          </p>
        </div>

        <div
          v-else-if="filteredBackups.length === 0"
          class="p-12 text-center"
        >
          <Database class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('modules.core.system.backups.empty') }}
          </p>
        </div>

        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('modules.core.system.backups.table.name') }}</TableHead>
              <TableHead>{{ t('modules.core.system.backups.table.size') }}</TableHead>
              <TableHead>Password</TableHead>
              <TableHead>{{ t('modules.core.system.backups.table.created') }}</TableHead>
              <TableHead class="text-right">
                {{ t('modules.core.system.backups.table.actions') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="backup in filteredBackups"
              :key="backup.id"
              class="hover:bg-muted/50 group"
            >
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-muted rounded-lg group-hover:bg-background">
                    <FileArchive class="w-4 h-4 text-muted-foreground group-hover:text-primary" />
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-foreground">
                      {{ backup.name }}
                    </div>
                    <div class="text-xs text-muted-foreground tracking-tight">
                      {{ backup.type || 'Full System Backup' }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-sm tabular-nums">
                {{ formatFileSize(backup.size) }}
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2 max-w-[200px]">
                  <div class="relative flex-1">
                    <input 
                      :type="visiblePasswords[backup.id] ? 'text' : 'password'" 
                      :value="backup.password || '****************'" 
                      readonly
                      class="w-full h-8 px-2 pr-8 text-sm bg-background border border-border/60 rounded-md focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    <button 
                      v-if="backup.password"
                      class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground"
                      tabindex="-1"
                      @click="togglePasswordVisibility(backup.id)"
                    >
                      <EyeOff
                        v-if="visiblePasswords[backup.id]"
                        class="w-3.5 h-3.5"
                      />
                      <Eye
                        v-else
                        class="w-3.5 h-3.5"
                      />
                    </button>
                  </div>
                  <Button
                    v-if="backup.password"
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 shrink-0"
                    @click="copyPassword(backup.id, backup.password)"
                  >
                    <Check
                      v-if="copiedPasswords[backup.id]"
                      class="w-3.5 h-3.5 text-green-500"
                    />
                    <Copy
                      v-else
                      class="w-3.5 h-3.5"
                    />
                  </Button>
                  <span
                    v-else
                    class="text-xs text-muted-foreground italic"
                  >No Password</span>
                </div>
              </TableCell>
              <TableCell class="text-sm text-muted-foreground">
                {{ formatDate(backup.created_at) }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100">
                  <Button 
                    variant="ghost" 
                    size="icon" 
                    :title="t('modules.core.system.backups.table.download')"
                    class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    @click="downloadBackup(backup)"
                  >
                    <Download class="w-4 h-4" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon" 
                    :title="t('modules.core.system.backups.table.restore')"
                    class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                    @click="restoreBackup(backup)"
                  >
                    <RotateCcw class="w-4 h-4" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon" 
                    :title="t('modules.core.system.backups.table.delete')"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                    @click="deleteBackup(backup)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <!-- Schedule Modal -->
    <Dialog v-model:open="showScheduleModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{{ t('modules.core.system.backups.schedule.modal.title') }}</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="flex items-center space-x-2">
            <Checkbox 
              id="scheduleEnabled" 
              :checked="scheduleForm.backup_schedule_enabled"
              @update:checked="scheduleForm.backup_schedule_enabled = $event"
            />
            <Label
              for="scheduleEnabled"
              class="text-sm font-medium leading-none cursor-pointer"
            >
              {{ t('modules.core.system.backups.schedule.modal.enable') }}
            </Label>
          </div>
          <div class="grid gap-2">
            <Label>{{ t('modules.core.system.backups.schedule.frequency') }}</Label>
            <Select v-model="scheduleForm.backup_schedule_frequency">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="daily">
                  {{ t('modules.core.system.backups.schedule.frequencies.daily') }}
                </SelectItem>
                <SelectItem value="weekly">
                  {{ t('modules.core.system.backups.schedule.frequencies.weekly') }}
                </SelectItem>
                <SelectItem value="monthly">
                  {{ t('modules.core.system.backups.schedule.frequencies.monthly') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label>{{ t('modules.core.system.backups.schedule.time') }}</Label>
            <Input
              v-model="scheduleForm.backup_schedule_time"
              type="time"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
              <Label>{{ t('modules.core.system.backups.schedule.retention') }} ({{ t('modules.core.system.backups.schedule.days') }})</Label>
              <Input
                v-model.number="scheduleForm.backup_retention_days"
                type="number"
                min="1"
                max="365"
              />
            </div>
            <div class="grid gap-2">
              <Label>{{ t('modules.core.system.backups.schedule.modal.max') }}</Label>
              <Input
                v-model.number="scheduleForm.backup_max_count"
                type="number"
                min="1"
                max="100"
              />
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button
            variant="outline"
            @click="showScheduleModal = false"
          >
            {{ t('modules.core.system.backups.schedule.modal.cancel') }}
          </Button>
          <Button
            :disabled="savingSchedule || !isScheduleDirty"
            @click="saveSchedule"
          >
            <Loader2
              v-if="savingSchedule"
              class="w-4 h-4 mr-2"
            />
            {{ savingSchedule ? t('modules.core.system.backups.schedule.modal.saving') : t('modules.core.system.backups.schedule.modal.save') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import { Badge, Button, Card, CardContent, CardHeader, CardTitle, Checkbox, Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, Input, Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';
import HardDrive from 'lucide-vue-next/dist/esm/icons/hard-drive.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import FileArchive from 'lucide-vue-next/dist/esm/icons/file-archive.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';

interface Backup {
    id: string;
    name: string;
    type: string;
    size: number;
    password?: string;
    created_at: string;
}

interface BackupSchedule {
    enabled: boolean;
    frequency: string;
    time: string;
    retention_days: number;
    max_count: number;
}

interface BackupStatistics {
    total: number;
    total_size: number;
    last_backup: string | null;
    schedule: BackupSchedule;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const backups = ref<Backup[]>([]);
const statistics = ref<BackupStatistics | null>(null);
const loading = ref(false);
const creating = ref(false);
const search = ref('');
const showScheduleModal = ref(false);
const savingSchedule = ref(false);
const scheduleForm = ref({
    backup_schedule_enabled: false,
    backup_schedule_frequency: 'daily',
    backup_schedule_time: '02:00',
    backup_retention_days: 30,
    backup_max_count: 10
});
const initialScheduleForm = ref<typeof scheduleForm.value | null>(null);

// Visibility toggle state for each backup row
const visiblePasswords = ref<Record<number, boolean>>({});
const copiedPasswords = ref<Record<number, boolean>>({});

const togglePasswordVisibility = (id: string) => {
    visiblePasswords.value[id] = !visiblePasswords.value[id];
};

const copyPassword = async (id: string, password: string) => {
    try {
        await navigator.clipboard.writeText(password);
        copiedPasswords.value[id] = true;
        toast.success.action(t('common.messages.copied'));
        setTimeout(() => {
            copiedPasswords.value[id] = false;
        }, 2000);
    } catch (err) {
        logger.error('Failed to copy password:', err);
    }
};

const isScheduleDirty = computed(() => {
    if (!initialScheduleForm.value) return false;
    return JSON.stringify(scheduleForm.value) !== JSON.stringify(initialScheduleForm.value);
});

const filteredBackups = computed(() => {
    if (!search.value) return backups.value;
    
    const searchLower = search.value.toLowerCase();
    return backups.value.filter(backup => 
        backup.name.toLowerCase().includes(searchLower)
    );
});

const fetchBackups = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/backups');
        const { data } = parseResponse(response);
        backups.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/manage/backups/statistics');
            statistics.value = parseSingleResponse<BackupStatistics>(statsResponse);
        } catch {
            // Calculate from backups if endpoint doesn't exist
            statistics.value = {
                total: backups.value.length,
                total_size: backups.value.reduce((sum, b) => sum + (b.size || 0), 0),
                last_backup: backups.value[0]?.created_at || null,
                schedule: {
                    enabled: false,
                    frequency: 'daily',
                    time: '02:00',
                    retention_days: 30,
                    max_count: 10
                }
            };
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch backups:', error);
    } finally {
        loading.value = false;
    }
};

const createBackup = async () => {
    creating.value = true;
    try {
        await api.post('/manage/backups');
        toast.success.action(t('modules.core.system.backups.messages.created'));
        await fetchBackups();
    } catch (error: unknown) {
        logger.error('Failed to create backup:', error);
        toast.error.fromResponse(error);
    } finally {
        creating.value = false;
    }
};

const downloadBackup = async (backup: Backup) => {
    try {
        const response = await api.get(`/manage/backups/${backup.id}/download`, {
            responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', backup.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error: unknown) {
        logger.error('Failed to download backup:', error);
        toast.error.fromResponse(error);
    }
};

const restoreBackup = async (backup: Backup) => {
    const confirmed = await confirm({
        title: t('modules.core.system.backups.actions.restore'),
        message: t('modules.core.system.backups.confirm.restore', { name: backup.name }),
        variant: 'warning',
        confirmText: t('modules.core.system.backups.actions.restore'),
    });

    if (!confirmed) return;

    const doubleConfirmed = await confirm({
        title: t('modules.core.system.backups.actions.restore'),
        message: t('modules.core.system.backups.confirm.restore_warning'),
        variant: 'danger',
        confirmText: t('common.actions.confirm'),
    });

    if (!doubleConfirmed) return;

    try {
        await api.post(`/manage/backups/${backup.id}/restore`);
        toast.success.action(t('modules.core.system.backups.messages.restored'));
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } catch (error: unknown) {
        logger.error('Failed to restore backup:', error);
        toast.error.fromResponse(error);
    }
};

const deleteBackup = async (backup: Backup) => {
    const confirmed = await confirm({
        title: t('modules.core.system.backups.actions.delete'),
        message: t('modules.core.system.backups.confirm.delete', { name: backup.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/backups/${backup.id}`);
        toast.success.delete();
        await fetchBackups();
    } catch (error: unknown) {
        logger.error('Failed to delete backup:', error);
        toast.error.fromResponse(error);
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const saveSchedule = async () => {
    savingSchedule.value = true;
    try {
        await api.post('/manage/backups/schedule', scheduleForm.value);
        showScheduleModal.value = false;
        await fetchBackups(); // Refresh statistics
        toast.success.save();
    } catch (error: unknown) {
        logger.error('Failed to save schedule:', error);
        toast.error.fromResponse(error);
    } finally {
        savingSchedule.value = false;
    }
};

const openScheduleModal = () => {
    // Populate form from statistics
    if (statistics.value && statistics.value.schedule) {
        scheduleForm.value = {
            backup_schedule_enabled: Boolean(statistics.value.schedule.enabled),
            backup_schedule_frequency: statistics.value.schedule.frequency || 'daily',
            backup_schedule_time: statistics.value.schedule.time || '02:00',
            backup_retention_days: Number(statistics.value.schedule.retention_days) || 30,
            backup_max_count: Number(statistics.value.schedule.max_count) || 10
        };
    }
    initialScheduleForm.value = JSON.parse(JSON.stringify(scheduleForm.value));
    showScheduleModal.value = true;
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    // Use i18n date format or component logic. 
    // Ideally use d() from useI18n if setup, but standard toLocaleString is used here.
    // I will keep standard toLocaleString but maybe use locale from i18n if possible, 
    // but browser locale is often fine. 
    // Actually, I should probably respect the app locale.
    return new Date(date).toLocaleString(); 
};

onMounted(() => {
    fetchBackups();
});
</script>

