<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.labels.edit') || 'Edit' : $t('features.school.extensions.tabs.alumni') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('common.labels.student') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.student_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('academic.placeholders.selectStudentToAdd')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in students"
                :key="s.id"
                :value="String(s.id)"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="year">{{ $t('features.school.extensions.labels.graduationYear') }} <span class="text-destructive">*</span></Label>
          <Input
            id="year"
            v-model="form.graduation_year"
            type="number"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="activity">{{ $t('features.school.extensions.labels.currentActivity') }}</Label>
          <Input
            id="activity"
            v-model="form.current_activity"
            :placeholder="$t('academic.placeholders.notesHint')"
          />
        </div>
        <div class="space-y-2">
          <Label for="inst">{{ $t('features.school.extensions.labels.companyName') }} / Campus</Label>
          <Input
            id="inst"
            v-model="form.institution_name"
          />
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
  Button, Label, Input, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const students = ref<any[]>([]);
const form = ref({
  student_id: undefined as string | undefined,
  graduation_year: new Date().getFullYear(),
  current_activity: '',
  institution_name: '',
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val, student_id: val.student_id?.toString() };
  else form.value = { student_id: undefined, graduation_year: new Date().getFullYear(), current_activity: '', institution_name: '' };
}, { immediate: true });

const fetchStudents = async () => {
    try {
        const response = await api.get('/admin/students?per_page=100');
        const { data } = parseResponse(response);
        students.value = data || [];
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(fetchStudents);
</script>
