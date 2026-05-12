<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Registrasi Kendaraan</DialogTitle>
        <DialogDescription>Masukkan detail armada transportasi sekolah baru.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="plate">Nomor Plat</Label>
          <Input
            id="plate"
            v-model="form.plate_number"
            placeholder="Contoh: B 1234 ABC"
          />
        </div>
        <div class="grid gap-2">
          <Label for="model">Model/Tipe</Label>
          <Input
            id="model"
            v-model="form.model"
            placeholder="Contoh: Toyota Hiace"
          />
        </div>
        <div class="grid gap-2">
          <Label for="capacity">Kapasitas Kursi</Label>
          <Input
            id="capacity"
            v-model="form.capacity"
            type="number"
            min="1"
          />
        </div>
        <div class="grid gap-2">
          <Label for="driver">Nama Driver</Label>
          <Input
            id="driver"
            v-model="form.driver_name"
          />
        </div>
        <div class="grid gap-2">
          <Label for="phone">No. Telp Driver</Label>
          <Input
            id="phone"
            v-model="form.driver_phone"
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
          Simpan Kendaraan
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label
} from '@/shared/components/ui';
import api from '@/core/api/client';
import { useToast } from '@/shared/composables/useToast';

defineProps<{ open: boolean }>();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
  school_id: 1,
  plate_number: '',
  model: '',
  capacity: 15,
  driver_name: '',
  driver_phone: ''
});

const handleSubmit = async () => {
  if (!form.value.plate_number || !form.value.model) return toast.error.action('Plat dan Model wajib diisi');
  
  loading.value = true;
  try {
    await api.post('/admin/logistics/transport/vehicles', form.value);
    toast.success.action('Kendaraan berhasil didaftarkan');
    emit('save');
    emit('update:open', false);
    form.value = { school_id: 1, plate_number: '', model: '', capacity: 15, driver_name: '', driver_phone: '' };
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
