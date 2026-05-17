<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('modules.school.academic.tabs.timetable') : $t('common.actions.add') + ' ' + $t('modules.school.academic.tabs.timetable') }}</DialogTitle>
        <DialogDescription>
          {{ $t('modules.school.academic.messages.setupScheduleDescription') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label>{{ $t('modules.school.academic.labels.day') }}</Label>
          <Select v-model="form.day">
            <SelectTrigger>
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.selectDay')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="Monday">
                {{ $t('common.days.monday') }}
              </SelectItem>
              <SelectItem value="Tuesday">
                {{ $t('common.days.tuesday') }}
              </SelectItem>
              <SelectItem value="Wednesday">
                {{ $t('common.days.wednesday') }}
              </SelectItem>
              <SelectItem value="Thursday">
                {{ $t('common.days.thursday') }}
              </SelectItem>
              <SelectItem value="Friday">
                {{ $t('common.days.friday') }}
              </SelectItem>
              <SelectItem value="Saturday">
                {{ $t('common.days.saturday') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label>{{ $t('modules.school.academic.labels.subject') }}</Label>
          <Select v-model="form.subject_id">
            <SelectTrigger>
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.selectSubject')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in subjects"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.name }} ({{ s.code }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label>{{ $t('modules.school.academic.labels.teacher') }}</Label>
          <Select v-model="form.staff_id">
            <SelectTrigger>
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.selectTeacher')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in staff"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label>{{ $t('modules.school.academic.labels.startTime') }}</Label>
            <Input
              v-model="form.start_time"
              type="time"
            />
          </div>
          <div class="grid gap-2">
            <Label>{{ $t('modules.school.academic.labels.endTime') }}</Label>
            <Input
              v-model="form.end_time"
              type="time"
            />
          </div>
        </div>

        <div class="grid gap-2">
          <Label>{{ $t('modules.school.academic.labels.room') }}</Label>
          <Select v-model="form.room_id">
            <SelectTrigger>
              <SelectValue :placeholder="$t('modules.school.academic.placeholders.selectRoom')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="r in rooms"
                :key="r.id"
                :value="String(r.id)"
              >
                {{ r.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <DialogFooter class="flex justify-between items-center sm:justify-between">
        <Button
          v-if="isEdit"
          variant="destructive"
          size="sm"
          @click="handleDelete"
        >
          <LucideIcon
            name="Trash2"
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.delete') }}
        </Button>
        <div class="flex gap-2">
          <Button
            variant="ghost"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            :disabled="loading"
            @click="handleSubmit"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.actions.save') }} {{ $t('modules.school.academic.tabs.timetable') }}
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { HRService } from '@/modules/School/services/HRService';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/shared/components/ui';
import { parseResponse } from '@/shared/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit: boolean;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit', 'delete']);

const form = ref<any>({
  day: 'Monday',
  subject_id: '',
  staff_id: '',
  start_time: '07:00',
  end_time: '08:00',
  room_id: '',
  study_group_id: '',
});

const subjects = ref<any[]>([]);
const staff = ref<any[]>([]);
const rooms = ref<any[]>([]);

const fetchOptions = async () => {
    try {
        const [subjRes, staffRes, roomRes] = await Promise.all([
            AcademicService.getSubjects(),
            HRService.getStaff(),
            LogisticsService.getSarprasRooms(),
        ]);
        subjects.value = parseResponse(subjRes).data;
        staff.value = parseResponse(staffRes).data;
        rooms.value = parseResponse(roomRes).data;
    } catch (e) {
        console.error(e);
    }
}

watch(() => props.open, (newVal) => {
  if (newVal && props.initialData) {
    form.value = { 
      ...props.initialData,
      subject_id: props.initialData.subject_id ? String(props.initialData.subject_id) : '',
      staff_id: props.initialData.staff_id ? String(props.initialData.staff_id) : '',
      room_id: props.initialData.room_id ? String(props.initialData.room_id) : '',
      start_time: props.initialData.start_time?.slice(0, 5) || '07:00',
      end_time: props.initialData.end_time?.slice(0, 5) || '08:00',
    };
  }
});

const handleSubmit = () => {
  emit('submit', { 
      ...form.value,
      start_time: form.value.start_time + ':00',
      end_time: form.value.end_time + ':00'
  });
};

const handleDelete = () => {
    emit('delete', props.initialData.id);
}

onMounted(() => {
    fetchOptions();
});
</script>
