<template>
  <div class="student-dashboard min-h-screen bg-[#050b14] text-white p-4 lg:p-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 relative">
      <div class="z-10 animate-fade-in-down">
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center border border-cyan-500/30">
            <LucideIcon
              name="Users"
              class="w-6 h-6 text-cyan-400"
            />
          </div>
          <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-400 bg-clip-text text-transparent">
            {{ $t('features.school.students.title') }}
          </h1>
        </div>
        <p class="text-slate-400 text-sm max-w-lg leading-relaxed flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse" />
          {{ $t('features.school.students.subtitle') }}
        </p>
      </div>

      <div class="flex flex-wrap gap-3 z-10">
        <Button
          variant="outline"
          class="bg-slate-900/50 border-slate-700 text-slate-300 hover:bg-slate-800 hover:border-cyan-500/50 transition-all duration-300"
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
          <Button class="bg-cyan-600 hover:bg-cyan-500 text-white border-b-4 border-cyan-800 active:border-b-0 active:translate-y-[2px] transition-all duration-100 px-6 shadow-lg shadow-cyan-900/20">
            <LucideIcon
              name="UserPlus"
              class="w-4 h-4 mr-2"
            />
            {{ $t('features.school.students.actions.add') }}
          </Button>
        </router-link>
      </div>

      <!-- Decorative background blur -->
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-cyan-600/10 rounded-full blur-[100px] pointer-events-none" />
    </div>

    <!-- Quick Stats Container -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div 
        v-for="(stat, index) in summaryStats" 
        :key="index"
        class="group relative bg-[#0a1524]/60 backdrop-blur-xl border border-slate-800 rounded-2xl p-5 hover:border-cyan-500/50 transition-all duration-500 hover:shadow-[0_0_20px_rgba(6,182,212,0.15)] overflow-hidden"
      >
        <div class="flex justify-between items-start mb-3">
          <div :class="`w-12 h-12 rounded-xl flex items-center justify-center border transition-colors duration-500 ${stat.bgColor}`">
            <LucideIcon
              :name="stat.icon"
              :class="`w-6 h-6 ${stat.iconColor}`"
            />
          </div>
          <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">{{ stat.label }}</span>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-3xl font-bold text-white group-hover:scale-105 transition-transform duration-500">
            {{ stat.value }}
          </h3>
          <span class="text-xs text-slate-400">Siswa</span>
        </div>
        
        <!-- Decoration -->
        <div class="absolute -bottom-2 -right-2 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-500 pointer-events-none">
          <LucideIcon
            :name="stat.icon"
            class="w-20 h-20 text-white"
          />
        </div>
      </div>
    </div>

    <!-- Main Table Section -->
    <div class="relative group">
      <!-- Glow decoration -->
      <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-[21px] blur-sm opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200" />
      
      <div class="relative bg-[#0a1524]/80 backdrop-blur-2xl border border-slate-800 rounded-[20px] overflow-hidden shadow-2xl">
        <!-- Table Header Extra (Search/Filter) -->
        <div class="p-6 border-b border-slate-800/50 flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="relative w-full sm:w-96">
            <LucideIcon
              name="Search"
              class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500"
            />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari NISN, Nama atau NIS..."
              class="w-full bg-slate-900/50 border border-slate-800 rounded-xl py-2 pl-10 pr-4 text-sm text-slate-300 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/20 transition-all placeholder:text-slate-600" 
              @input="handleSearch"
            >
          </div>
          
          <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-900/50 px-3 py-1.5 rounded-lg border border-slate-800/50">
            <span class="w-2 h-2 rounded-full bg-cyan-500" />
            Real-time Data Active
          </div>
        </div>

        <CardContent class="p-0">
          <DataTable
            :table="table"
            :loading="loading"
            class="student-table"
          />
          
          <!-- Custom Pagination -->
          <div class="p-6 border-t border-slate-800/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-slate-500 order-2 md:order-1">
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
      </div>
    </div>
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
      bgColor: 'bg-cyan-500/10 border-cyan-500/20', 
      iconColor: 'text-cyan-400' 
    },
    { 
      label: 'SISWA AKTIF', 
      value: list.filter(s => s.status === 'active').length, 
      icon: 'ShieldCheck', 
      bgColor: 'bg-emerald-500/10 border-emerald-500/20', 
      iconColor: 'text-emerald-400' 
    },
    { 
      label: 'LAKI-LAKI', 
      value: list.filter(s => s.gender === 'L').length, 
      icon: 'Users', 
      bgColor: 'bg-blue-500/10 border-blue-500/20', 
      iconColor: 'text-blue-400' 
    },
    { 
      label: 'PEREMPUAN', 
      value: list.filter(s => s.gender === 'P').length, 
      icon: 'User', 
      bgColor: 'bg-pink-500/10 border-pink-500/20', 
      iconColor: 'text-pink-400' 
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
      h(Avatar, { class: 'h-9 w-9 rounded-full border border-slate-700 bg-slate-900 group-hover:border-cyan-500/50 transition-colors' }, [
        h(AvatarFallback, { class: 'bg-[#0a1524] text-cyan-400 font-bold text-xs uppercase' }, () => info.row.original.full_name?.substring(0, 2))
      ]),
      h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'font-medium text-slate-100' }, info.getValue()),
        h('span', { class: 'text-[10px] text-slate-500 font-mono uppercase tracking-widest' }, info.row.original.nis || '-')
      ])
    ]),
  }),
  columnHelper.accessor('nisn', {
    header: 'NISN',
    cell: info => h('span', { class: 'font-mono text-xs text-slate-400' }, info.getValue() || '-'),
  }),
  columnHelper.accessor('gender', {
    header: t('features.school.students.labels.genderShort'),
    cell: info => {
      const val = info.getValue();
      const isMale = val === 'L';
      return h('span', { 
        class: `px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
          isMale ? 'bg-blue-500/10 text-blue-400' : 'bg-pink-500/10 text-pink-400'
        }` 
      }, val);
    },
  }),
  columnHelper.accessor('department.name', {
    header: t('features.school.students.labels.level'),
    cell: info => h('div', { class: 'flex flex-col' }, [
      h('span', { class: 'text-sm text-slate-200' }, info.row.original.department?.code || '-'),
      h('span', { class: 'text-[10px] text-slate-500' }, info.row.original.level?.name || '-')
    ]),
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: info => h('span', { 
      class: 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
    }, info.getValue()),
  }),
  columnHelper.display({
    id: 'actions',
    header: '',
    cell: ({ row }) => h('div', { class: 'flex justify-end gap-1 px-2' }, [
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-cyan-400 hover:bg-cyan-500/20 hover:text-cyan-300',
        title: t('features.school.students.actions.printID'),
        onClick: () => window.open(`${api.defaults.baseURL}/admin/reports/students/${row.original.id}/id-card`, '_blank')
      }, () => h(LucideIcon, { name: 'IdCard', class: 'w-3.5 h-3.5' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-amber-400 hover:bg-amber-500/20 hover:text-amber-300',
        title: t('common.actions.view'),
        onClick: () => router.push({ name: 'students.show', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Eye', class: 'w-3.5 h-3.5' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-emerald-400 hover:bg-emerald-500/20 hover:text-emerald-300',
        title: t('common.actions.edit'),
        onClick: () => router.push({ name: 'students.edit', params: { id: row.original.id } })
      }, () => h(LucideIcon, { name: 'Edit2', class: 'w-3.5 h-3.5' })),
      h(Button, {
        variant: 'ghost',
        size: 'icon',
        class: 'h-8 w-8 text-rose-400 hover:bg-rose-500/20 hover:text-rose-300',
        title: t('common.actions.delete'),
        onClick: () => handleDelete(row.original.id)
      }, () => h(LucideIcon, { name: 'Trash2', class: 'w-3.5 h-3.5' }))
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

.animate-fade-in-down {
  animation: fadeInDown 0.8s ease-out forwards;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

:deep(.student-table table) {
  @apply border-separate px-4;
  border-spacing: 0 8px;
}

:deep(.student-table thead tr) {
  @apply bg-transparent border-none opacity-60 text-[10px] uppercase tracking-[2px] font-mono;
}

:deep(.student-table tbody tr) {
  @apply bg-[#0d1b2e]/40 hover:bg-[#15273f]/60 transition-all duration-300 border border-slate-800/50 rounded-xl mb-4;
}

:deep(.student-table td) {
  @apply py-4 border-none first:rounded-l-xl last:rounded-r-xl;
}

:deep(.student-table th) {
  @apply border-none pb-4;
}

/* Glassmorphism scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #050b14;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #06b6d4;
}
</style>
