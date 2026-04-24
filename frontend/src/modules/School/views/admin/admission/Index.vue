<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('features.school.admission.title') }}
        </h1>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.admission.subtitle') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button @click="dialogs.register = true">
          <LucideIcon
            name="UserPlus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.admission.actions.manualRegister') }}
        </Button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card
        v-for="(stat, key) in stats"
        :key="key"
        class="bg-card"
      >
        <CardContent class="p-4 flex items-center gap-4">
          <div :class="`p-2 rounded-lg ${stat.color}`">
            <LucideIcon
              :name="stat.icon"
              class="w-5 h-5"
            />
          </div>
          <div>
            <p class="text-xs text-muted-foreground uppercase font-bold">
              {{ stat.label }}
            </p>
            <p class="text-2xl font-bold">
              {{ stat.value }}
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardContent class="p-0">
        <div class="p-6 border-b flex flex-wrap gap-4 justify-between items-center">
          <div class="flex gap-4">
            <div class="relative w-64">
              <LucideIcon
                name="Search"
                class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"
              />
              <Input
                v-model="filters.search"
                :placeholder="$t('features.school.admission.placeholders.search')"
                class="pl-9"
              />
            </div>
            <Select v-model="filters.status">
              <SelectTrigger class="w-40">
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
        
        <div class="p-4 border-t">
          <Pagination
            v-if="pagination && (pagination.total || 0) > (pagination.per_page || 0)"
            v-model:current-page="pagination.current_page"
            :total-items="pagination.total || 0"
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
  columnHelper.accessor('registration_number', { header: t('features.school.admission.labels.regNumber'), cell: info => h('span', { class: 'font-mono font-bold text-primary' }, info.getValue()) }),
  columnHelper.accessor('full_name', { header: t('common.labels.name') }),
  columnHelper.accessor('nisn', { header: 'NISN' }),
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
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-medium ${config?.class || ''}` }, config?.text || '');
    }
  }),
  columnHelper.accessor('created_at', { 
    header: t('features.school.admission.labels.regNumber'), 
    cell: info => new Date(info.getValue()).toLocaleDateString(t('common.language') === 'id' ? 'id-ID' : 'en-US') 
  }),
  columnHelper.display({
    id: 'actions',
    header: t('common.labels.status'),
    cell: ({ row }) => h(Button, {
      variant: 'ghost',
      size: 'sm',
      onClick: () => router.push({ name: 'admission.show', params: { id: row.original.id } })
    }, t('common.actions.view'))
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
