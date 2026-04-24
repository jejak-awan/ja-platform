<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('common.actions.edit') + ' ' + $t('features.school.finance.tabs.feeTypes') : $t('features.school.finance.actions.addFeeType') }}</DialogTitle>
      </DialogHeader>
      
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label for="name">{{ $t('features.school.finance.labels.feeName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
            placeholder="Contoh: SPP Bulanan"
          />
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="code">{{ $t('features.school.finance.labels.feeCode') }}</Label>
            <Input
              id="code"
              v-model="form.code"
              placeholder="SPP"
            />
          </div>
          <div class="space-y-2">
            <Label for="period">{{ $t('features.school.finance.labels.period') }}</Label>
            <Select v-model="form.period">
              <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.selectPeriod')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="monthly">
                  {{ $t('features.school.finance.periods.monthly') }}
                </SelectItem>
                <SelectItem value="quarterly">
                  {{ $t('features.school.finance.periods.quarterly') }}
                </SelectItem>
                <SelectItem value="yearly">
                  {{ $t('features.school.finance.periods.yearly') }}
                </SelectItem>
                <SelectItem value="once">
                  {{ $t('features.school.finance.periods.once') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="amount">{{ $t('features.school.finance.labels.defaultNominal') }} (Rp) <span class="text-destructive">*</span></Label>
          <Input
            id="amount"
            v-model="form.amount"
            type="number"
            required
          />
        </div>

        <div class="space-y-2">
          <Label for="description">{{ $t('common.labels.description') }}</Label>
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
            {{ $t('common.actions.cancel') }}
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
            {{ $t('common.actions.save') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea, LucideIcon
} from '@/components/ui';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
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
  school_id: 1,
  school_level_id: 1,
  name: '',
  code: '',
  amount: 0,
  period: 'monthly',
  description: '',
  is_active: true
});

watch(() => props.open, (val) => {
  if (val) {
    if (props.initialData) {
      form.value = { ...props.initialData };
      isEdit.value = true;
    } else {
      form.value = {
        id: undefined,
        school_id: 1,
        school_level_id: 1,
        name: '',
        code: '',
        amount: 0,
        period: 'monthly',
        description: '',
        is_active: true
      };
      isEdit.value = false;
    }
  }
});

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/admin/finance/fee-types/${form.value.id}`, form.value);
      toast.success.action(t('features.school.academic.messages.updateSuccess'));
    } else {
      await api.post('/admin/finance/fee-types', form.value);
      toast.success.action(t('features.school.academic.messages.addSuccess'));
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
