<template>
  <Card class="border border-border/40 shadow-none rounded-xl">
    <CardHeader class="border-b border-border/40 bg-muted/20 pb-4">
      <div class="flex justify-between items-center">
        <div>
          <CardTitle class="text-lg">{{ $t('modules.school.graduation.labels.statusPub') }}</CardTitle>
          <CardDescription>{{ $t('modules.school.graduation.labels.mappingSubjectDesc') }}</CardDescription>
        </div>
        <Button variant="default" size="sm" class="rounded-lg" @click="fetchSettings" :loading="loading">
          <LucideIcon name="RefreshCcw" class="w-3.5 h-3.5 mr-2" />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
    </CardHeader>
    <CardContent class="p-6">
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="text-xs font-bold uppercase text-muted-foreground">{{ $t('modules.school.graduation.labels.selectYear') }}</label>
            <Select v-model="selectedYear" @update:model-value="onYearChange">
              <SelectTrigger class="w-full rounded-xl">
                <SelectValue :placeholder="$t('common.placeholders.select_year')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="year in years" :key="year" :value="String(year)">
                  {{ year }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div class="flex items-end">
            <Button v-if="!currentSetting" variant="outline" class="w-full rounded-xl border-dashed" @click="createSetting">
              <LucideIcon name="Plus" class="w-4 h-4 mr-2" />
              {{ $t('modules.school.graduation.labels.createSetting', { year: selectedYear }) }}
            </Button>
          </div>
        </div>

        <div v-if="currentSetting" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 bg-muted/10 rounded-2xl border border-border/50">
            <!-- Status & Jadwal -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold flex items-center">
                <LucideIcon name="Settings2" class="w-4 h-4 mr-2 text-primary" />
                {{ $t('modules.school.graduation.labels.statusPub') }}
              </h3>
              
              <div class="flex items-center space-x-2">
                <Switch id="is-open" v-model:checked="form.is_open" />
                <label for="is-open" class="text-sm font-medium leading-none cursor-pointer">
                  {{ $t('modules.school.graduation.labels.openAnnouncement') }}
                </label>
              </div>
              <p class="text-xs text-muted-foreground ml-11">
                {{ $t('modules.school.graduation.labels.openAnnouncementDesc') }}
              </p>

              <div class="space-y-2 pt-2">
                <label class="text-xs font-bold uppercase text-muted-foreground">{{ $t('modules.school.graduation.labels.announcementSchedule') }}</label>
                <Input v-model="form.announcement_date" type="datetime-local" class="rounded-xl w-full" />
                <p class="text-xs text-muted-foreground">{{ $t('modules.school.graduation.labels.announcementScheduleDesc') }}</p>
              </div>
            </div>

            <!-- Subject Mapping -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold flex items-center justify-between">
                <div class="flex items-center">
                  <LucideIcon name="BookOpen" class="w-4 h-4 mr-2 text-primary" />
                  {{ $t('modules.school.graduation.labels.mappingSubject') }}
                </div>
                <Button variant="ghost" size="sm" class="h-8 px-2 text-xs" @click="addSubject">
                  <LucideIcon name="Plus" class="w-3 h-3 mr-1" /> {{ $t('common.actions.add') }}
                </Button>
              </h3>
              
              <p class="text-xs text-muted-foreground mb-2">
                {{ $t('modules.school.graduation.labels.mappingSubjectDesc') }}
              </p>

              <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2">
                <div v-for="(_, idx) in form.subjects" :key="idx" class="flex items-center gap-2">
                  <Input v-model="form.subjects[idx]" :placeholder="$t('common.placeholders.subject_name')" class="flex-1 rounded-lg text-sm" />
                  <Button variant="ghost" size="icon" class="h-9 w-9 text-destructive shrink-0" @click="removeSubject(idx)">
                    <LucideIcon name="Trash2" class="w-4 h-4" />
                  </Button>
                </div>
                <div v-if="!form.subjects || form.subjects.length === 0" class="text-center py-4 text-xs italic text-muted-foreground border-2 border-dashed border-border rounded-xl">
                  {{ $t('modules.school.graduation.labels.noGrades') }}
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-border/40">
            <Button variant="outline" class="rounded-xl px-6" @click="fetchSettings" :disabled="saving">{{ $t('common.actions.cancel') }}</Button>
            <Button variant="default" class="rounded-xl px-8" :loading="saving" @click="saveSettings">
              <LucideIcon name="Save" class="w-4 h-4 mr-2" />
              {{ $t('modules.school.graduation.labels.saveSetting') }}
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
  Card, CardContent, CardHeader, CardTitle, CardDescription,
  Button, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Input, Switch
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import api from '@/core/api/client';
import { parseResponse } from '@/shared/utils/responseParser';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);

const selectedYear = ref(String(new Date().getFullYear()));
const years = computed(() => {
    const current = new Date().getFullYear() + 1; // Future year possible
    return Array.from({ length: 5 }, (_, i) => current - i);
});

const currentSetting = ref<any>(null);
const form = ref({
    is_open: false,
    announcement_date: '',
    subjects: [] as string[],
    config: {}
});

const fetchSettings = async () => {
    if (!selectedYear.value) return;
    loading.value = true;
    try {
        const response = await api.get(`admin/operations/graduation/settings/${selectedYear.value}`);
        const data = parseResponse(response) as any;
        if (data && data.data) {
            currentSetting.value = data.data;
            form.value = {
                is_open: !!currentSetting.value.is_open,
                announcement_date: currentSetting.value.announcement_date ? currentSetting.value.announcement_date.slice(0, 16) : '',
                subjects: Array.isArray(currentSetting.value.subjects) ? [...currentSetting.value.subjects] : [],
                config: currentSetting.value.config || {}
            };
        } else {
            currentSetting.value = null;
        }
    } catch (e: any) {
        if (e.response && e.response.status === 404) {
            currentSetting.value = null;
        } else {
            toast.error.fromResponse(e);
        }
    } finally {
        loading.value = false;
    }
};

const onYearChange = () => {
    fetchSettings();
};

const createSetting = async () => {
    saving.value = true;
    try {
        await api.post(`admin/operations/graduation/settings`, {
            graduation_year: parseInt(selectedYear.value),
            is_open: false,
            subjects: ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris']
        });
        toast.success.action(t('modules.school.graduation.messages.createSuccess'));
        await fetchSettings();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
};

const saveSettings = async () => {
    if (!currentSetting.value) return;
    saving.value = true;
    try {
        await api.put(`admin/operations/graduation/settings/${selectedYear.value}`, {
            is_open: form.value.is_open,
            announcement_date: form.value.announcement_date || null,
            subjects: form.value.subjects,
            config: form.value.config
        });
        toast.success.action(t('modules.school.graduation.messages.saveSuccess'));
        await fetchSettings();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
};

const addSubject = () => {
    form.value.subjects.push('');
};

const removeSubject = (idx: number) => {
    form.value.subjects.splice(idx, 1);
};

onMounted(() => {
    fetchSettings();
});
</script>
