<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.labels.edit') || 'Edit' : $t('modules.school.extensions.tabs.uks') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('modules.school.extensions.labels.patientName') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.patient_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('academic.placeholders.selectDay')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in patients"
                :key="s.id"
                :value="s.id.toString()"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="complaint">{{ $t('modules.school.extensions.labels.complaint') }} <span class="text-destructive">*</span></Label>
          <Textarea
            id="complaint"
            v-model="form.complaint"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="treatment">{{ $t('common.labels.description') }}</Label>
          <Textarea
            id="treatment"
            v-model="form.treatment"
          />
        </div>
        <div class="space-y-2">
          <Label for="medicine">{{ $t('common.labels.name') }}</Label>
          <Input
            id="medicine"
            v-model="form.medicine_given"
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
  Button, Label, Input, Textarea, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import api from '@/core/api/client';
import { parseResponse } from '@/shared/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const patients = ref<any[]>([]);
const form = ref({
  patient_type: 'student',
  patient_id: undefined as string | undefined,
  complaint: '',
  treatment: '',
  medicine_given: '',
});

watch(() => props.initialData, (val) => {
  if (val) form.value = { ...val, patient_id: val.patient_id?.toString() };
  else form.value = { patient_type: 'student', patient_id: undefined, complaint: '', treatment: '', medicine_given: '' };
}, { immediate: true });

const fetchPatients = async () => {
    try {
        const response = await api.get('/admin/students?per_page=100');
        const { data } = parseResponse(response);
        patients.value = data || [];
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', { ...form.value });
};

onMounted(fetchPatients);
</script>
