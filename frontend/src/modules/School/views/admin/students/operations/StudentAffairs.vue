<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('modules.school.operations.affairs.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('modules.school.operations.affairs.subtitle') }}
        </p>
      </div>
      <Button @click="handleAdd">
        <LucideIcon
          name="Plus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('modules.school.operations.affairs.btnAdd') }}
      </Button>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="mb-6 border-b bg-transparent p-0 h-auto gap-6 flex-wrap">
        <TabsTrigger 
          value="violation" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="AlertTriangle"
            class="w-4 h-4 mr-2 text-destructive"
          />
          {{ $t('modules.school.operations.affairs.tabs.violation') }}
        </TabsTrigger>
        <TabsTrigger 
          value="achievement" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Trophy"
            class="w-4 h-4 mr-2 text-yellow-500"
          />
          {{ $t('modules.school.operations.affairs.tabs.achievement') }}
        </TabsTrigger>
        <TabsTrigger 
          value="counseling" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="MessagesSquare"
            class="w-4 h-4 mr-2 text-primary"
          />
          {{ $t('modules.school.operations.affairs.tabs.counseling') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <Card>
          <CardContent class="p-0">
            <DataTable 
              :table="table"
              :loading="loading"
            />
            <div class="p-4 border-t flex justify-between items-center">
              <p class="text-sm text-muted-foreground">
                Total records: {{ pagination?.total || 0 }}
              </p>
              <Pagination
                v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
                :total-items="pagination.total || 0"
                :per-page="pagination.per_page || 10"
                :current-page="pagination.current_page || 1"
                @page-change="handlePageChange"
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </Tabs>

    <StudentAffairDialog 
      v-model:open="dialogOpen" 
      :type="activeTab" 
      :is-edit="!!selectedItem" 
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />

    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { OperationsService } from '@/modules/School/services/OperationsService';
import {
  Tabs, TabsList, TabsTrigger, Card, CardContent, Button, LucideIcon, DataTable, Pagination, ConfirmModal
} from '@/shared/components/ui';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';
import StudentAffairDialog from './components/StudentAffairDialog.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const activeTab = ref<'violation' | 'achievement' | 'counseling'>('violation');
const loading = ref(false);
const saving = ref(false);
const dialogOpen = ref(false);
const selectedItem = ref<any>(null);
const rows = ref<any[]>([]);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const columnHelper = createColumnHelper<any>();

const actionColumn = columnHelper.display({
  id: 'actions',
  header: t('common.labels.status'),
  cell: ({ row }) => h('div', { class: 'flex gap-2' }, [
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-primary',
      onClick: (e: any) => {
          e.stopPropagation();
          handleEdit(row.original);
      }
    }, () => h(LucideIcon, { name: 'Pencil', class: 'w-4 h-4' })),
    h(Button, {
      variant: 'ghost',
      size: 'icon',
      class: 'h-8 w-8 text-destructive',
      onClick: (e: any) => {
          e.stopPropagation();
          handleDelete(row.original);
      }
    }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
  ]),
});

const violationColumns = [
  columnHelper.accessor('student.full_name', { header: t('modules.school.operations.affairs.labels.studentName') }),
  columnHelper.accessor('description', { header: t('modules.school.operations.affairs.labels.description.violation') }),
  columnHelper.accessor('points', { header: t('modules.school.operations.affairs.labels.points') }),
  columnHelper.accessor('date', { header: t('modules.school.operations.affairs.labels.date'), cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') }),
  actionColumn
];

const achievementColumns = [
  columnHelper.accessor('student.full_name', { header: t('modules.school.operations.affairs.labels.studentName') }),
  columnHelper.accessor('title', { header: t('modules.school.operations.affairs.labels.description.achievement') }),
  columnHelper.accessor('level', { header: t('modules.school.operations.affairs.labels.level') }),
  columnHelper.accessor('date', { header: t('modules.school.operations.affairs.labels.date'), cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') }),
  actionColumn
];

const counselingColumns = [
  columnHelper.accessor('student.full_name', { header: t('modules.school.operations.affairs.labels.studentName') }),
  columnHelper.accessor('type', { header: t('modules.school.operations.affairs.labels.type') }),
  columnHelper.accessor('status', { 
    header: t('modules.school.operations.affairs.labels.status'),
    cell: info => h('span', { 
        class: `px-2 py-1 rounded-full text-xs font-medium ${
            info.getValue() === 'Resolved' ? 'bg-success/10 text-success' : 
            info.getValue() === 'Monitoring' ? 'bg-amber-100 text-amber-700' : 'bg-muted text-muted-foreground'
        }` 
    }, info.getValue())
  }),
  columnHelper.accessor('date', { header: t('modules.school.operations.affairs.labels.date'), cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') }),
  actionColumn
];

const table = useVueTable({
  get data() { return rows.value },
  get columns() { 
      if (activeTab.value === 'violation') return violationColumns;
      if (activeTab.value === 'achievement') return achievementColumns;
      return counselingColumns;
  },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const endpointMap: any = {
        violation: 'violations',
        achievement: 'achievements',
        counseling: 'counseling'
    };
    const endpoint = (endpointMap as any)[activeTab.value];
    const response = await OperationsService.getStudentAffairs(endpoint, { page });
    const res = parseResponse(response);
    rows.value = res.data;
    if (res.pagination && pagination.value) {
      pagination.value = {
        total: res.pagination.total || 0,
        per_page: res.pagination.per_page || 20,
        current_page: res.pagination.current_page || 1,
      };
    }
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page: number) => {
  fetchData(page);
};

watch(activeTab, () => {
  rows.value = [];
  fetchData("1");
});

const handleAdd = () => {
    selectedItem.value = null;
    dialogOpen.value = true;
}

const handleEdit = (item: any) => {
    selectedItem.value = item;
    dialogOpen.value = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        const endpointMap: any = {
            violation: 'violations',
            achievement: 'achievements',
            counseling: 'counseling'
        };
        const endpoint = (endpointMap as any)[activeTab.value];
        if (selectedItem.value) {
            await OperationsService.updateStudentAffair(endpoint, selectedItem.value.id, formData);
            toast.success.action(t('modules.school.operations.affairs.messages.updateSuccess'));
        } else {
            await OperationsService.storeStudentAffair(endpoint, formData);
            toast.success.action(t('modules.school.operations.affairs.messages.saveSuccess'));
        }
        dialogOpen.value = false;
        fetchData(pagination.value.current_page);
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
}

const handleDelete = async (item: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: t('modules.school.operations.affairs.messages.deleteConfirmTitle'),
        message: t('modules.school.operations.affairs.messages.deleteConfirmMessage'),
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            const endpointMap: any = {
                violation: 'violations',
                achievement: 'achievements',
                counseling: 'counseling'
            };
            const endpoint = (endpointMap as any)[activeTab.value];
            await OperationsService.deleteStudentAffair(endpoint, item.id);
            toast.success.action(t('modules.school.operations.affairs.messages.deleteSuccess'));
            fetchData(pagination.value.current_page);
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
}

onMounted(() => {
  fetchData();
});
</script>
