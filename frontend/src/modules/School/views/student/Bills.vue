<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Tagihan & Pembayaran
      </h1>
      <p class="text-muted-foreground">
        Kelola administrasi keuangan Anda di sekolah ini.
      </p>
    </div>

    <div
      v-if="loading"
      class="space-y-4"
    >
      <SkeletonLoader
        v-for="i in 5"
        :key="i"
        class="h-20 w-full"
      />
    </div>
    <div
      v-else-if="bills.length > 0"
      class="grid gap-6"
    >
      <Card
        v-for="bill in bills"
        :key="bill.id"
        :class="{'border-success/20 bg-success/5': bill.status === 'paid'}"
      >
        <CardContent class="p-6">
          <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <h3 class="font-bold text-lg">
                  {{ bill.fee_type?.name }}
                </h3>
                <Badge :variant="bill.status === 'paid' ? 'success' : bill.status === 'partially_paid' ? 'warning' : 'destructive'">
                  {{ bill.status === 'paid' ? 'Lunas' : bill.status === 'partially_paid' ? 'Cicilan' : 'Belum Bayar' }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">
                Tahun Ajaran: {{ bill.academic_year?.year || '-' }} • 
                Bulan: {{ bill.month ? new Date(2024, bill.month-1).toLocaleString('id-ID', {month: 'long'}) : '-' }}
              </p>
            </div>

            <div class="flex flex-col md:items-end gap-1">
              <p class="text-sm text-muted-foreground">
                Total Tagihan
              </p>
              <p class="text-2xl font-bold">
                {{ formatCurrency(bill.amount) }}
              </p>
            </div>
          </div>

          <Separator class="my-4" />

          <div class="flex flex-col md:flex-row justify-between gap-4">
            <div class="flex gap-4">
              <div>
                <p class="text-[10px] text-muted-foreground uppercase font-semibold">
                  Telah Dibayar
                </p>
                <p class="font-semibold text-success">
                  {{ formatCurrency(bill.paid_amount) }}
                </p>
              </div>
              <div>
                <p class="text-[10px] text-muted-foreground uppercase font-semibold">
                  Sisa Tagihan
                </p>
                <p class="font-semibold text-destructive">
                  {{ formatCurrency(bill.amount - bill.paid_amount) }}
                </p>
              </div>
            </div>

            <div
              v-if="bill.status !== 'paid'"
              class="flex items-center gap-2"
            >
              <LucideIcon
                name="Info"
                class="w-4 h-4 text-warning"
              />
              <p class="text-xs text-muted-foreground">
                Pembayaran dilakukan melalui kasir sekolah.
              </p>
            </div>
          </div>

          <div
            v-if="bill.transactions?.length > 0"
            class="mt-4 pt-4 border-t border-dashed"
          >
            <p class="text-xs font-semibold mb-2">
              Riwayat Transaksi:
            </p>
            <div class="space-y-2">
              <div
                v-for="tx in bill.transactions"
                :key="tx.id"
                class="flex justify-between text-xs p-2 bg-muted/30 rounded"
              >
                <span>{{ new Date(tx.payment_date).toLocaleDateString('id-ID') }} via {{ tx.payment_method }}</span>
                <span class="font-medium text-success">+ {{ formatCurrency(tx.amount) }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    <div
      v-else
      class="text-center py-20 bg-muted/20 rounded-xl border border-dashed"
    >
      <LucideIcon
        name="Wallet"
        class="w-16 h-16 mx-auto mb-4 opacity-10"
      />
      <h3 class="text-lg font-semibold">
        Belum ada tagihan
      </h3>
      <p class="text-muted-foreground">
        Anda tidak memiliki tagihan aktif saat ini.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  Card, CardContent, Badge, Separator, SkeletonLoader, LucideIcon
} from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';

const loading = ref(true);
const bills = ref<any[]>([]);

const fetchBills = async () => {
  try {
    const response = await api.get('/school/student/bills');
    bills.value = parseResponse(response).data || [];
  } catch (e) {
    console.error(e);
  }
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(amount);
};

onMounted(async () => {
  loading.value = true;
  await fetchBills();
  loading.value = false;
});
</script>
