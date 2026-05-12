<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Atur Struktur Gaji</DialogTitle>
        <DialogDescription>
          Tentukan komponen gaji untuk {{ staffName }}.
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="base_salary">Gaji Pokok (Rp) <span class="text-destructive">*</span></Label>
          <Input
            id="base_salary"
            v-model="form.base_salary"
            type="number"
            required
            placeholder="0"
          />
        </div>

        <div class="grid gap-2">
          <Label for="transport">Tunjangan Transport (Rp)</Label>
          <Input
            id="transport"
            v-model="form.transport_allowance"
            type="number"
            placeholder="0"
          />
        </div>

        <div class="grid gap-2">
          <Label for="meal">Tunjangan Makan (Rp)</Label>
          <Input
            id="meal"
            v-model="form.meal_allowance"
            type="number"
            placeholder="0"
          />
        </div>

        <div class="grid gap-2">
          <Label for="other">Tunjangan Lainnya (Rp)</Label>
          <Input
            id="other"
            v-model="form.other_allowance"
            type="number"
            placeholder="0"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="ghost"
          @click="$emit('update:open', false)"
        >
          Batal
        </Button>
        <Button
          :disabled="loading"
          @click="handleSubmit"
        >
          <LucideIcon
            v-if="loading"
            name="Loader2"
            class="w-4 h-4 mr-2 animate-spin"
          />
          Simpan Struktur
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, LucideIcon
} from '@/shared/components/ui';

const props = defineProps<{
  open: boolean;
  staffName: string;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref<any>({
  base_salary: 0,
  transport_allowance: 0,
  meal_allowance: 0,
  other_allowance: 0,
});

watch(() => props.open, (newVal) => {
  if (newVal) {
    if (props.initialData) {
      form.value = { ...props.initialData };
    } else {
      form.value = {
        base_salary: 0,
        transport_allowance: 0,
        meal_allowance: 0,
        other_allowance: 0,
      };
    }
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};
</script>
