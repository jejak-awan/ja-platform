<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.lms.actions.editBank') : $t('features.school.lms.actions.addBank') }}</DialogTitle>
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
          <Label for="name">{{ $t('features.school.lms.labels.bankName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            :placeholder="$t('features.school.lms.placeholders.bankNameHint')"
            required
          />
        </div>
        <div class="space-y-2">
          <Label for="description">{{ $t('common.labels.description') }}</Label>
          <Textarea
            id="description"
            v-model="form.description"
            rows="3"
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
  name: '',
  description: '',
});

watch(() => props.initialData, (val) => {
  if (val) {
    form.value = { 
      ...val,
      subject_id: val.subject_id ? String(val.subject_id) : undefined
    };
  }
  else form.value = { subject_id: undefined, name: '', description: '' };
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
