<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('modules.school.logistics.sarpras.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('modules.school.logistics.sarpras.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button variant="outline">
          <LucideIcon
            name="Download"
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.export') }}
        </Button>
        <Button @click="handleAdd">
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('common.actions.add') }}
        </Button>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="mb-6 border-b bg-transparent p-0 h-auto gap-6 flex-wrap">
        <TabsTrigger 
          value="land" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Map"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.sarpras.tabs.land') }}
        </TabsTrigger>
        <TabsTrigger 
          value="building" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Building2"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.sarpras.tabs.building') }}
        </TabsTrigger>
        <TabsTrigger 
          value="room" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="DoorOpen"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.sarpras.tabs.room') }}
        </TabsTrigger>
        <TabsTrigger 
          value="asset" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Package"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.sarpras.tabs.asset') }}
        </TabsTrigger>
        <TabsTrigger 
          value="tickets" 
          class="px-2 py-3 bg-transparent shadow-none data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-all"
        >
          <LucideIcon
            name="Tool"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.sarpras.tabs.maintenance') }}
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
                {{ $t('common.labels.total') }} records: {{ pagination?.total || 0 }}
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

    <!-- Dialogs -->
    <LandDialog
      v-model:open="dialogs.land"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <BuildingDialog
      v-model:open="dialogs.building"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <RoomDialog
      v-model:open="dialogs.room"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <AssetDialog
      v-model:open="dialogs.asset"
      :is-edit="!!selectedItem"
      :initial-data="selectedItem"
      :loading="saving"
      @submit="handleSave"
    />
    <MaintenanceTicketDialog
      v-model:open="dialogs.tickets"
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
import { LogisticsService } from '@/modules/School/services/LogisticsService';
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

// Components
import LandDialog from './components/LandDialog.vue';
import BuildingDialog from './components/BuildingDialog.vue';
import RoomDialog from './components/RoomDialog.vue';
import AssetDialog from './components/AssetDialog.vue';
import MaintenanceTicketDialog from './components/MaintenanceTicketDialog.vue';

const { t } = useI18n();
const toast = useToast();
const confirmModal = ref<any>(null);
const activeTab = ref('land');
const loading = ref(false);
const saving = ref(false);
const rows = ref<any[]>([]);
const selectedItem = ref<any>(null);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const dialogs = ref({
  land: false,
  building: false,
  room: false,
  asset: false,
  tickets: false,
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

const landColumns = [
  columnHelper.accessor('name', { header: t('modules.school.logistics.sarpras.labels.landName') }),
  columnHelper.accessor('area', { header: `${t('modules.school.logistics.sarpras.labels.area')} (m2)`, cell: info => `${info.getValue()} m²` }),
  columnHelper.accessor('certificate_number', { header: t('modules.school.logistics.sarpras.labels.certificateNumber'), cell: info => info.getValue() || '-' }),
  actionColumn
];

const buildingColumns = [
  columnHelper.accessor('name', { header: t('modules.school.logistics.sarpras.labels.buildingName') }),
  columnHelper.accessor('land_asset.name', { header: t('modules.school.logistics.sarpras.labels.location') }),
  columnHelper.accessor('condition', { header: t('modules.school.logistics.sarpras.labels.condition') }),
  actionColumn
];

const roomColumns = [
  columnHelper.accessor('name', { header: t('modules.school.logistics.sarpras.labels.roomName') }),
  columnHelper.accessor('building.name', { header: t('modules.school.logistics.sarpras.tabs.building') }),
  columnHelper.accessor('type', { header: t('modules.school.logistics.sarpras.labels.roomType') }),
  actionColumn
];

const assetColumns = [
  columnHelper.accessor('name', { header: t('modules.school.logistics.sarpras.labels.assetName') }),
  columnHelper.accessor('code', { header: t('modules.school.logistics.sarpras.labels.assetCode') }),
  columnHelper.accessor('room.name', { header: t('modules.school.logistics.sarpras.labels.location') }),
  columnHelper.accessor('condition', { header: t('modules.school.logistics.sarpras.labels.condition') }),
  actionColumn
];

const ticketColumns = [
    columnHelper.accessor('asset.name', { header: t('modules.school.logistics.sarpras.tabs.asset') }),
    columnHelper.accessor('reporter.full_name', { header: t('modules.school.logistics.sarpras.labels.reporter') }),
    columnHelper.accessor('priority', { header: t('modules.school.logistics.sarpras.labels.priority') }),
    columnHelper.accessor('status', { header: t('common.labels.status') }),
    columnHelper.accessor('date_reported', { 
        header: t('common.labels.date'), 
        cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
    }),
    actionColumn
];

const currentColumns = computed(() => {
  switch (activeTab.value) {
    case 'land': return landColumns;
    case 'building': return buildingColumns;
    case 'room': return roomColumns;
    case 'asset': return assetColumns;
    case 'tickets': return ticketColumns;
    default: return landColumns;
  }
});

const table = useVueTable({
  get data() { return rows.value },
  get columns() { return currentColumns.value },
  getCoreRowModel: getCoreRowModel(),
});

const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const response = await LogisticsService.getSarprasData(activeTab.value, { page });
    const { data, pagination: pagin } = parseResponse(response);
    rows.value = data;
    if (pagin && pagination.value) {
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

const handlePageChange = (page: number) => {
  fetchData(page);
};

watch(activeTab, () => {
  rows.value = [];
  fetchData("1");
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
    if (activeTab.value === 'land') dialogs.value.land = true;
    else if (activeTab.value === 'building') dialogs.value.building = true;
    else if (activeTab.value === 'room') dialogs.value.room = true;
    else if (activeTab.value === 'asset') dialogs.value.asset = true;
    else if (activeTab.value === 'tickets') dialogs.value.tickets = true;
}

const handleSave = async (formData: any) => {
    saving.value = true;
    try {
        const type = activeTab.value;
        if (selectedItem.value) {
            await LogisticsService.updateSarprasData(type, selectedItem.value.id, formData);
            toast.success.action(t('modules.school.academic.messages.updateSuccess'));
        } else {
            const data = { ...formData };
            if (type === 'tickets') {
                data.school_id = 1;
                data.workspace_id = 1;
            }
            await LogisticsService.storeSarprasData(type, data);
            toast.success.action(t('modules.school.academic.messages.addSuccess'));
        }
        
        Object.keys(dialogs.value).forEach(k => (dialogs.value as any)[k] = false);
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
        message: `${t('academic.messages.deleteConfirm', { name: item.name })}`,
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            await LogisticsService.deleteSarprasData(activeTab.value, item.id);
            toast.success.action(t('modules.school.academic.messages.deleteSuccess'));
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
