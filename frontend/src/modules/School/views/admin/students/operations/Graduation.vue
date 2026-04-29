<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-foreground">
          {{ $t('features.school.ops.graduation.title') }}
        </h2>
        <p class="text-sm text-muted-foreground italic">
          {{ $t('features.school.ops.graduation.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <router-link :to="{ name: 'settings.document-templates', query: { type: 'skl' } }">
          <Button
            variant="outline"
            class="rounded-xl shadow-sm"
          >
            <LucideIcon
              name="FileText"
              class="w-4 h-4 mr-2"
            />
            {{ $t('features.school.ops.graduation.labels.manageTemplates') }}
          </Button>
        </router-link>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="grid w-full grid-cols-3 lg:w-[600px]">
        <TabsTrigger value="eligible">
          {{ $t('features.school.ops.graduation.labels.tabEligible') }}
        </TabsTrigger>
        <TabsTrigger value="history">
          {{ $t('features.school.ops.graduation.labels.tabHistory') }}
        </TabsTrigger>
        <TabsTrigger value="settings">
          Pengaturan
        </TabsTrigger>
      </TabsList>

      <TabsContent
        value="eligible"
        class="mt-6 space-y-4"
      >
        <Card class="border border-border/40 shadow-none rounded-xl">
          <CardContent class="p-4 space-y-4">
            <div class="flex flex-wrap gap-4 items-end">
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tahun Lulus</label>
                <Select v-model="batchYear">
                  <SelectTrigger class="w-[120px] rounded-xl">
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
                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Status Target</label>
                <Select v-model="batchStatus">
                  <SelectTrigger class="w-[180px] rounded-xl">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="graduated">Lulus</SelectItem>
                    <SelectItem value="not_graduated">Tidak Lulus</SelectItem>
                    <SelectItem value="deferred">Ditangguhkan</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <Button
                v-if="selectedRows.length > 0"
                variant="default"
                class="rounded-xl shadow-sm px-6 bg-success hover:bg-success/90"
                :loading="processing"
                @click="handleProcess"
              >
                <LucideIcon
                  name="CheckCircle"
                  class="w-4 h-4 mr-2"
                />
                Proses ({{ selectedRows.length }})
              </Button>
            </div>

            <div class="p-3 bg-muted/30 rounded-lg text-[10px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-2">
              <LucideIcon
                name="Info"
                class="w-3 h-3"
              />
              {{ $t('features.school.ops.graduation.labels.eligibleHint') }}
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
        <Card class="border border-border/40 shadow-none rounded-xl overflow-hidden">
          <CardContent class="p-0">
            <div class="p-4 flex justify-between items-center bg-muted/20 border-b border-border/40">
              <div class="flex items-center gap-2">
                <Select v-model="filterYear">
                  <SelectTrigger class="w-[120px] rounded-xl h-8">
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
              <Button
                variant="outline"
                size="sm"
                class="rounded-lg"
                @click="importModalOpen = true"
              >
                <LucideIcon
                  name="Upload"
                  class="w-3.5 h-3.5 mr-2"
                />
                Import Nilai
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="rounded-lg"
                @click="fetchHistory"
              >
                <LucideIcon
                  name="RefreshCcw"
                  class="w-3.5 h-3.5 mr-2"
                />
                Refresh
              </Button>
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
      <DialogContent class="sm:max-w-[600px] rounded-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <DialogHeader>
          <DialogTitle>Input Nilai & Sertifikat</DialogTitle>
          <DialogDescription v-if="editingResult">
            Siswa: {{ editingResult.student?.full_name }} ({{ editingResult.student?.nisn }})
          </DialogDescription>
        </DialogHeader>
        
        <div 
          v-if="editingResult" 
          class="space-y-4 py-4 flex-1 overflow-y-auto"
        >
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase">Status</label>
              <Select v-model="editingResult.status">
                <SelectTrigger class="rounded-xl">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="graduated">Lulus</SelectItem>
                  <SelectItem value="not_graduated">Tidak Lulus</SelectItem>
                  <SelectItem value="deferred">Ditangguhkan</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase">No. Sertifikat/SKL</label>
              <Input
                v-model="editingResult.certificate_number"
                placeholder="Contoh: SKL/2024/001"
                class="rounded-xl"
              />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <label class="text-xs font-bold uppercase">Rincian Nilai</label>
              <Button 
                variant="link" 
                size="sm" 
                class="h-auto p-0 text-xs"
                @click="addGradeField"
              >
                + Tambah Mapel
              </Button>
            </div>
            <div class="max-h-[250px] overflow-y-auto space-y-2 pr-1">
              <div 
                v-for="(_, key) in editingResult.grades" 
                :key="key" 
                class="flex gap-2"
              >
                <Input 
                  v-model="tempGradeKeys[key]" 
                  placeholder="Mapel" 
                  class="flex-1 rounded-lg h-8 text-sm"
                  @blur="updateGradeKey(key as string)"
                />
                <Input 
                  v-model="editingResult.grades[key]" 
                  placeholder="Nilai" 
                  type="number" 
                  class="w-20 rounded-lg h-8 text-sm text-center"
                />
                <Button 
                  variant="ghost" 
                  size="icon" 
                  class="h-8 w-8 text-destructive"
                  @click="removeGradeField(key as string)"
                >
                  <LucideIcon name="Trash2" class="w-3.5 h-3.5" />
                </Button>
              </div>
              <div v-if="Object.keys(editingResult.grades || {}).length === 0" class="text-center py-4 text-muted-foreground text-xs italic">
                Belum ada data nilai.
              </div>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button 
            variant="outline" 
            class="rounded-xl" 
            @click="gradeModalOpen = false"
          >
            Batal
          </Button>
          <Button 
            variant="default" 
            class="rounded-xl px-8" 
            :loading="savingResult"
            @click="saveResult"
          >
            Simpan Perubahan
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Import Grades Modal -->
    <Dialog v-model:open="importModalOpen">
      <DialogContent class="sm:max-w-[500px] rounded-2xl">
        <DialogHeader>
          <DialogTitle>Import Nilai dari File</DialogTitle>
          <DialogDescription>
            Upload file CSV atau Excel (.xlsx) dengan format: kolom pertama NISN, kolom selanjutnya nama mata pelajaran dengan nilainya.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <div class="p-4 border-2 border-dashed border-border rounded-xl text-center space-y-2 bg-muted/20">
            <LucideIcon name="Upload" class="w-8 h-8 mx-auto text-muted-foreground" />
            <input
              ref="importFileInput"
              type="file"
              accept=".csv,.xlsx,.xls"
              class="hidden"
              @change="onImportFileSelected"
            />
            <Button variant="outline" class="rounded-xl" @click="($refs.importFileInput as HTMLInputElement)?.click()">
              Pilih File CSV/Excel
            </Button>
            <p v-if="importFile" class="text-sm font-medium text-primary">{{ importFile.name }}</p>
            <p class="text-[10px] text-muted-foreground">Format: CSV (.csv) atau Excel (.xlsx)</p>
          </div>

          <div class="space-y-2">
            <label class="text-xs font-bold uppercase">Tahun Kelulusan</label>
            <Select v-model="importYear">
              <SelectTrigger class="rounded-xl">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="year in years" :key="year" :value="String(year)">{{ year }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="p-3 bg-primary/5 rounded-xl border border-primary/10">
            <p class="text-[10px] font-bold uppercase tracking-wider text-primary mb-1">Format File</p>
            <div class="font-mono text-[10px] text-muted-foreground space-y-0.5">
              <p>NISN, Matematika, B. Indonesia, B. Inggris, ...</p>
              <p>0012345678, 85, 90, 88, ...</p>
              <p>0012345679, 92, 78, 95, ...</p>
            </div>
          </div>

          <div v-if="importErrors.length > 0" class="p-3 bg-destructive/5 rounded-xl border border-destructive/10 max-h-[120px] overflow-y-auto">
            <p class="text-[10px] font-bold uppercase text-destructive mb-1">Errors:</p>
            <p v-for="(err, i) in importErrors" :key="i" class="text-[10px] text-destructive">{{ err }}</p>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" class="rounded-xl" @click="importModalOpen = false">Batal</Button>
          <Button 
            variant="default" 
            class="rounded-xl px-8" 
            :loading="importing"
            :disabled="!importFile"
            @click="handleImport"
          >
            <LucideIcon name="Upload" class="w-4 h-4 mr-2" />
            Import Nilai
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
    cell: info => h('span', { class: 'font-mono text-xs' }, info.getValue() || '-')
  }),
  columnHelper.accessor('full_name', { 
    header: t('features.school.operations.affairs.labels.studentName'),
    cell: info => h('span', { class: 'font-bold' }, info.getValue())
  }),
  columnHelper.accessor('level.name', { 
    header: t('common.labels.single_level'),
    cell: info => h('span', { class: 'text-xs opacity-70' }, info.getValue())
  }),
  columnHelper.accessor('status', { 
    header: t('common.labels.status'),
    cell: info => h('span', { class: 'px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest' }, info.getValue())
  }),
];

const historyColumns = [
  columnHelper.accessor('student.full_name', { 
    header: 'Nama Siswa',
    cell: info => h('div', [
        h('div', { class: 'font-bold' }, info.getValue()),
        h('div', { class: 'text-[10px] opacity-60 font-mono' }, info.row.original.student?.nisn)
    ])
  }),
  columnHelper.accessor('status', { 
    header: 'Status',
    cell: info => {
        const val = info.getValue() as string;
        const colors: any = { graduated: 'bg-success/10 text-success', not_graduated: 'bg-destructive/10 text-destructive', deferred: 'bg-warning/10 text-warning' };
        return h('span', { class: `px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest ${colors[val] || 'bg-muted'}` }, val);
    }
  }),
  columnHelper.accessor('certificate_number', { 
    header: 'No. Sertifikat',
    cell: info => h('span', { class: 'text-xs italic opacity-60' }, info.getValue() || '-')
  }),
  columnHelper.display({
    id: 'actions',
    header: 'Aksi',
    cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
        h(Button, {
            variant: 'ghost',
            size: 'sm',
            class: 'h-8 px-2 text-primary',
            onClick: () => openGradeModal(row.original)
        }, () => [h(LucideIcon, { name: 'Edit', class: 'w-3.5 h-3.5 mr-1' }), 'Nilai']),
        h(Button, {
            variant: 'ghost',
            size: 'sm',
            class: 'h-8 px-2',
            disabled: row.original.status !== 'graduated',
            onClick: () => downloadCertificate(row.original.student_id as number)
        }, () => h(LucideIcon, { name: 'Download', class: 'w-3.5 h-3.5' }))
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
    title: 'Konfirmasi Proses Kelulusan',
    message: `Apakah Anda yakin ingin memproses ${count} siswa dengan status "${batchStatus.value}" untuk tahun ${batchYear.value}?`,
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
    const newKey = `Mapel ${Object.keys(editingResult.value.grades).length + 1}`;
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
        toast.success.action('Data nilai berhasil disimpan');
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
        toast.success.action(`Berhasil import nilai untuk ${data.imported} siswa`);
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
