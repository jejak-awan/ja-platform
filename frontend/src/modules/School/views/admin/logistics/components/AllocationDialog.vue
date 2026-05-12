<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ $t('modules.school.logistics.hostel.labels.allocateStudentTitle') }}</DialogTitle>
        <DialogDescription>
          {{ $t('modules.school.logistics.hostel.placeholders.searchStudent') }} <strong>{{ bed?.bed_number }}</strong>.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="student">{{ $t('modules.school.logistics.hostel.actions.allocateStudent') }}</Label>
          <div class="relative">
            <Input
              v-model="search"
              :placeholder="$t('modules.school.logistics.hostel.placeholders.searchStudent')"
              @input="fetchStudents"
            />
            <div
              v-if="searching"
              class="absolute right-3 top-2.5"
            >
              <LucideIcon
                name="Loader2"
                class="w-4 h-4 animate-spin text-muted-foreground"
              />
            </div>
          </div>
          <div
            v-if="students.length > 0"
            class="mt-2 max-h-[200px] overflow-y-auto border rounded-md divide-y bg-muted/20"
          >
            <div 
              v-for="s in students" 
              :key="s.id" 
              class="p-2 hover:bg-primary/10 cursor-pointer text-sm flex justify-between items-center transition-colors"
              :class="{ 'bg-primary/5 border-primary/20': form.student_id === s.id }"
              @click="form.student_id = s.id"
            >
              <span>{{ s.full_name }}</span>
              <span class="text-[10px] text-muted-foreground">{{ s.nis }}</span>
            </div>
          </div>
        </div>
        <div class="grid gap-2">
          <Label for="start">{{ $t('modules.school.logistics.hostel.labels.occupancyStartDate') }}</Label>
          <Input
            id="start"
            v-model="form.start_date"
            type="date"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :loading="loading"
          :disabled="!form.student_id"
          @click="handleSubmit"
        >
          {{ $t('modules.school.logistics.hostel.actions.confirmAllocation') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, LucideIcon
} from '@/shared/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { StudentService } from '@/modules/School/services/StudentService';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';

const props = defineProps<{
  open: boolean;
  bed: any;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const searching = ref(false);
const search = ref('');
const students = ref<any[]>([]);

const form = ref({
  student_id: null as number | null,
  bed_id: null as number | null,
  start_date: new Date().toISOString().split('T')[0]
});

const fetchStudents = async () => {
    if (search.value.length < 2) {
        students.value = [];
        return;
    }
    searching.value = true;
    try {
        const response = await StudentService.searchStudents(search.value);
        students.value = parseResponse(response).data;
    } catch {
        // Silent error
    } finally {
        searching.value = false;
    }
}

const handleSubmit = async () => {
  if (!form.value.student_id || !props.bed?.id) return;
  
  form.value.bed_id = props.bed.id;
  loading.value = true;
  try {
    await LogisticsService.allocateBed(form.value);
    toast.success.action('Siswa berhasil dialokasikan');
    emit('save');
    emit('update:open', false);
    // Reset
    form.value = { student_id: null, bed_id: null, start_date: new Date().toISOString().split('T')[0] };
    search.value = '';
    students.value = [];
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
