<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Data Pelamar Kerja</DialogTitle>
        <DialogDescription>Daftar alumni/siswa yang melamar lowongan di Career Center.</DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <DataTable
          :table="table"
          :loading="loading"
        />
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          Tutup
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, h } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, DataTable, Badge
} from '@/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{ open: boolean }>();
defineEmits(['update:open']);
const loading = ref(false);
const applications = ref<any[]>([]);

const columnHelper = createColumnHelper<any>();
const columns = [
   columnHelper.accessor('student.full_name', { header: 'Pelamar' }),
   columnHelper.accessor('vacancy.position', { header: 'Posisi' }),
   columnHelper.accessor('vacancy.company_name', { header: 'Perusahaan' }),
   columnHelper.accessor('status', {
      header: 'Status',
      cell: info => h(Badge, { variant: 'outline', class: 'text-[10px] uppercase font-bold' }, info.getValue())
   }),
   columnHelper.accessor('created_at', { 
      header: 'Tanggal',
      cell: info => new Date(info.getValue()).toLocaleDateString()
   }),
];

const table = useVueTable({
   get data() { return applications.value },
   get columns() { return columns },
   getCoreRowModel: getCoreRowModel()
});

const fetchApplications = async () => {
   loading.value = true;
   try {
      const response = await LogisticsService.getApplications();
      applications.value = parseResponse(response).data;
   } catch {
      // Ignored
   } finally {
      loading.value = false;
   }
}

watch(() => props.open, (isOpen) => {
   if (isOpen) fetchApplications();
});
</script>
