<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ $t('features.school.finance.labels.payment') }}</DialogTitle>
      </DialogHeader>
      
      <div
        v-if="bill"
        class="bg-muted/30 p-4 rounded-lg space-y-2 mb-4"
      >
        <div class="flex justify-between text-sm">
          <span class="text-muted-foreground">{{ $t('common.labels.student') }}:</span>
          <span class="font-medium">{{ bill.student?.full_name }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-muted-foreground">{{ $t('features.school.finance.tabs.bills') }}:</span>
          <span class="font-medium">{{ bill.fee_type?.name }}</span>
        </div>
        <div class="flex justify-between text-sm border-t pt-2 mt-2">
          <span class="text-muted-foreground font-bold">{{ $t('features.school.finance.labels.remainingBill') }}:</span>
          <span class="font-bold text-primary">{{ formatCurrency(bill.amount - bill.paid_amount) }}</span>
        </div>
      </div>

      <div
        v-if="isSuccess"
        class="py-6 text-center space-y-4"
      >
        <div class="flex justify-center">
          <div class="p-3 bg-success/10 rounded-full">
            <LucideIcon
              name="CheckCircle"
              class="w-12 h-12 text-success"
            />
          </div>
        </div>
        <p class="text-sm text-muted-foreground">
          {{ $t('features.school.finance.messages.paymentSuccess') }}
        </p>
        <div class="flex justify-center gap-3 pt-4">
          <Button
            variant="outline"
            @click="$emit('update:open', false)"
          >
            {{ $t('common.actions.done') }}
          </Button>
          <Button @click="handlePrint">
            <LucideIcon
              name="Printer"
              class="w-4 h-4 mr-2"
            />
            {{ $t('common.actions.printReceipt') }}
          </Button>
        </div>
      </div>

      <form
        v-else
        class="space-y-4 py-2"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.paidAmount') }} (Rp) <span class="text-destructive">*</span></Label>
          <Input
            v-model="form.amount"
            type="number"
            required
            :max="bill ? bill.amount - bill.paid_amount : undefined"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>{{ $t('features.school.finance.labels.paymentMethod') }}</Label>
            <Select
              v-model="form.payment_method"
              required
            >
              <SelectTrigger><SelectValue :placeholder="$t('features.school.finance.placeholders.selectPaymentMethod')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="Tunai">
                  {{ $t('features.school.finance.paymentMethods.cash') }}
                </SelectItem>
                <SelectItem value="Transfer Bank">
                  {{ $t('features.school.finance.paymentMethods.bankTransfer') }}
                </SelectItem>
                <SelectItem value="E-Wallet">
                  {{ $t('features.school.finance.paymentMethods.eWallet') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.school.finance.labels.paymentDate') }}</Label>
            <Input
              v-model="form.payment_date"
              type="date"
              required
            />
          </div>
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.referenceNumber') }}</Label>
          <Input
            v-model="form.reference_number"
            placeholder="Contoh: No. Resi / ID Transaksi"
          />
        </div>

        <div class="space-y-2">
          <Label>{{ $t('features.school.finance.labels.notes') }}</Label>
          <Textarea
            v-model="form.notes"
            :placeholder="$t('features.school.finance.placeholders.notesHint')"
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
            {{ $t('common.actions.save') }} {{ $t('features.school.finance.labels.payment') }}
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
  Button, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea, LucideIcon
} from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';
import { useToast } from '@/composables/useToast';

const { t } = useI18n();
const props = defineProps<{
  open: boolean;
  bill: any;
}>();

const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'Tunai',
  reference_number: '',
  notes: ''
});

watch(() => props.open, (val) => {
  if (val && props.bill) {
    form.value.amount = props.bill.amount - props.bill.paid_amount;
  }
});

const isSuccess = ref(false);
const transactionId = ref<number | null>(null);

const handleSubmit = async () => {
  loading.value = true;
  try {
    const response = await api.post(`/admin/finance/bills/${props.bill.id}/pay`, form.value);
    const data = parseResponse(response).data as any;
    transactionId.value = data.id;
    isSuccess.value = true;
    toast.success.action(t('features.school.finance.messages.paymentSuccess'));
    emit('save');
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    loading.value = false;
  }
};

const handlePrint = () => {
  const url = `${import.meta.env.VITE_API_URL}/admin/reports/payments/${transactionId.value}/pdf`;
  window.open(url, '_blank');
  emit('update:open', false);
};

const formatCurrency = (val: any) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
};
</script>
