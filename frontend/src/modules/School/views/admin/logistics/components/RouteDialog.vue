<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Tambah Rute Perjalanan</DialogTitle>
        <DialogDescription>Definisikan rute antar jemput siswa.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="name">Nama Rute</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Contoh: Rute Bekasi - Jakarta"
          />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="grid gap-2">
            <Label for="start">Awal</Label>
            <Input
              id="start"
              v-model="form.start_location"
              placeholder="Lokasi Awal"
            />
          </div>
          <div class="grid gap-2">
            <Label for="end">Akhir</Label>
            <Input
              id="end"
              v-model="form.end_location"
              placeholder="Lokasi Akhir"
            />
          </div>
        </div>
        <div class="grid gap-2">
          <Label for="fee">Biaya Layanan (Per Bulan)</Label>
          <Input
            id="fee"
            v-model="form.fee"
            type="number"
          />
        </div>
        <div class="grid gap-2">
          <Label for="stops">Pemberhentian (Dipisah koma)</Label>
          <Textarea
            id="stops"
            v-model="stopInput"
            placeholder="Contoh: Halte A, Simpang B"
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
          Simpan Rute
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
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';

defineProps<{ open: boolean }>();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const stopInput = ref('');

const form = ref({
  school_id: "1",
  name: '',
  start_location: '',
  end_location: '',
  stops: [] as string[],
  fee: 0
});

const handleSubmit = async () => {
  if (!form.value.name) return toast.error.action('Nama rute wajib diisi');
  
  form.value.stops = stopInput.value.split(',').map(s => s.trim()).filter(s => s);
  
  loading.value = true;
  try {
    await api.post('/admin/logistics/transport/routes', form.value);
    toast.success.action('Rute berhasil dibuat');
    emit('save');
    emit('update:open', false);
    form.value = { school_id: "1", name: '', start_location: '', end_location: '', stops: [], fee: 0 };
    stopInput.value = '';
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
