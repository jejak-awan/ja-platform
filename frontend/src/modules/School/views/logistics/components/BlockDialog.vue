<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Tambah Blok Asrama</DialogTitle>
        <DialogDescription>
          Masukkan detail untuk blok asrama baru.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="name">Nama Blok</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Contoh: Blok A - Putra"
          />
        </div>
        <div class="grid gap-2">
          <Label for="gender">Kategori Gender</Label>
          <Select v-model="form.gender">
            <SelectTrigger>
              <SelectValue placeholder="Pilih Gender" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="male">
                Putra (Male)
              </SelectItem>
              <SelectItem value="female">
                Putri (Female)
              </SelectItem>
              <SelectItem value="mixed">
                Campuran (Mixed)
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="grid gap-2">
          <Label for="description">Keterangan</Label>
          <Textarea
            id="description"
            v-model="form.description"
            placeholder="Deskripsi opsional..."
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
          Simpan Blok
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';

defineProps<{
  open: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
  school_id: "1", // Dynamic school ID if needed
  name: '',
  gender: 'mixed',
  description: ''
});

const handleSubmit = async () => {
  if (!form.value.name) return toast.error.action('Nama blok wajib diisi');
  
  loading.value = true;
  try {
    await api.post('/admin/logistics/hostel/blocks', form.value);
    toast.success.action('Blok berhasil ditambahkan');
    emit('save');
    emit('update:open', false);
    // Reset form
    form.value = { school_id: "1", name: '', gender: 'mixed', description: '' };
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
