<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Input Permohonan Cuti / Izin</DialogTitle>
      </DialogHeader>
      
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>Nama PTK <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.staff_id"
            required
          >
            <SelectTrigger><SelectValue placeholder="Pilih PTK" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="item in staffList"
                :key="item.id"
                :value="item.id"
              >
                {{ item.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>Jenis Izin</Label>
          <Select
            v-model="form.type"
            required
          >
            <SelectTrigger><SelectValue placeholder="Pilih Jenis" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="Cuti Tahunan">
                Cuti Tahunan
              </SelectItem>
              <SelectItem value="Sakit">
                Sakit
              </SelectItem>
              <SelectItem value="Izin Penting">
                Izin Penting
              </SelectItem>
              <SelectItem value="Umroh/Haji">
                Umroh/Haji
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Tgl Mulai</Label>
            <Input
              v-model="form.start_date"
              type="date"
              required
            />
          </div>
          <div class="space-y-2">
            <Label>Tgl Selesai</Label>
            <Input
              v-model="form.end_date"
              type="date"
              required
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Alasan / Keterangan</Label>
          <Textarea
            v-model="form.reason"
            placeholder="Jelaskan alasan permohonan..."
          />
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="$emit('update:open', false)"
          >
            Batal
          </Button>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            Kirim Permohonan
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea, Input, LucideIcon
} from '@/shared/components/ui';
import { HRService } from '@/modules/School/services/HRService';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';

const props = defineProps<{ open: boolean }>();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const staffList = ref<any[]>([]);

const form = ref<any>({
  school_id: "1",
  staff_id: undefined,
  type: 'Cuti Tahunan',
  start_date: new Date().toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0],
  reason: ''
});

const fetchData = async () => {
  try {
    const response = await HRService.getStaff();
    const res = parseResponse(response);
    staffList.value = res.data || [];
  } catch (e) {
    console.error(e);
  }
};

watch(() => props.open, (val) => {
  if (val) fetchData();
});

const handleSubmit = async () => {
  loading.value = true;
  try {
    await HRService.storeLeave(form.value);
    toast.success.action('Permohonan berhasil dikirim');
    emit('save');
    emit('update:open', false);
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
