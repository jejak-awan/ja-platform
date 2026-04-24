<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.lms.actions.editExam') : $t('features.school.lms.actions.addExam') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.academic.labels.subject') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.subject_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.lms.placeholders.selectSubject')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in subjects"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="title">{{ $t('features.school.lms.labels.examName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="title"
            v-model="form.title"
            :placeholder="$t('features.school.lms.placeholders.examTitleHint')"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="duration">{{ $t('features.school.lms.labels.duration') }} ({{ $t('features.school.lms.labels.minutes') }}) <span class="text-destructive">*</span></Label>
            <Input
              id="duration"
              v-model="form.duration_minutes"
              type="number"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="grade">{{ $t('features.school.lms.labels.passingGrade') }}</Label>
            <Input
              id="grade"
              v-model="form.passing_grade"
              type="number"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="start">{{ $t('features.school.lms.labels.startTime') }} <span class="text-destructive">*</span></Label>
            <Input
              id="start"
              v-model="form.start_time"
              type="datetime-local"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="end">{{ $t('features.school.lms.labels.endTime') }} <span class="text-destructive">*</span></Label>
            <Input
              id="end"
              v-model="form.end_time"
              type="datetime-local"
              required
            />
          </div>
        </div>
        <div class="flex items-center space-x-2 pt-2">
          <Switch
            id="active"
            :checked="form.is_active"
            @update:checked="v => form.is_active = v"
          />
          <Label for="active">{{ $t('features.school.lms.labels.activateExam') }}</Label>
        </div>
        <DialogFooter>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.labels.save') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Switch
} from '@/components/ui';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const subjects = ref<any[]>([]);
const form = ref({
  subject_id: undefined as string | undefined,
  title: '',
  duration_minutes: 60,
  passing_grade: 75,
  start_time: '',
  end_time: '',
  is_active: false,
});

watch(() => props.initialData, (val) => {
  if (val) {
      form.value = { 
          ...val,
          subject_id: val.subject_id ? String(val.subject_id) : undefined,
          start_time: val.start_time ? new Date(val.start_time).toISOString().slice(0, 16) : '',
          end_time: val.end_time ? new Date(val.end_time).toISOString().slice(0, 16) : '',
      };
  }
  else {
      form.value = { subject_id: undefined, title: '', duration_minutes: 60, passing_grade: 75, start_time: '', end_time: '', is_active: false };
  }
}, { immediate: true });

const fetchMetadata = async () => {
    try {
        const response = await AcademicService.getSubjects();
        subjects.value = parseResponse(response).data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', { ...form.value, school_id: 1 });
};

onMounted(fetchMetadata);
</script>
