<template>
  <div class="space-y-8 p-4 lg:p-8 animate-in fade-in duration-700">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="Users"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground">
            {{ $t('features.school.students.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium flex items-center gap-2">
          {{ $t('features.school.students.subtitle') }}
        </p>
      </div>

      <div class="flex flex-wrap gap-3">
        <Button
          variant="outline"
          class="rounded-xl border-border/40"
          :disabled="loading || students.length === 0"
          @click="handleExport"
        >
          <LucideIcon
            name="Download"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.students.actions.export') }}
        </Button>
        
        <router-link :to="{ name: 'students.create' }">
          <Button class="rounded-xl shadow-sm px-6">
            <LucideIcon
              name="UserPlus"
              class="w-4 h-4 mr-2"
            />
            {{ $t('features.school.students.actions.add') }}
          </Button>
        </router-link>
      </div>
    </div>

    <!-- Quick Stats Container -->
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="(stat, index) in summaryStats" 
        :key="index"
        class="group bg-card border border-border/40 rounded-xl p-6 shadow-none hover:bg-muted/30 transition-all duration-300"
      >
        <div class="flex justify-between items-start mb-4">
          <div :class="`w-10 h-10 rounded-xl flex items-center justify-center border border-border/40 bg-muted/50 ${stat.iconColor}`">
            <LucideIcon
              :name="stat.icon"
              class="w-5 h-5"
            />
          </div>
          <span class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">{{ stat.label }}</span>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-2xl font-black tracking-tight text-foreground">
            {{ stat.value }}
          </h3>
          <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ $t('common.labels.student') }}</span>
        </div>
      </div>
    </div>

    <!-- Main Table Section -->
    <Card class="border-border/40 bg-card shadow-none rounded-xl overflow-hidden">
      <!-- Table Header (Search/Filter) -->
      <div class="p-6 border-b border-border/40 flex flex-col sm:flex-row justify-between items-center gap-4 bg-muted/10">
        <div class="relative w-full sm:w-96">
          <LucideIcon
            name="Search"
            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
          />
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari NISN, Nama atau NIS..."
            class="w-full bg-background border border-border/40 rounded-xl py-2 pl-10 pr-4 text-sm text-foreground focus:outline-none focus:ring-1 focus:ring-primary/20 transition-all placeholder:text-muted-foreground/60" 
            @input="handleSearch"
          >
        </div>
        
        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">
          <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse" />
          Real-time Data Active
        </div>
      </div>

      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
        />
        
        <!-- Custom Pagination -->
        <div class="p-6 border-t border-border/40 flex flex-col md:flex-row justify-between items-center gap-4 bg-muted/5">
          <p class="text-xs font-bold text-muted-foreground/60 uppercase tracking-widest order-2 md:order-1">
            {{ $t('features.school.students.labels.totalStudents', { total: pagination?.total || 0 }) }}
          </p>
          
          <div class="order-1 md:order-2">
            <Pagination
              v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
              :total-items="pagination.total || 0"
              :per-page="pagination.per_page || 10"
              :current-page="pagination.current_page || 1"
              @page-change="handlePageChange"
            />
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, h, toRefs, computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  CardContent, Button, LucideIcon, DataTable, Pagination, Avatar, AvatarFallback
} from '@/components/ui';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { 
  useVueTable, 
  getCoreRowModel, 
  createColumnHelper,
} from '@tanstack/vue-table';
import { useRouter } from 'vue-router';
import { exportToCSV } from '@/utils/exportUtils';
import { useStudentStore } from '@/modules/School/stores/student';
import api from '@/services/api';
import debounce from 'lodash/debounce';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();
const router = useRouter();
const studentStore = useStudentStore();
const { students, loading, pagination } = toRefs(studentStore);

const searchQuery = ref('');

// Computed Stats
const summaryStats = computed(() => {
  const list = students.value || [];
  return [
    { 
      label: 'TOTAL SISWA', 
      value: pagination.value?.total || 0, 
      icon: 'UserCircle', 
      iconColor: 'text-primary' 
    },
    { 
      label: 'SISWA AKTIF', 
      value: list.filter(s => s.status === 'active').length, 
      icon: 'ShieldCheck', 
      iconColor: 'text-success' 
    },
    { 
      label: 'LAKI-LAKI', 
      value: list.filter(s => s.gender === 'L').length, 
      icon: 'Users', 
      iconColor: 'text-info' 
    },
    { 
      label: 'PEREMPUAN', 
      value: list.filter(s => s.gender === 'P').length, 
      icon: 'User', 
      iconColor: 'text-rose-500' 
    },
  ];
});

const handleSearch = debounce(() => {
  studentStore.fetchStudents({ search: searchQuery.value });
}, 500);

const handleExport = () => {
  exportToCSV(students.value, `data-siswa-${new Date().toISOString().split('T')[0]}`);
  toast.success.action(t('common.messages.exportSuccess'));
};

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.accessor('full_name', {
    header: t('features.school.admission.labels.full_name'),
    cell: info => h('div', { class: 'flex items-center gap-3' }, [
      h(Avatar, { class: 'h-9 w-9 rounded-xl border border-border/40 bg-muted transition-colors' }, [
        h(AvatarFallback, { class: 'bg-primary/10 text-primary font-black text-[10px] uppercase tracking-widest' }, () => info.row.original.full_name?.substring(0, 2))
      ]),
      h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'font-bold text-foreground text-sm' }, info.getValue()),
        h('span', { class: 'text-[9px] text-muted-foreground font-black uppercase tracking-[0.2em]' }, info.row.original.nis || '-')
      ])
    ]),
  }),
  columnHelper.accessor('nisn', {
    header: 'NISN',
    cell: info => h('span', { class: 'text-xs font-bold text-muted-foreground uppercase' }, info.getValue() || '-'),
  }),
  columnHelper.accessor('gender', {
    header: t('features.school.students.labels.genderShort'),
    cell: info => {
      const val = info.getValue();
      const isMale = val === 'L';
      return h('span', { 
        class: `px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] ${
          isMale ? 'bg-info/10 text-info border border-info/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20'
        }` 
      }, val);
    },
  }),
  columnHelper.accessor('department.name', {
    header: t('features.school.students.labels.level'),
    cell: info => h('div', { class: 'flex flex-col' }, [
      h('span', { class: 'text-xs font-bold text-foreground' }, info.row.original.department?.code || '-'),
      h('span', { class: 'text-[9px] font-black text-muted-foreground uppercase tracking-widest' }, info.row.original.level?.name || '-')
    ]),
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: info => h('span', { 
      class: 'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] bg-success/10 text-success border border-success/20' 
    }, info.getValue()),
  }),
  columnHelper.display({
    id: 'actions',
    header: '',
    cell: ({ row }) => h('div', { class: 'flex justify-end gap-1 px-2' }, [
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-muted-foreground hover:bg-primary/10 hover:text-primary rounded-lg',
        title: t('features.school.students.actions.printID'),
        onClick: () => window.open(`${api.defaults.baseURL}/admin/reports/students/${row.original.id}/id-card`, '_blank')
      }, () => h(LucideIcon, { name: 'IdCard', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-muted-foreground hover:bg-warning/10 hover:text-warning rounded-lg',
        title: t('common.actions.view'),
        onClick: () => router.push({ name: 'students.show', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Eye', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-muted-foreground hover:bg-success/10 hover:text-success rounded-lg',
        title: t('common.actions.edit'),
        onClick: () => router.push({ name: 'students.edit', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Edit2', class: 'w-4 h-4' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded-lg',
        title: t('common.actions.delete'),
        onClick: () => handleDelete(row.original.id)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-4 h-4' }))
    ]),
  }),
];

const table = useVueTable({
  get data() { return students.value || [] },
  columns,
  getCoreRowModel: getCoreRowModel(),
});

const handlePageChange = (page: number) => {
  studentStore.fetchStudents({ page, search: searchQuery.value });
};

const handleDelete = async (id: number) => {
  const isConfirmed = await confirm({
    title: t('common.actions.delete'),
    message: t('features.school.academic.messages.deleteConfirm'),
    variant: 'destructive',
  });

  if (isConfirmed) {
    try {
      await studentStore.deleteStudent(id);
      toast.success.action(t('features.school.academic.messages.deleteSuccess'));
    } catch (e: unknown) {
      toast.error.fromResponse(e);
    }
  }
};

onMounted(() => {
  studentStore.fetchStudents();
});
</script>

<style scoped lang="postcss">
@reference "../../../../../../css/app.css";

/* Clean style scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  @apply bg-transparent;
}
::-webkit-scrollbar-thumb {
  @apply bg-muted rounded-full;
}
::-webkit-scrollbar-thumb:hover {
  @apply bg-muted-foreground/30;
}
</style>
