<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.extensions.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.extensions.subtitle') }}
        </p>
      </div>
      <Button @click="handleAdd">
        <LucideIcon
          name="Plus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('common.actions.add') || $t('common.labels.add') }}
      </Button>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="mb-6 border-b bg-transparent p-0 h-auto gap-6 flex-wrap">
        <TabsTrigger 
          value="library" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Library"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.library') }}
        </TabsTrigger>
        <TabsTrigger 
          value="library-circulations" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="ArrowRightLeft"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.circulations') }}
        </TabsTrigger>
        <TabsTrigger 
          value="uks" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Stethoscope"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.uks') }}
        </TabsTrigger>
        <TabsTrigger 
          value="guest" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="NotebookPen"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.guest') }}
        </TabsTrigger>
        <TabsTrigger 
          value="alumni" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="GraduationCap"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.alumni') }}
        </TabsTrigger>
        <TabsTrigger 
          value="tracer-studies" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Search"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.extensions.tabs.tracer') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <Card>
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
import api from '@/services/api';
import {
  Tabs, TabsList, TabsTrigger, Card, CardContent, Button, LucideIcon, DataTable, ConfirmModal
} from '@/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';

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
      class: 'h-8 w-8 text-primary',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-destructive',
      onClick: (e: MouseEvent) => {
          e.stopPropagation();
          handleDelete(row.original);
      }
    }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
  ]),
});

const libraryColumns = computed(() => [
  columnHelper.accessor('title', { header: t('features.school.extensions.labels.bookTitle') }),
  columnHelper.accessor('author', { header: t('features.school.extensions.labels.author') }),
  columnHelper.accessor('quantity', { header: t('features.school.extensions.labels.stock') }),
  actionColumn
]);

const circulationColumns = computed(() => [
  columnHelper.accessor('book.title', { header: t('features.school.extensions.tabs.library') }),
  columnHelper.accessor('borrower.full_name', { header: t('features.school.extensions.labels.borrower') }),
  columnHelper.accessor('status', { header: t('common.labels.status') }),
  columnHelper.accessor('due_date', { 
    header: t('features.school.extensions.labels.dueDate'),
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  actionColumn
]);

const uksColumns = computed(() => [
  columnHelper.accessor('created_at', { 
    header: t('common.labels.date'),
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US')
  }),
  columnHelper.accessor('patient.full_name', { header: t('features.school.extensions.labels.patientName') }),
  columnHelper.accessor('complaint', { header: t('features.school.extensions.labels.complaint') }),
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
  columnHelper.accessor('graduation_year', { header: t('features.school.extensions.labels.graduationYear') }),
  columnHelper.accessor('current_activity', { header: t('features.school.extensions.labels.currentActivity'), cell: info => info.getValue() || '-' }),
  actionColumn
]);

const tracerColumns = computed(() => [
  columnHelper.accessor('alumni.student.full_name', { header: t('common.labels.name') }),
  columnHelper.accessor('employment_status', { header: t('common.labels.status') }),
  columnHelper.accessor('company_name', { header: t('features.school.extensions.labels.companyName'), cell: info => info.getValue() || info.row.original.university_name || '-' }),
  columnHelper.accessor('is_relevant_to_major', { 
      header: t('features.school.extensions.labels.relevantToMajor'), 
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
    const response = await api.get(`/admin/extensions/${endpointMap[activeTab.value]}`);
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
            await api.put(`/admin/extensions/${type}/${selectedItem.value.id}`, formData);
            toast.success.action(t('features.school.extensions.messages.saveSuccess'));
        } else {
            await api.post(`/admin/extensions/${type}`, formData);
            toast.success.action(t('features.school.extensions.messages.saveSuccess'));
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
        title: t('features.school.extensions.messages.deleteTitle'),
        message: t('features.school.extensions.messages.deleteConfirm'),
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            const type = activeTab.value === 'library-circulations' ? 'circulations' : 
                         activeTab.value === 'tracer-studies' ? 'tracer' : activeTab.value;
            await api.delete(`/admin/extensions/${type}/${item.id}`);
            toast.success.action(t('features.school.extensions.messages.deleteSuccess'));
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
