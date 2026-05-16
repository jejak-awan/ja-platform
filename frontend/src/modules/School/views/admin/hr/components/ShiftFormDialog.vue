<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? 'Edit Shift' : 'Tambah Shift Baru' }}</DialogTitle>
      </DialogHeader>
      
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">Nama Shift <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
            placeholder="Contoh: Shift Pagi"
          />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="start_time">Jam Mulai</Label>
            <Input
              id="start_time"
              v-model="form.start_time"
              type="time"
              required
            />
          </div>
          <div class="space-y-2">
            <Label for="end_time">Jam Selesai</Label>
            <Input
              id="end_time"
              v-model="form.end_time"
              type="time"
              required
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label for="description">Keterangan</Label>
          <Textarea
            id="description"
            v-model="form.description"
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
            Simpan
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
  Button, Input, Label, Textarea, LucideIcon
} from '@/shared/components/ui';
import { HRService } from '@/modules/School/services/HRService';
import { useToast } from '@/shared/composables/useToast';

const props = defineProps<{
  open: boolean;
  initialData?: any;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);
const isEdit = ref(false);

const form = ref({
  id: undefined as number | undefined,
  school_id: "1",
  name: '',
  start_time: '07:00',
  end_time: '14:00',
  description: ''
});

watch(() => props.open, (val) => {
  if (val) {
    if (props.initialData) {
      form.value = { ...props.initialData };
      isEdit.value = true;
    } else {
      form.value = {
        id: undefined,
        school_id: "1",
        name: '',
        start_time: '07:00',
        end_time: '14:00',
        description: ''
      };
      isEdit.value = false;
    }
  }
});

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEdit.value) {
      await HRService.updateShift(form.value.id!, form.value);
      toast.success.action('Shift berhasil diperbarui');
    } else {
      await HRService.storeShift(form.value);
      toast.success.action('Shift berhasil ditambahkan');
    }
    emit('save');
    emit('update:open', false);
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};
</script>
