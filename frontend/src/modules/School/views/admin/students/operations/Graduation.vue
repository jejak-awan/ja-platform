<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20 shadow-sm">
            <LucideIcon
              name="GraduationCap"
              class="w-6 h-6 text-primary"
            />
          </div>
          <h2 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('graduation.title') }}
          </h2>
        </div>
        <p class="text-sm text-muted-foreground italic font-medium">
          {{ $t('graduation.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <router-link :to="{ name: 'settings.document-templates', query: { type: 'skl' } }">
          <Button
            variant="outline"
            class="rounded-xl shadow-sm h-11 px-6 font-bold"
          >
            <LucideIcon
              name="FileText"
              class="w-4 h-4 mr-2"
            />
            {{ $t('graduation.labels.manageTemplates') }}
          </Button>
        </router-link>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="grid w-full grid-cols-3 lg:w-[600px] h-12 bg-muted/50 p-1 rounded-xl">
        <TabsTrigger value="eligible" class="rounded-lg font-bold">
          {{ $t('graduation.tabs.eligible') }}
        </TabsTrigger>
        <TabsTrigger value="history" class="rounded-lg font-bold">
          {{ $t('graduation.tabs.history') }}
        </TabsTrigger>
        <TabsTrigger value="settings" class="rounded-lg font-bold">
          {{ $t('graduation.tabs.settings') }}
        </TabsTrigger>
      </TabsList>

      <TabsContent
        value="eligible"
        class="mt-6 space-y-4"
      >
        <Card class="border border-border/40 shadow-sm rounded-2xl overflow-hidden">
          <CardContent class="p-6 space-y-6">
            <div class="flex flex-wrap gap-4 items-end bg-muted/10 p-4 rounded-xl border border-border/30">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.batchYear') }}</label>
                <Select v-model="batchYear">
                  <SelectTrigger class="w-[140px] rounded-xl h-11 bg-background">
                    <SelectValue :placeholder="String(new Date().getFullYear())" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="year in years"
                      :key="year"
                      :value="String(year)"
                    >
                      {{ year }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.targetStatus') }}</label>
                <Select v-model="batchStatus">
                  <SelectTrigger class="w-[200px] rounded-xl h-11 bg-background">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="graduated">{{ $t('common.status.completed') }}</SelectItem>
                    <SelectItem value="not_graduated">{{ $t('common.status.rejected') }}</SelectItem>
                    <SelectItem value="deferred">{{ $t('common.status.pending') }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <Button
                v-if="selectedRows.length > 0"
                variant="default"
                class="rounded-xl shadow-sm px-8 h-11 font-bold bg-success hover:bg-success/90 ml-auto"
                :loading="processing"
                @click="handleProcess"
              >
                <LucideIcon
                  name="CircleCheck"
                  class="w-5 h-5 mr-2"
                />
                {{ $t('graduation.labels.processSelected', { count: selectedRows.length }) }}
              </Button>
            </div>

            <div class="p-3 bg-primary/5 border border-primary/10 rounded-xl text-[11px] font-bold uppercase tracking-wider text-primary flex items-center gap-2">
              <LucideIcon
                name="Info"
                class="w-4 h-4"
              />
              {{ $t('graduation.labels.eligibleHint') }}
            </div>

            <DataTable
              :table="table"
              :loading="loading"
            />
          </CardContent>
        </Card>
      </TabsContent>

      <TabsContent
        value="history"
        class="mt-6"
      >
        <Card class="border border-border/40 shadow-sm rounded-2xl overflow-hidden">
          <CardContent class="p-0">
            <div class="p-6 flex flex-wrap justify-between items-center bg-muted/10 border-b border-border/40 gap-4">
              <div class="flex items-center gap-3">
                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.graduationYear') }}</label>
                <Select v-model="filterYear">
                  <SelectTrigger class="w-[140px] rounded-xl h-10 bg-background">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="year in years"
                      :key="year"
                      :value="String(year)"
                    >
                      {{ year }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="flex gap-2">
                <Button
                  variant="outline"
                  size="default"
                  class="rounded-xl h-10 px-6 font-bold"
                  @click="importModalOpen = true"
                >
                  <LucideIcon
                    name="Upload"
                    class="w-4 h-4 mr-2 text-primary"
                  />
                  {{ $t('graduation.labels.importGrades') }}
                </Button>
                <Button
                  variant="outline"
                  size="default"
                  class="rounded-xl h-10 px-6 font-bold"
                  @click="fetchHistory"
                >
                  <LucideIcon
                    name="RefreshCcw"
                    class="w-4 h-4 mr-2"
                  />
                  {{ $t('common.actions.refresh') }}
                </Button>
              </div>
            </div>
            <DataTable
              :table="historyTable"
              :loading="loadingHistory"
            />
          </CardContent>
        </Card>
      </TabsContent>

      <TabsContent
        value="settings"
        class="mt-6"
      >
        <GraduationSettings />
      </TabsContent>
    </Tabs>

    <ConfirmModal ref="confirmModal" />
    
    <!-- Edit Grade Modal -->
    <Dialog v-model:open="gradeModalOpen">
      <DialogContent class="sm:max-w-[600px] rounded-2xl max-h-[90vh] overflow-hidden flex flex-col p-0">
        <DialogHeader class="p-6 border-b border-border/40">
          <DialogTitle>{{ $t('graduation.labels.editGrades') }}</DialogTitle>
          <DialogDescription v-if="editingResult" class="font-bold text-primary">
            {{ editingResult.student?.full_name }} ({{ editingResult.student?.nisn }})
          </DialogDescription>
        </DialogHeader>
        
        <div 
          v-if="editingResult" 
          class="p-6 space-y-6 flex-1 overflow-y-auto"
        >
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('common.labels.status') }}</label>
              <Select v-model="editingResult.status">
                <SelectTrigger class="rounded-xl h-11">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="graduated">{{ $t('common.status.completed') }}</SelectItem>
                  <SelectItem value="not_graduated">{{ $t('common.status.rejected') }}</SelectItem>
                  <SelectItem value="deferred">{{ $t('common.status.pending') }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.certificateNumber') }}</label>
              <Input
                v-model="editingResult.certificate_number"
                :placeholder="$t('graduation.labels.certificateNumberPlaceholder')"
                class="rounded-xl h-11"
              />
            </div>
          </div>

          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.gradeDetails') }}</label>
              <Button 
                variant="ghost" 
                size="sm" 
                class="h-8 px-3 rounded-lg text-primary font-bold hover:bg-primary/5"
                @click="addGradeField"
              >
                <LucideIcon name="Plus" class="w-3.5 h-3.5 mr-1" />
                {{ $t('graduation.labels.addSubject') }}
              </Button>
            </div>
            <div class="max-h-[300px] overflow-y-auto space-y-3 pr-2 custom-scrollbar">
              <div 
                v-for="(_, key) in editingResult.grades" 
                :key="key" 
                class="flex gap-3 animate-in fade-in slide-in-from-left-2 duration-300"
              >
                <Input 
                  v-model="tempGradeKeys[key]" 
                  :placeholder="$t('common.placeholders.subject_name')" 
                  class="flex-1 rounded-xl h-10 text-sm font-medium"
                  @blur="updateGradeKey(key as string)"
                />
                <Input 
                  v-model="editingResult.grades[key]" 
                  placeholder="0" 
                  type="number" 
                  class="w-24 rounded-xl h-10 text-sm font-black text-center"
                />
                <Button 
                  variant="ghost" 
                  size="icon" 
                  class="h-10 w-10 text-destructive hover:bg-destructive/10 rounded-xl shrink-0"
                  @click="removeGradeField(key as string)"
                >
                  <LucideIcon name="Trash2" class="w-4 h-4" />
                </Button>
              </div>
              <div v-if="Object.keys(editingResult.grades || {}).length === 0" class="text-center py-10 bg-muted/20 rounded-2xl border-2 border-dashed border-border/50 text-muted-foreground text-xs italic font-medium">
                {{ $t('graduation.labels.noGrades') }}
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 border-t border-border/40 bg-muted/10">
          <Button 
            variant="outline" 
            class="rounded-xl h-11 px-8 font-bold" 
            @click="gradeModalOpen = false"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button 
            variant="default" 
            class="rounded-xl h-11 px-10 font-bold shadow-sm" 
            :loading="savingResult"
            @click="saveResult"
          >
            <LucideIcon name="Save" class="w-4 h-4 mr-2" />
            {{ $t('common.actions.save') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Import Grades Modal -->
    <Dialog v-model:open="importModalOpen">
      <DialogContent class="sm:max-w-[500px] rounded-2xl p-0 overflow-hidden">
        <DialogHeader class="p-6 border-b border-border/40">
          <DialogTitle>{{ $t('graduation.labels.importTitle') }}</DialogTitle>
          <DialogDescription>
            {{ $t('graduation.labels.importDescription') }}
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-6">
          <div class="p-8 border-2 border-dashed border-border rounded-2xl text-center space-y-4 bg-muted/10 hover:bg-muted/20 transition-colors cursor-pointer group" @click="($refs.importFileInput as HTMLInputElement)?.click()">
            <div class="w-16 h-16 rounded-2xl bg-background mx-auto flex items-center justify-center shadow-sm border border-border group-hover:scale-110 transition-transform">
              <LucideIcon name="CloudUpload" class="w-8 h-8 text-primary" />
            </div>
            <input
              ref="importFileInput"
              type="file"
              accept=".csv,.xlsx,.xls"
              class="hidden"
              @change="onImportFileSelected"
            />
            <div class="space-y-1">
              <p class="text-sm font-bold">{{ importFile ? importFile.name : $t('graduation.labels.chooseFile') }}</p>
              <p class="text-[10px] text-muted-foreground uppercase font-black tracking-widest">CSV (.csv) / Excel (.xlsx)</p>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('graduation.labels.graduationYear') }}</label>
            <Select v-model="importYear">
              <SelectTrigger class="rounded-xl h-11">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="year in years" :key="year" :value="String(year)">{{ year }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10">
            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">{{ $t('graduation.labels.formatHint') }}</p>
            <div class="font-mono text-[10px] text-muted-foreground space-y-1 bg-background/50 p-3 rounded-lg border border-border/30">
              <p>NISN, Matematika, B. Indonesia, B. Inggris, ...</p>
              <p class="text-primary/70">0012345678, 85, 90, 88, ...</p>
              <p class="text-primary/70">0012345679, 92, 78, 95, ...</p>
            </div>
          </div>

          <div v-if="importErrors.length > 0" class="p-4 bg-destructive/5 rounded-xl border border-destructive/10 max-h-[150px] overflow-y-auto">
            <p class="text-[10px] font-black uppercase text-destructive mb-2">Errors:</p>
            <ul class="space-y-1">
              <li v-for="(err, i) in importErrors" :key="i" class="text-[10px] text-destructive font-medium flex items-start gap-2">
                <span class="block w-1 h-1 rounded-full bg-destructive mt-1 shrink-0"></span>
                {{ err }}
              </li>
            </ul>
          </div>
        </div>

        <DialogFooter class="p-6 bg-muted/10 border-t border-border/40">
          <Button variant="outline" class="rounded-xl h-11 px-8 font-bold" @click="importModalOpen = false">{{ $t('common.actions.cancel') }}</Button>
          <Button 
            variant="default" 
            class="rounded-xl h-11 px-10 font-bold shadow-sm" 
            :loading="importing"
            :disabled="!importFile"
            @click="handleImport"
          >
            <LucideIcon name="Upload" class="w-4 h-4 mr-2" />
            {{ $t('graduation.labels.importGrades') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
} from '@tanstack/vue-table';
import {
  Card, CardContent, Button, LucideIcon, DataTable, ConfirmModal, Checkbox,
  Tabs, TabsList, TabsTrigger, TabsContent,
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Input
} from '@/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';
import GraduationSettings from '@/modules/School/components/operations/GraduationSettings.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const loading = ref(false);
const processing = ref(false);
const students = ref<any[]>([]);
const rowSelection = ref({});

const activeTab = ref('eligible');
const batchYear = ref(String(new Date().getFullYear()));
const batchStatus = ref('graduated');
const filterYear = ref(String(new Date().getFullYear()));

const years = computed(() => {
    const current = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => current - i);
});

// History Logic
const history = ref<any[]>([]);
const loadingHistory = ref(false);
const fetchHistory = async () => {
    loadingHistory.value = true;
    try {
        const response = await OperationsService.getGraduationResults({ year: filterYear.value });
        const parsed = parseResponse(response) as any;
        history.value = parsed.data?.data || parsed.data || [];
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loadingHistory.value = false;
    }
};

watch(activeTab, (val: string) => {
    if (val === 'history') fetchHistory();
    else fetchEligible();
});

watch(filterYear, fetchHistory);

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.display({
    id: 'select',
    header: ({ table }) => h(Checkbox, {
      checked: table.getIsAllPageRowsSelected(),
      'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
      ariaLabel: 'Select all',
    }),
    cell: ({ row }) => h(Checkbox, {
      checked: row.getIsSelected(),
      'onUpdate:checked': (value: boolean) => row.toggleSelected(!!value),
      ariaLabel: 'Select row',
    }),
    size: 50,
  }),
  columnHelper.accessor('nisn', { 
    header: 'NISN',
    cell: info => h('span', { class: 'font-mono text-xs font-black tracking-widest text-primary' }, info.getValue() || '-')
  }),
  columnHelper.accessor('full_name', { 
    header: t('common.labels.name'),
    cell: info => h('span', { class: 'font-bold' }, info.getValue())
  }),
  columnHelper.accessor('gender', {
    header: t('common.labels.gender'),
    cell: info => {
      const val = info.getValue()?.toLowerCase();
      const label = val === 'p' ? t('common.genders.female') : t('common.genders.male');
      return h('span', { class: 'text-xs font-medium' }, label);
    }
  }),
  columnHelper.accessor('level.name', { 
    header: t('common.labels.single_level'),
    cell: info => h('span', { class: 'text-xs font-bold text-muted-foreground' }, info.getValue())
  }),
  columnHelper.accessor('status', { 
    header: t('common.labels.status'),
    cell: info => h('span', { class: 'px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/20' }, info.getValue())
  }),
];

const historyColumns = [
  columnHelper.accessor('student.full_name', { 
    header: t('common.labels.name'),
    cell: info => h('div', [
        h('div', { class: 'font-bold text-sm' }, info.getValue()),
        h('div', { class: 'text-[10px] text-muted-foreground font-black tracking-widest uppercase' }, info.row.original.student?.nisn)
    ])
  }),
  columnHelper.accessor('status', { 
    header: t('common.labels.status'),
    cell: info => {
        const val = info.getValue() as string;
        const colors: any = { graduated: 'bg-success/10 text-success border-success/20', not_graduated: 'bg-destructive/10 text-destructive border-destructive/20', deferred: 'bg-warning/10 text-warning border-warning/20' };
        const labelMap: any = { graduated: t('common.status.completed'), not_graduated: t('common.status.rejected'), deferred: t('common.status.pending') };
        return h('span', { class: `px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] border ${colors[val] || 'bg-muted border-border'}` }, labelMap[val] || val);
    }
  }),
  columnHelper.accessor('certificate_number', { 
    header: t('graduation.labels.certificateNumber'),
    cell: info => h('span', { class: 'text-xs font-mono font-bold text-muted-foreground' }, info.getValue() || '-')
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.actions'),
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
        h(Button, {
            variant: 'ghost',
            size: 'sm',
            class: 'h-8 px-3 rounded-lg text-primary font-bold hover:bg-primary/10',
            onClick: () => openGradeModal(row.original)
        }, () => [h(LucideIcon, { name: 'Edit3', class: 'w-3.5 h-3.5 mr-1' }), t('common.actions.edit')]),
        h(Button, {
            variant: 'ghost',
            size: 'icon',
            class: 'h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary',
            disabled: row.original.status !== 'graduated',
            onClick: () => downloadCertificate(row.original.student_id as number)
        }, () => h(LucideIcon, { name: 'Download', class: 'w-4 h-4' }))
    ])
  })
];

const table = useVueTable({
  get data() { return students.value },
  get columns() { return columns },
  getCoreRowModel: getCoreRowModel(),
  state: {
    get rowSelection() { return rowSelection.value },
  },
  enableRowSelection: true,
  onRowSelectionChange: (updaterOrValue: any) => {
    rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;
  },
});

const historyTable = useVueTable({
    get data() { return history.value },
    get columns() { return historyColumns },
    getCoreRowModel: getCoreRowModel(),
});

const selectedRows = computed(() => {
  return table.getSelectedRowModel().flatRows.map(row => row.original);
});

const fetchEligible = async () => {
  loading.value = true;
  try {
    const response = await OperationsService.getEligibleGraduates();
    const parsed = parseResponse(response) as any;
    students.value = parsed.data?.data || parsed.data || [];
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const handleProcess = async () => {
  const count = selectedRows.value.length;
  const confirmed = await confirmModal.value.confirm({
    title: t('graduation.messages.processConfirmTitle'),
    message: t('graduation.messages.processConfirmMessage', { count, status: batchStatus.value, year: batchYear.value }),
    variant: 'default'
  });

  if (confirmed) {
    processing.value = true;
    try {
      const ids = selectedRows.value.map((s: any) => s.id);
      await OperationsService.processGraduation({
          student_ids: ids,
          graduation_year: parseInt(batchYear.value),
          status: batchStatus.value
      });
      toast.success.action(t('common.messages.saveSuccess'));
      rowSelection.value = {};
      fetchEligible();
    } catch (e) {
      toast.error.fromResponse(e);
    } finally {
      processing.value = false;
    }
  }
};

// Grade Management
const gradeModalOpen = ref(false);
const editingResult = ref<any>(null);
const savingResult = ref(false);
const tempGradeKeys = ref<Record<string, string>>({});

const openGradeModal = (result: any) => {
    editingResult.value = JSON.parse(JSON.stringify(result));
    if (!editingResult.value.grades) editingResult.value.grades = {};
    
    // Initialize temp keys for editing
    tempGradeKeys.value = {};
    Object.keys(editingResult.value.grades).forEach(key => {
        tempGradeKeys.value[key] = key;
    });
    
    gradeModalOpen.value = true;
};

const addGradeField = () => {
    const newKey = `${t('common.labels.subject')} ${Object.keys(editingResult.value.grades).length + 1}`;
    editingResult.value.grades[newKey] = 0;
    tempGradeKeys.value[newKey] = newKey;
};

const removeGradeField = (key: string) => {
    delete editingResult.value.grades[key];
    delete tempGradeKeys.value[key];
};

const updateGradeKey = (oldKey: string) => {
    const newKey = tempGradeKeys.value[oldKey];
    if (newKey && newKey !== oldKey) {
        const val = editingResult.value.grades[oldKey];
        delete editingResult.value.grades[oldKey];
        editingResult.value.grades[newKey] = val;
        
        // Update temp keys mapping
        delete tempGradeKeys.value[oldKey];
        tempGradeKeys.value[newKey] = newKey;
    }
};

const saveResult = async () => {
    savingResult.value = true;
    try {
        await OperationsService.updateGraduationResult(editingResult.value);
        toast.success.action(t('graduation.messages.gradeSaveSuccess'));
        gradeModalOpen.value = false;
        fetchHistory();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        savingResult.value = false;
    }
};

// Import Grades Logic
const importModalOpen = ref(false);
const importFile = ref<File | null>(null);
const importYear = ref(String(new Date().getFullYear()));
const importing = ref(false);
const importErrors = ref<string[]>([]);

const onImportFileSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        importFile.value = files[0] as File;
        importErrors.value = [];
    }
};

const handleImport = async () => {
    if (!importFile.value) return;
    importing.value = true;
    importErrors.value = [];
    try {
        const response = await OperationsService.importGrades(importFile.value, parseInt(importYear.value));
        const parsed = parseResponse(response) as any;
        const data = parsed.data;
        toast.success.action(t('graduation.messages.importSuccess', { count: data.imported }));
        if (data.errors && data.errors.length > 0) {
            importErrors.value = data.errors;
        } else {
            importModalOpen.value = false;
            importFile.value = null;
        }
        fetchHistory();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        importing.value = false;
    }
};

const downloadCertificate = (studentId: number) => {
    window.open(`/api/v1/public/graduation/certificate/${studentId}`, '_blank');
};

onMounted(() => {
    if (activeTab.value === 'eligible') fetchEligible();
    else fetchHistory();
});
</script>
