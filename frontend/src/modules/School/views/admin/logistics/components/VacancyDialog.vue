<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Posting Lowongan Baru</DialogTitle>
        <DialogDescription>Input detail lowongan kerja untuk alumni/siswa.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="company">Nama Perusahaan</Label>
          <Input
            id="company"
            v-model="form.company_name"
            placeholder="Contoh: PT. Maju Bersama"
          />
        </div>
        <div class="grid gap-2">
          <Label for="pos">Posisi / Jabatan</Label>
          <Input
            id="pos"
            v-model="form.position"
            placeholder="Contoh: Admin Gudang"
          />
        </div>
        <div class="grid gap-2">
          <Label for="desc">Deskripsi Pekerjaan</Label>
          <Textarea
            id="desc"
            v-model="form.description"
            rows="3"
          />
        </div>
        <div class="grid gap-2">
          <Label for="dl">Deadline Pendaftaran</Label>
          <Input
            id="dl"
            v-model="form.deadline"
            type="date"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          Batal
        </Button>
        <Button
          :loading="loading"
          @click="handleSubmit"
        >
          Posting Sekarang
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Textarea
} from '@/shared/components/ui';
import api from '@/core/api/client';
import { useToast } from '@/shared/composables/useToast';

defineProps<{ open: boolean }>();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
  school_id: 1,
  company_name: '',
  position: '',
  description: '',
  deadline: ''
});

const handleSubmit = async () => {
  if (!form.value.company_name || !form.value.position) return toast.error.action('Data wajib diisi');
  
  loading.value = true;
  try {
    await api.post('/admin/logistics/career/vacancies', form.value);
    toast.success.action('Lowongan berhasil diposting');
    emit('save');
    emit('update:open', false);
    form.value = { school_id: 1, company_name: '', position: '', description: '', deadline: '' };
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
