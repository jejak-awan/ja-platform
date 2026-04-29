<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="UserCheck"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('features.school.admission.title') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('features.school.admission.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button 
          class="rounded-xl shadow-sm px-6"
          @click="dialogs.register = true"
        >
          <LucideIcon
            name="UserPlus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.admission.actions.manualRegister') }}
        </Button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="(stat, key) in stats" 
        :key="key"
        class="group bg-card border border-border/40 rounded-xl p-6 shadow-none hover:bg-muted/30 transition-all duration-300"
      >
        <div class="flex justify-between items-start mb-4">
          <div :class="`w-10 h-10 rounded-xl flex items-center justify-center border border-border/40 bg-muted/50 ${stat.color.split(' ')[1]}`">
            <LucideIcon
              :name="stat.icon"
              class="w-5 h-5"
            />
          </div>
          <span class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">{{ stat.label }}</span>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-2xl font-black tracking-tight text-foreground">
            {{ stat.value }}
          </h3>
          <span class="text-[10px] font-bold text-muted-foreground uppercase">{{ $t('common.labels.student') }}</span>
        </div>
      </div>
    </div>

    <Card class="border border-border/40 bg-card shadow-none rounded-xl overflow-hidden">
      <CardContent class="p-0">
        <div class="p-6 border-b border-border/40 flex flex-wrap gap-4 justify-between items-center bg-muted/10">
          <div class="flex gap-4">
            <div class="relative w-64">
              <LucideIcon
                name="Search"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
              />
              <Input
                v-model="filters.search"
                :placeholder="$t('features.school.admission.placeholders.search')"
                class="pl-10 rounded-xl bg-background border-border/40"
              />
            </div>
            <Select v-model="filters.status">
              <SelectTrigger class="w-40 rounded-xl bg-background border-border/40">
                <SelectValue :placeholder="$t('common.labels.status')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ $t('common.labels.all') }}
                </SelectItem>
                <SelectItem value="applied">
                  {{ $t('features.school.admission.labels.new') }}
                </SelectItem>
                <SelectItem value="verified">
                  {{ $t('features.school.admission.labels.verified') }}
                </SelectItem>
                <SelectItem value="exam">
                  {{ $t('features.school.admission.labels.selection') }}
                </SelectItem>
                <SelectItem value="admitted">
                  {{ $t('features.school.admission.labels.admitted') }}
                </SelectItem>
                <SelectItem value="rejected">
                  {{ $t('features.school.admission.labels.rejected') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DataTable
          :table="table"
          :loading="loading"
        />
        
        <div class="p-6 border-t border-border/40 flex justify-between items-center bg-muted/5">
          <p class="text-xs font-bold text-muted-foreground/60 uppercase tracking-widest">
             TOTAL: {{ pagination?.total || 0 }}
          </p>
          <Pagination
            v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
            v-model:current-page="pagination.current_page"
            :total-items="pagination.total || 0"
            :per-page="pagination.per_page || 20"
          />
        </div>
      </CardContent>
    </Card>

    <!-- Dialogs -->
    <RegistrationDialog
      v-model:open="dialogs.register"
      @save="fetchEnrollments"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Card, CardContent, Button, LucideIcon, DataTable, Input,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Pagination
} from '@/components/ui';
import { AdmissionService } from '@/modules/School/services/AdmissionService';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import { useRouter } from 'vue-router';

import RegistrationDialog from './components/RegistrationDialog.vue';

const { t } = useI18n();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const enrollments = ref<any[]>([]);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });

const dialogs = ref({
  register: false
});

const filters = ref({
  search: '',
  status: 'all'
});

const stats = ref({
  total: { label: t('features.school.admission.labels.totalApplicants'), value: 0, icon: 'Users', color: 'bg-primary/10 text-primary' },
  applied: { label: t('features.school.admission.labels.new'), value: 0, icon: 'UserPlus', color: 'bg-blue-500/10 text-blue-500' },
  verified: { label: t('features.school.admission.labels.verified'), value: 0, icon: 'CheckCircle', color: 'bg-green-500/10 text-green-500' },
  admitted: { label: t('features.school.admission.labels.admitted'), value: 0, icon: 'CheckSquare', color: 'bg-purple-500/10 text-purple-500' }
});

const columnHelper = createColumnHelper<any>();

const columns = [
  columnHelper.accessor('registration_number', { header: t('features.school.admission.labels.regNumber'), cell: info => h('span', { class: 'text-xs font-black tracking-widest text-primary uppercase' }, info.getValue()) }),
  columnHelper.accessor('full_name', { header: t('common.labels.name'), cell: info => h('span', { class: 'text-sm font-bold text-foreground' }, info.getValue()) }),
  columnHelper.accessor('nisn', { header: 'NISN', cell: info => h('span', { class: 'text-xs font-bold text-muted-foreground' }, info.getValue() || '-') }),
  columnHelper.accessor('status', {
    header: t('common.labels.status'),
    cell: info => {
      const status = info.getValue();
      const labels: Record<string, { text: string; class: string }> = {
        applied: { text: t('features.school.admission.labels.new'), class: 'bg-blue-500/10 text-blue-500' },
        verified: { text: t('features.school.admission.labels.verified'), class: 'bg-green-500/10 text-green-500' },
        exam: { text: t('features.school.admission.labels.selection'), class: 'bg-warning/10 text-warning' },
        admitted: { text: t('features.school.admission.labels.admitted'), class: 'bg-purple-500/10 text-purple-500' },
        rejected: { text: t('features.school.admission.labels.rejected'), class: 'bg-destructive/10 text-destructive' },
        draft: { text: 'Draft', class: 'bg-muted text-muted-foreground' }
      };
      const config = labels[status as string] || labels.draft;
      return h('span', { class: `px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] border border-current ${config?.class || ''}` }, config?.text || '');
    }
  }),
  columnHelper.accessor('created_at', { 
    header: t('features.school.admission.labels.regNumber'), 
    cell: info => h('span', { class: 'text-xs font-medium text-muted-foreground' }, new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US'))
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h(Button, {
      variant: 'ghost',
      size: 'sm',
      class: 'rounded-xl h-8 text-muted-foreground hover:bg-primary/10 hover:text-primary font-bold',
      onClick: () => router.push({ name: 'admission.show', params: { id: row.original.id } })
    }, () => t('common.actions.view'))
  })
];

const table = useVueTable({
  get data() { return enrollments.value },
  get columns() { return columns },
  getCoreRowModel: getCoreRowModel(),
});

const fetchEnrollments = async () => {
  loading.value = true;
  try {
    const params = {
      ...filters.value,
      page: pagination.value?.current_page || 1
    };
    if (params.status === 'all') delete (params as any).status;
    const response = await AdmissionService.getEnrollments(params);
    const { data, pagination: pagin } = parseResponse(response);
    enrollments.value = data;
    if (pagin && pagination.value) {
      pagination.value.total = pagin.total || 0;
    }
    
    // Simple stat calculation from current page for demo or separate API
    stats.value.total.value = pagin?.total || 0;
    // (In real app, backend should return these stats)
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

watch([() => pagination.value.current_page, filters], fetchEnrollments, { deep: true });

onMounted(fetchEnrollments);
</script>
