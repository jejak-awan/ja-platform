<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Input Barang Baru</DialogTitle>
        <DialogDescription>Tambahkan barang ke master inventaris logistik.</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="sku">SKU / Kode Barang</Label>
          <Input
            id="sku"
            v-model="form.sku"
            placeholder="Contoh: ATK-001"
          />
        </div>
        <div class="grid gap-2">
          <Label for="name">Nama Barang</Label>
          <Input
            id="name"
            v-model="form.name"
            placeholder="Nama barang lengkap"
          />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="grid gap-2">
            <Label>Kategori</Label>
            <Select v-model="form.category_id">
              <SelectTrigger><SelectValue placeholder="Pilih" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="cat in categories"
                  :key="cat.id"
                  :value="String(cat.id)"
                >
                  {{ cat.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label>Satuan</Label>
            <Input
              v-model="form.unit"
              placeholder="pcs, box, dll"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="grid gap-2">
            <Label>Stok Awal</Label>
            <Input
              v-model="form.quantity_on_hand"
              type="number"
              min="0"
            />
          </div>
          <div class="grid gap-2">
            <Label>Stok Minimum</Label>
            <Input
              v-model="form.minimum_stock"
              type="number"
              min="0"
            />
          </div>
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
          Simpan Barang
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import api from '@/core/api/client';
import { useToast } from '@/shared/composables/useToast';

defineProps<{ 
   open: boolean;
   categories: any[];
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
  school_id: 1,
  category_id: '',
  sku: '',
  name: '',
  unit: 'pcs',
  quantity_on_hand: 0,
  minimum_stock: 5,
});

const handleSubmit = async () => {
  if (!form.value.sku || !form.value.name || !form.value.category_id) {
     return toast.error.action('Lengkapi SKU, Nama, dan Kategori');
  }
  
  loading.value = true;
  try {
    await api.post('/admin/logistics/inventory/items', form.value);
    toast.success.action('Barang berhasil disimpan');
    emit('save');
    emit('update:open', false);
    form.value = { school_id: 1, category_id: '', sku: '', name: '', unit: 'pcs', quantity_on_hand: 0, minimum_stock: 5 };
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
