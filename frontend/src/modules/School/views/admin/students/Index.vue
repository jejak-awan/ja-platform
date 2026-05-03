<template>
  <div class="pb-8">
    <div class="px-6 space-y-8 animate-in fade-in duration-700">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-sm">
              <LucideIcon
                name="Users"
                class="w-6 h-6 text-primary"
              />
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
              {{ $t('features.school.students.title') }}
            </h1>
          </div>
          <div class="flex items-center gap-2">
            <Badge variant="secondary" class="bg-primary/10 text-primary border-primary/20 shrink-0 font-bold">
              {{ pagination?.total || 0 }} {{ $t('features.school.students.title') }}
            </Badge>
            <p class="text-sm text-muted-foreground italic font-medium">
              {{ $t('features.school.students.subtitle') }}
            </p>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
          <Button
            variant="outline"
            size="lg"
            class="rounded-xl border-border/50 hover:bg-accent/50 transition-colors h-11 px-6 font-bold"
            :disabled="loading || students.length === 0"
            @click="handleExport"
          >
            <LucideIcon name="Download" class="w-5 h-5 mr-2" />
            {{ $t('features.school.students.actions.export') }}
          </Button>

          <Button
            variant="secondary"
            size="lg"
            class="rounded-xl bg-muted/50 hover:bg-muted/80 transition-colors h-11 px-6 font-bold border border-border/30"
            @click="handleImport"
          >
            <LucideIcon name="Upload" class="w-5 h-5 mr-2" />
            {{ $t('features.school.students.actions.import') }}
          </Button>

          <Button 
            size="lg" 
            class="rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 font-bold h-11 px-8"
            @click="handleAdd"
          >
            <LucideIcon name="UserPlus" class="w-5 h-5 mr-2" />
            {{ $t('features.school.students.actions.add') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filter Sidebar -->
        <div class="lg:col-span-1 space-y-4">
          <Card class="border-border/50 bg-background/50 backdrop-blur-sm shadow-sm rounded-2xl">
            <CardHeader class="pb-4 border-b border-border/30 bg-muted/20">
              <CardTitle class="text-sm font-bold flex items-center">
                <LucideIcon name="Filter" class="w-4 h-4 mr-2 text-primary" />
                {{ $t('common.labels.filter_data') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-6 space-y-6 text-left">
              <div class="space-y-2">
                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('common.labels.search') }}</Label>
                <Input 
                  v-model="filters.search" 
                  :placeholder="$t('common.labels.search_name_nisn')" 
                  class="bg-background/50 border-border/50 rounded-xl h-11"
                  @input="handleFilter"
                >
                  <template #prefix>
                    <LucideIcon name="Search" class="w-4 h-4 text-muted-foreground" />
                  </template>
                </Input>
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('common.labels.level') }}</Label>
                <Select v-model="filters.level_id" @update:model-value="handleFilter">
                  <SelectTrigger class="bg-background/50 border-border/50 rounded-xl h-11">
                    <SelectValue :placeholder="$t('features.school.academic.placeholders.selectLevel')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="l in levels" :key="l.id" :value="l.id.toString()">{{ l.name }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2">
                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('common.labels.class_group') }}</Label>
                <Select v-model="filters.study_group_id" @update:model-value="handleFilter">
                  <SelectTrigger class="bg-background/50 border-border/50 rounded-xl h-11">
                    <SelectValue :placeholder="$t('features.school.academic.placeholders.selectGroup')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="g in groups" :key="g.id" :value="g.id.toString()">{{ g.name }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <Button variant="ghost" class="w-full text-xs hover:bg-destructive/10 hover:text-destructive rounded-xl h-10 transition-colors" @click="resetFilters">
                  {{ $t('common.labels.reset_filter') }}
              </Button>
            </CardContent>
          </Card>
        </div>

        <!-- Table Section -->
        <div class="lg:col-span-3">
          <Card class="overflow-hidden border-border/50 bg-background/50 backdrop-blur-sm shadow-sm rounded-2xl h-full flex flex-col">
            <CardContent class="p-0 flex-1">
              <DataTable
                :table="table"
                :loading="loading"
                class="border-none"
              />
            </CardContent>
            
            <div class="p-6 bg-muted/20 border-t border-border/50 flex flex-col sm:flex-row justify-between items-center gap-4 mt-auto">
              <div class="flex items-center gap-3">
                <div class="h-8 w-1 bg-primary rounded-full"></div>
                <p class="text-xs font-black text-muted-foreground uppercase tracking-widest">
                  {{ $t('features.school.hr.staff.labels.total', { total: pagination?.total || 0 }) }}
                </p>
              </div>
              
              <Pagination
                v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
                :total-items="pagination.total || 0"
                :per-page="pagination.per_page || 20"
                :current-page="pagination.current_page || 1"
                @page-change="handlePageChange"
                class="w-full sm:w-auto"
              />
            </div>
          </Card>
        </div>
      </div>

      <!-- Dialogs -->
      <StudentDialog 
        v-model:open="dialogs.student"
        :is-edit="!!selectedStudent"
        :initial-data="selectedStudent"
        :loading="saving"
        @submit="handleSave"
      />
      
      <ConfirmModal ref="confirmModal" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { StudentService } from '@/modules/School/services/StudentService';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { useAcademicStore } from '@/modules/School/stores/academic';
import {
  Card, CardContent, CardHeader, CardTitle, Button, LucideIcon, DataTable, Pagination, Badge, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Input, ConfirmModal
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { exportToCSV } from '@/utils/exportUtils';
import StudentDialog from './components/StudentForm.vue';
import { parseResponse } from '@/utils/responseParser';
import _ from 'lodash';

const { t } = useI18n();
const toast = useToast();
const academicStore = useAcademicStore();

const students = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const confirmModal = ref<any>(null);
const levels = ref<any[]>([]);
const groups = ref<any[]>([]);

const filters = ref({
  search: '',
  level_id: '',
  study_group_id: '',
});

const dialogs = ref({ student: false });
const selectedStudent = ref<any>(null);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.accessor('full_name', { 
    header: t('features.school.students.labels.fullName'),
    cell: ({ row }) => h('div', { class: 'flex items-center gap-3' }, [
      h('div', { class: 'w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold border border-primary/20' }, 
        row.original.full_name.charAt(0)
      ),
      h('div', { class: 'flex flex-col text-left' }, [
        h('span', { class: 'font-semibold text-foreground' }, row.original.full_name),
        h('span', { class: 'text-[10px] text-muted-foreground uppercase tracking-widest font-black' }, `NISN: ${row.original.nisn || '-'}`)
      ])
    ])
  }),
  columnHelper.accessor('study_group.name', { 
    header: t('features.school.students.labels.class'),
    cell: info => h(Badge, { variant: 'outline', class: 'bg-accent/30 font-bold border-border/50 text-[10px] uppercase tracking-widest' }, () => info.getValue() || '-')
  }),
  columnHelper.accessor('gender', { 
    header: t('features.school.students.labels.gender'),
    cell: info => {
      const val = info.getValue()?.toLowerCase();
      const label = val === 'p' ? t('common.genders.female') : t('common.genders.male');
      return h('span', { class: 'text-xs font-medium' }, label);
    }
  }),
  columnHelper.display({
    id: 'actions',
    header: '',
    cell: ({ row }) => h('div', { class: 'flex justify-end gap-1 px-2' }, [
      h(Button, {
        variant: 'ghost', size: 'icon', class: 'h-8 w-8 hover:bg-primary/10 hover:text-primary transition-colors rounded-lg',
        onClick: () => handleEdit(row.original)
      }, () => h(LucideIcon, { name: 'Edit2', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost', size: 'icon', class: 'h-8 w-8 hover:bg-destructive/10 hover:text-destructive transition-colors rounded-lg',
        onClick: () => handleDelete(row.original)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ]),
  }),
];

const table = useVueTable({
  get data() { return students.value },
  get columns() { return columns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const response = await StudentService.getStudents({ page, ...filters.value });
    const { data, pagination: pagin } = parseResponse(response);
    students.value = data;
    if (pagin) {
      pagination.value = {
        total: pagin.total || 0,
        per_page: pagin.per_page || 20,
        current_page: pagin.current_page || 1,
      };
    }
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const fetchFilters = async () => {
    try {
        const lvRes = await InstitutionService.getUnits();
        await academicStore.fetchStudyGroups();
        levels.value = parseResponse(lvRes).data;
        groups.value = academicStore.studyGroups;
    } catch (e) { console.error(e); }
}

const handleFilter = _.debounce(() => {
    fetchData(1);
}, 500);

const resetFilters = () => {
    filters.value = { search: '', level_id: '', study_group_id: '' };
    fetchData(1);
}

const handlePageChange = (page: number) => {
  fetchData(page);
};

const handleAdd = () => {
    selectedStudent.value = null;
    dialogs.value.student = true;
}

const handleEdit = (item: any) => {
    selectedStudent.value = item;
    dialogs.value.student = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        if (selectedStudent.value) {
            await StudentService.updateStudent(selectedStudent.value.id, formData);
        } else {
            await StudentService.createStudent(formData);
        }
        toast.success.action(t('features.school.students.messages.saveSuccess'));
        dialogs.value.student = false;
        fetchData(pagination.value.current_page);
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDelete = async (item: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: t('common.actions.delete'),
        message: t('features.school.students.messages.deleteConfirm', { name: item.full_name }),
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            await StudentService.deleteStudent(item.id);
            toast.success.action(t('features.school.students.messages.deleteSuccess'));
            fetchData(pagination.value.current_page);
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
};

const handleExport = () => {
    exportToCSV(students.value, 'data-siswa');
}

const handleImport = () => {
    toast.info('Info', 'Fitur import sedang dikembangkan.');
}

onMounted(() => {
  fetchData();
  fetchFilters();
});
</script>
