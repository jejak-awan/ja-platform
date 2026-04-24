<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('features.school.finance.tabs.expenses') : $t('features.school.finance.actions.addExpense') }}</DialogTitle>
        <DialogDescription>
          {{ $t('features.school.finance.messages.expenseDescription') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label>{{ $t('features.school.finance.labels.category') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.category"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.selectCategory')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="Operasional">
                {{ $t('features.school.finance.categories.ops') }}
              </SelectItem>
              <SelectItem value="Pemeliharaan">
                {{ $t('features.school.finance.categories.maintenance') }}
              </SelectItem>
              <SelectItem value="SDM / Gaji">
                {{ $t('features.school.finance.categories.hr_sub') }}
              </SelectItem>
              <SelectItem value="Sarana Prasarana">
                {{ $t('features.school.finance.categories.sarpras') }}
              </SelectItem>
              <SelectItem value="Lainnya">
                {{ $t('features.school.finance.categories.others') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label for="amount">{{ $t('common.labels.amount') }} (Rp) <span class="text-destructive">*</span></Label>
          <Input
            id="amount"
            v-model="form.amount"
            type="number"
            required
            placeholder="0"
          />
        </div>

        <div class="grid gap-2">
          <Label for="date">{{ $t('common.labels.date') }} <span class="text-destructive">*</span></Label>
          <Input
            id="date"
            v-model="form.date"
            type="date"
            required
          />
        </div>

        <div class="grid gap-2">
          <Label for="description">{{ $t('common.labels.description') }} <span class="text-destructive">*</span></Label>
          <Input
            id="description"
            v-model="form.description"
            required
            placeholder="Contoh: Pembelian Alat Tulis"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="ghost"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.actions.cancel') }}
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
          {{ $t('common.actions.save') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/components/ui';

const props = defineProps<{
  open: boolean;
  isEdit: boolean;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const form = ref<any>({
  category: 'Operasional',
  amount: 0,
  date: new Date().toISOString().split('T')[0],
  description: '',
});

watch(() => props.open, (newVal) => {
  if (newVal) {
    if (props.initialData) {
      form.value = { 
          ...props.initialData,
          date: props.initialData.date ? props.initialData.date.split('T')[0] : new Date().toISOString().split('T')[0]
      };
    } else {
      form.value = {
        category: 'Operasional',
        amount: 0,
        date: new Date().toISOString().split('T')[0],
        description: '',
      };
    }
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};
</script>
