<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-card p-4 rounded-xl border border-border/50">
      <div class="flex gap-4 items-center">
        <div class="space-y-1">
          <Label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $t('modules.school.academic.tabs.studyGroups') }}</Label>
          <Select v-model="selectedGroup">
            <SelectTrigger class="w-[200px] h-9 bg-background/50">
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.selectGroup')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="group in groups"
                :key="group.id"
                :value="String(group.id)"
              >
                {{ group.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="w-px h-10 bg-border/50 mx-2" />
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="fetchSchedules"
        >
          <LucideIcon
            name="RefreshCcw"
            class="w-4 h-4 mr-2"
            :class="{ 'animate-spin': loading }"
          />
          {{ $t('common.actions.refresh') }}
        </Button>
      </div>
      <Button
        size="sm"
        class="h-9 shadow-lg shadow-primary/20"
        @click="handleAdd"
      >
        <LucideIcon
          name="Plus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('modules.school.academic.actions.setSchedule') }}
      </Button>
    </div>

    <!-- Timetable Grid -->
    <div class="bg-card rounded-xl border border-border/50 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <div class="min-w-[800px]">
          <!-- Day Headers -->
          <div class="grid grid-cols-8 border-b bg-muted/30">
            <div class="p-3 border-r text-xs font-bold text-muted-foreground text-center bg-muted/50">
              {{ $t('modules.school.academic.labels.time') }}
            </div>
            <div
              v-for="day in days"
              :key="day"
              class="p-3 border-r text-xs font-bold text-center uppercase tracking-widest text-foreground/80"
            >
              {{ day }}
            </div>
          </div>

          <!-- Time Slots -->
          <div
            v-if="loading"
            class="h-64 flex items-center justify-center"
          >
            <LucideIcon
              name="Loader2"
              class="w-8 h-8 animate-spin text-primary opacity-50"
            />
          </div>
          <div
            v-else-if="!selectedGroup"
            class="h-64 flex flex-col items-center justify-center text-muted-foreground space-y-2"
          >
            <LucideIcon
              name="Calendar"
              class="w-12 h-12 opacity-20"
            />
            <p>{{ $t('modules.school.academic.messages.selectGroupToView') }}</p>
          </div>
          <div
            v-else
            class="divide-y"
          >
            <div
              v-for="slot in timeSlots"
              :key="slot"
              class="grid grid-cols-8 hover:bg-muted/5 transition-colors"
            >
              <div class="p-3 border-r text-[10px] font-medium text-muted-foreground text-center flex items-center justify-center bg-muted/20">
                {{ slot }}
              </div>
              <div
                v-for="day in days"
                :key="day"
                class="p-2 border-r relative min-h-[80px]"
              >
                <div 
                  v-for="item in getSchedulesByDayAndTime(day, slot)" 
                  :key="item.id"
                  class="absolute inset-x-1 inset-y-1 p-2 rounded-lg border shadow-sm flex flex-col justify-between overflow-hidden cursor-pointer hover:scale-[1.02] transition-transform"
                  :class="getRandomColor(item.subject_id)"
                  @click="handleEdit(item)"
                >
                  <div class="flex flex-col gap-0.5">
                    <span class="text-[10px] font-bold leading-tight">{{ item.subject?.name }}</span>
                    <span class="text-[9px] opacity-80 truncate">{{ item.staff?.full_name }}</span>
                  </div>
                  <div class="flex justify-between items-center mt-1 text-white/90">
                    <span class="text-[8px] font-mono opacity-80">{{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}</span>
                    <div class="flex items-center gap-1">
                      <LucideIcon
                        v-if="item.room && item.study_group?.students_count > item.room.capacity"
                        name="AlertTriangle"
                        class="w-2.5 h-2.5 text-warning animate-pulse"
                        :title="$t('modules.school.academic.tooltips.overCapacity')"
                      />
                      <LucideIcon
                        v-if="item.room"
                        name="MapPin"
                        class="w-2.5 h-2.5 opacity-60"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Dialog -->
    <ScheduleDialog 
      v-model:open="dialogOpen"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
      @delete="handleDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { AcademicService } from '@/modules/School/services/AcademicService';
import {
  Button, LucideIcon, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';
import ScheduleDialog from './components/ScheduleDialog.vue';

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const groups = ref<any[]>([]);
const selectedGroup = ref('');
const schedules = ref<any[]>([]);
const dialogOpen = ref(false);
const selectedItem = ref<any>(null);

const days = [
  t('common.labels.days.monday'),
  t('common.labels.days.tuesday'),
  t('common.labels.days.wednesday'),
  t('common.labels.days.thursday'),
  t('common.labels.days.friday'),
  t('common.labels.days.saturday')
];
const timeSlots = [
  '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'
];

const fetchGroups = async () => {
  try {
    const response = await AcademicService.getStudyGroups();
    groups.value = parseResponse(response).data;
    if (groups.value.length > 0) {
      selectedGroup.value = String(groups.value[0].id);
    }
  } catch (e) {
    toast.error.fromResponse(e);
  }
};

const fetchSchedules = async () => {
  if (!selectedGroup.value) return;
  loading.value = true;
  try {
    const response = await AcademicService.getSchedules({ study_group_id: selectedGroup.value });
    schedules.value = parseResponse(response).data;
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const getSchedulesByDayAndTime = (day: string, slot: string) => {
  // To match the translated day back to the original English day for filtering
  // This assumes a consistent mapping between translated day and original day in the backend/data
  const originalDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const translatedDays = [
    t('common.labels.days.monday'),
    t('common.labels.days.tuesday'),
    t('common.labels.days.wednesday'),
    t('common.labels.days.thursday'),
    t('common.labels.days.friday'),
    t('common.labels.days.saturday')
  ];
  const originalDay = originalDays[translatedDays.indexOf(day)];

  return schedules.value.filter(s => {
    if (s.day !== originalDay) return false;
    const startHour = s.start_time.split(':')[0];
    const slotHour = slot.split(':')[0];
    return startHour === slotHour;
  });
};

const formatTime = (time: string) => {
  return time.split(':').slice(0, 2).join(':');
};

const getRandomColor = (id: number) => {
  const colors = [
    'bg-blue-100 text-blue-700 border-blue-200',
    'bg-purple-100 text-purple-700 border-purple-200',
    'bg-emerald-100 text-emerald-700 border-emerald-200',
    'bg-amber-100 text-amber-700 border-amber-200',
    'bg-pink-100 text-pink-700 border-pink-200',
    'bg-indigo-100 text-indigo-700 border-indigo-200',
    'bg-cyan-100 text-cyan-700 border-cyan-200',
  ];
  return colors[id % colors.length];
};

const handleAdd = () => {
  selectedItem.value = {
    study_group_id: selectedGroup.value,
    day: 'Monday', // Default to Monday, dialog will handle selection
    start_time: '07:00:00',
    end_time: '08:00:00',
    is_active: true
  };
  dialogOpen.value = true;
};

const handleEdit = (item: any) => {
  selectedItem.value = { ...item };
  dialogOpen.value = true;
};

const handleSave = async (formData: any) => {
  saving.value = true;
  try {
    if (selectedItem.value?.id) {
      await AcademicService.updateSchedule(selectedItem.value.id, formData);
      toast.success.action(t('modules.school.academic.messages.scheduleUpdateSuccess'));
    } else {
      await AcademicService.storeSchedule({
          ...formData,
          school_id: 1, // Will be handled by backend usually but let's be safe
          workspace_id: 1, // Will be handled by context middleware
          academic_year_id: 1, // Should be dynamic
          semester_id: 1, // Should be dynamic
      });
      toast.success.action(t('modules.school.academic.messages.scheduleAddSuccess'));
    }
    dialogOpen.value = false;
    fetchSchedules();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (id: number) => {
    try {
        await AcademicService.deleteSchedule(id);
        toast.success.action(t('modules.school.academic.messages.scheduleDeleteSuccess'));
        dialogOpen.value = false;
        fetchSchedules();
    } catch (e) {
        toast.error.fromResponse(e);
    }
}

watch(selectedGroup, () => {
  fetchSchedules();
});

onMounted(() => {
  fetchGroups();
});
</script>
