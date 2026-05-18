<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="Puzzle"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('modules.school.extensions.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('modules.school.extensions.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button 
          class="rounded-xl shadow-sm px-6"
          @click="handleAdd"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.add') || $t('common.labels.add') }}
        </Button>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="p-2 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 mb-6 flex-wrap">
        <TabsTrigger 
          value="library" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Library"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.library') }}
        </TabsTrigger>
        <TabsTrigger 
          value="library-circulations" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="ArrowRightLeft"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.circulations') }}
        </TabsTrigger>
        <TabsTrigger 
          value="uks" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Stethoscope"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.uks') }}
        </TabsTrigger>
        <TabsTrigger 
          value="guest" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="NotebookPen"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.guest') }}
        </TabsTrigger>
        <TabsTrigger 
          value="alumni" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="GraduationCap"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.alumni') }}
        </TabsTrigger>
        <TabsTrigger 
          value="tracer-studies" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40"
        >
          <LucideIcon
            name="Search"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.extensions.tabs.tracer') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <Card class="border border-border/40 bg-card shadow-none rounded-xl overflow-hidden">
          <CardContent class="p-0">
            <DataTable 
              :table="table"
              :loading="loading"
            />
          </CardContent>
        </Card>
      </div>
    </Tabs>

    <!-- Dialogs -->
    <LibraryBookDialog
      v-model:open="dialogs.library"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <LibraryCirculationDialog
      v-model:open="dialogs.libraryCirculations"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <UksVisitDialog
      v-model:open="dialogs.uks"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <GuestLogDialog
      v-model:open="dialogs.guest"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <AlumniDialog
      v-model:open="dialogs.alumni"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <TracerStudyDialog
      v-model:open="dialogs.tracerStudies"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />

    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import {
  Tabs, TabsList, TabsTrigger, Card, CardContent, Button, LucideIcon, DataTable, ConfirmModal
} from '@/shared/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';

// Components
import LibraryBookDialog from './components/LibraryBookDialog.vue';
import LibraryCirculationDialog from './components/LibraryCirculationDialog.vue';
import UksVisitDialog from './components/UksVisitDialog.vue';
import GuestLogDialog from './components/GuestLogDialog.vue';
import AlumniDialog from './components/AlumniDialog.vue';
import TracerStudyDialog from './components/TracerStudyDialog.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const activeTab = ref('library');
const loading = ref(false);
const saving = ref(false);
const rows = ref<any[]>([]);
const selectedItem = ref<any>(null);

const dialogs = ref({
  library: false,
  libraryCirculations: false,
  uks: false,
  guest: false,
  alumni: false,
  tracerStudies: false,
});

const columnHelper = createColumnHelper<any>();

const actionColumn = columnHelper.display({
  id: 'actions',
  header: t('common.labels.action'),
  cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-muted-foreground hover:bg-primary/10 hover:text-primary rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-lg',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleDelete(row.original);
      }
    }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
  ]),
});

const libraryColumns = computed(() => [
  columnHelper.accessor('title', { header: t('modules.school.extensions.labels.bookTitle') }),
  columnHelper.accessor('author', { header: t('modules.school.extensions.labels.author') }),
  columnHelper.accessor('quantity', { header: t('modules.school.extensions.labels.stock') }),
  actionColumn
]);

const circulationColumns = computed(() => [
  columnHelper.accessor('book.title', { header: t('modules.school.extensions.tabs.library') }),
  columnHelper.accessor('borrower.full_name', { header: t('modules.school.extensions.labels.borrower') }),
  columnHelper.accessor('status', { header: t('common.labels.status') }),
  columnHelper.accessor('due_date', { 
    header: t('modules.school.extensions.labels.dueDate'),
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  actionColumn
]);

const uksColumns = computed(() => [
  columnHelper.accessor('created_at', { 
    header: t('common.labels.date'),
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  columnHelper.accessor('patient.full_name', { header: t('modules.school.extensions.labels.patientName') }),
  columnHelper.accessor('complaint', { header: t('modules.school.extensions.labels.complaint') }),
  actionColumn
]);

const guestColumns = computed(() => [
  columnHelper.accessor('name', { header: t('common.labels.name') }),
  columnHelper.accessor('purpose', { header: t('common.labels.description') }),
  columnHelper.accessor('visit_time', { 
    header: t('common.labels.date'),
    cell: info => info.getValue() ? new Date(info.getValue()).toLocaleString(t('common.language') === 'id' ? 'id-ID' : 'en-US') : '-'
  }),
  actionColumn
]);

const alumniColumns = computed(() => [
  columnHelper.accessor('student.full_name', { header: t('common.labels.name') }),
  columnHelper.accessor('graduation_year', { header: t('modules.school.extensions.labels.graduationYear') }),
  columnHelper.accessor('current_activity', { header: t('modules.school.extensions.labels.currentActivity'), cell: info => info.getValue() || '-' }),
  actionColumn
]);

const tracerColumns = computed(() => [
  columnHelper.accessor('alumni.student.full_name', { header: t('common.labels.name') }),
  columnHelper.accessor('employment_status', { header: t('common.labels.status') }),
  columnHelper.accessor('company_name', { header: t('modules.school.extensions.labels.companyName'), cell: info => info.getValue() || info.row.original.university_name || '-' }),
  columnHelper.accessor('is_relevant_to_major', { 
      header: t('modules.school.extensions.labels.relevantToMajor'), 
      cell: info => info.getValue() ? t('common.labels.yes') : t('common.labels.no') 
  }),
  actionColumn
]);

const currentColumns = computed(() => {
  switch (activeTab.value) {
    case 'library': return libraryColumns.value;
    case 'library-circulations': return circulationColumns.value;
    case 'uks': return uksColumns.value;
    case 'guest': return guestColumns.value;
    case 'alumni': return alumniColumns.value;
    case 'tracer-studies': return tracerColumns.value;
    default: return libraryColumns.value;
  }
});

const table = useVueTable({
  get data() { return rows.value },
  get columns() { return currentColumns.value },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async () => {
  loading.value = true;
  try {
    const endpointMap: any = {
        library: 'library',
        'library-circulations': 'library-circulations',
        uks: 'uks',
        guest: 'guest',
        alumni: 'alumni',
        'tracer-studies': 'tracer-studies'
    };
    const response = await api.get(`/manage/school/extensions/${endpointMap[activeTab.value]}`);
    const { data } = parseResponse(response);
    rows.value = data;
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

watch(activeTab, () => {
  rows.value = [];
  fetchData();
});

const handleAdd = () => {
    selectedItem.value = null;
    openDialog();
}

const handleEdit = (item: any) => {
    selectedItem.value = item;
    openDialog();
}

const openDialog = () => {
    if (activeTab.value === 'library') dialogs.value.library = true;
    else if (activeTab.value === 'library-circulations') dialogs.value.libraryCirculations = true;
    else if (activeTab.value === 'uks') dialogs.value.uks = true;
    else if (activeTab.value === 'guest') dialogs.value.guest = true;
    else if (activeTab.value === 'alumni') dialogs.value.alumni = true;
    else if (activeTab.value === 'tracer-studies') dialogs.value.tracerStudies = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        const type = activeTab.value === 'library-circulations' ? 'circulations' : 
                     activeTab.value === 'tracer-studies' ? 'tracer' : activeTab.value;
        if (selectedItem.value) {
            await api.put(`/manage/school/extensions/${type}/${selectedItem.value.id}`, formData);
            toast.success.action(t('modules.school.extensions.messages.saveSuccess'));
        } else {
            await api.post(`/manage/school/extensions/${type}`, formData);
            toast.success.action(t('modules.school.extensions.messages.saveSuccess'));
        }
        
        Object.keys(dialogs.value).forEach(k => (dialogs.value as any)[k] = false);
        fetchData();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDelete = async (item: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: t('modules.school.extensions.messages.deleteTitle'),
        message: t('modules.school.extensions.messages.deleteConfirm'),
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            const type = activeTab.value === 'library-circulations' ? 'circulations' : 
                         activeTab.value === 'tracer-studies' ? 'tracer' : activeTab.value;
            await api.delete(`/manage/school/extensions/${type}/${item.id}`);
            toast.success.action(t('modules.school.extensions.messages.deleteSuccess'));
            fetchData();
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
}

onMounted(() => {
  fetchData();
});
</script>
