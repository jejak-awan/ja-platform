<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.operations.attendance.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.operations.attendance.subtitle') }}
        </p>
      </div>
    </div>

    <Card class="mb-8">
      <CardContent class="p-6 flex flex-wrap gap-4 items-end">
        <div class="space-y-2 w-full md:w-64">
          <Label>{{ $t('features.school.operations.attendance.pilihRombel') }}</Label>
          <Select v-model="selectedGroup">
            <SelectTrigger><SelectValue :placeholder="$t('features.school.operations.attendance.pilihRombelPlaceholder')" /></SelectTrigger>
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
        <div class="space-y-2 w-full md:w-48">
          <Label>{{ $t('features.school.operations.attendance.tanggal') }}</Label>
          <Input
            v-model="selectedDate"
            type="date"
          />
        </div>
        <Button
          :disabled="!selectedGroup || loading"
          @click="fetchAttendance"
        >
          <LucideIcon
            v-if="loading"
            name="Loader2"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ $t('features.school.operations.attendance.btnShow') }}
        </Button>
      </CardContent>
    </Card>

    <Card v-if="attendance.length > 0">
      <CardContent class="p-0">
        <DataTable 
          :table="table"
          :loading="loading"
        />
        <div class="p-6 bg-muted/20 border-t flex justify-end">
          <Button
            :disabled="saving"
            @click="handleSave"
          >
            <LucideIcon
              v-if="saving"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('features.school.operations.attendance.btnSaveAll') }}
          </Button>
        </div>
      </CardContent>
    </Card>
    <div
      v-else-if="!loading && selectedGroup"
      class="text-center py-20 bg-muted/10 rounded-lg border-2 border-dashed"
    >
      <LucideIcon
        name="Calendar"
        class="w-12 h-12 mx-auto text-muted-foreground mb-4 opacity-20"
      />
      <p class="text-muted-foreground font-medium">
        {{ $t('features.school.operations.attendance.emptyHint') }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { StudentService } from '@/modules/School/services/StudentService';
import { OperationsService } from '@/modules/School/services/OperationsService';
import {
  Card, CardContent, Button, LucideIcon, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, DataTable
} from '@/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

interface AttendanceRecord {
  id?: number;
  student_id: number;
  student_name: string;
  status: string;
  notes: string;
}

const { t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const groups = ref<any[]>([]);
const selectedGroup = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const attendance = ref<AttendanceRecord[]>([]);

const columnHelper = createColumnHelper<AttendanceRecord>();

const columns = [
  columnHelper.accessor('student_name', { header: t('features.school.operations.attendance.labels.studentName') }),
  columnHelper.accessor('status', { 
    header: t('features.school.operations.attendance.labels.status'),
    cell: ({ row }) => h('div', { class: 'w-32' }, [
      h(Select, {
        modelValue: row.original.status,
        'onUpdate:modelValue': (val: any) => row.original.status = val,
      }, () => [
        h(SelectTrigger, { class: 'w-full h-8 text-xs' }, () => h(SelectValue)),
        h(SelectContent, () => [
          h(SelectItem, { value: 'H' }, () => t('features.school.operations.attendance.status.H')),
          h(SelectItem, { value: 'S' }, () => t('features.school.operations.attendance.status.S')),
          h(SelectItem, { value: 'I' }, () => t('features.school.operations.attendance.status.I')),
          h(SelectItem, { value: 'A' }, () => t('features.school.operations.attendance.status.A')),
        ])
      ])
    ])
  }),
  columnHelper.accessor('notes', { 
    header: t('features.school.operations.attendance.labels.notes'),
    cell: ({ row }) => h(Input, {
      modelValue: row.original.notes,
      placeholder: t('common.placeholders.notesHint'),
      class: 'h-8 text-xs',
      'onUpdate:modelValue': (val: any) => row.original.notes = val
    })
  }),
];

const table = useVueTable({
  get data() { return attendance.value },
  columns,
  getCoreRowModel: getCoreRowModel(),
});

const fetchGroups = async () => {
  try {
    const response = await AcademicService.getStudyGroups();
    groups.value = parseResponse(response).data;
  } catch (e) {
    toast.error.fromResponse(e);
  }
};

const fetchAttendance = async () => {
    if(!selectedGroup.value) return;
    loading.value = true;
    try {
        // First, fetch existing attendance for this date and group
        const attRes = await OperationsService.getAttendances({ 
            group_id: selectedGroup.value, 
            date: selectedDate.value 
        });
        const existingData = parseResponse(attRes).data as any[];
        
        // Then, fetch all students in this group to ensure everyone is listed
        const stuRes = await StudentService.getStudents({ 
            study_group_id: selectedGroup.value, 
            per_page: 100 
        });
        const groupStudents = parseResponse(stuRes).data;
        
        attendance.value = groupStudents.map((s: any) => {
            const existing = existingData.find((a: any) => a.student_id === s.id);
            return {
                id: existing?.id,
                student_id: s.id,
                student_name: s.full_name,
                status: existing?.status || 'H',
                notes: existing?.notes || ''
            };
        });
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
};

const handleSave = async () => {
    saving.value = true;
    try {
        // In a real app, you might have a bulk endpoint. 
        // Here we'll just loop for simplicity if no bulk endpoint, but let's assume we can handle multiple.
        // For now, let's do sequential or Promise.all if the server can handle it.
        const promises = attendance.value.map(record => {
            const statusMap: Record<string, "sick" | "present" | "absent" | "late" | "permit"> = {
                'H': 'present',
                'S': 'sick',
                'I': 'permit',
                'A': 'absent'
            };
            const data = {
                student_id: record.student_id,
                date: selectedDate.value,
                status: statusMap[record.status] || 'present',
                notes: record.notes,
                school_id: 1,
                academic_year_id: 1, // Should be dynamic
                semester_id: 1, // Should be dynamic
            };
            if (record.id) {
                return OperationsService.updateAttendance(record.id, data);
            } else {
                return OperationsService.storeAttendance(data);
            }
        });
        
        await Promise.all(promises);
        toast.success.action(t('features.school.operations.attendance.messages.saveSuccess'));
        fetchAttendance();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
  fetchGroups();
});
</script>
