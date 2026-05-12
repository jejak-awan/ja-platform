<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Tambah Kamar Asrama</DialogTitle>
        <DialogDescription>
          Masukkan detail kamar baru. Bed akan digenerate otomatis sesuai kapasitas.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="number">Nomor Kamar</Label>
          <Input
            id="number"
            v-model="form.room_number"
            placeholder="Contoh: 101"
          />
        </div>
        <div class="grid gap-2">
          <Label for="capacity">Kapasitas (Jumlah Bed)</Label>
          <Input
            id="capacity"
            v-model="form.capacity"
            type="number"
            min="1"
          />
        </div>
        <div class="grid gap-2">
          <Label for="features">Fasilitas (Dipisah koma)</Label>
          <Input
            id="features"
            v-model="featureInput"
            placeholder="Contoh: AC, KM Dalam, Lemari"
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
          Simpan Kamar
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

const props = defineProps<{
  open: boolean;
  blockId: number;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const featureInput = ref('');

const form = ref({
  room_number: '',
  capacity: 4,
  features: [] as string[]
});

const handleSubmit = async () => {
  if (!form.value.room_number) return toast.error.action('Nomor kamar wajib diisi');
  
  form.value.features = featureInput.value.split(',').map(f => f.trim()).filter(f => f);
  
  loading.value = true;
  try {
    await api.post(`/admin/logistics/hostel/blocks/${props.blockId}/rooms`, form.value);
    toast.success.action('Kamar berhasil ditambahkan');
    emit('save');
    emit('update:open', false);
    // Reset
    form.value = { room_number: '', capacity: 4, features: [] };
    featureInput.value = '';
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
