<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center bg-card p-4 rounded-xl border border-border/50 text-left">
      <div class="flex gap-4">
        <div class="relative w-64">
          <LucideIcon
            name="Search"
            class="absolute left-3 top-2.5 w-4 h-4 text-muted-foreground"
          />
          <Input
            v-model="filters.search"
            :placeholder="$t('modules.school.logistics.inventory.placeholders.searchSKU')"
            class="pl-9"
            @input="fetchItems"
          />
        </div>
        <Select
          v-model="filters.category_id"
          @update:model-value="fetchItems"
        >
          <SelectTrigger class="w-[180px]">
            <SelectValue :placeholder="$t('common.labels.all') + ' ' + $t('common.labels.category')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="0">
              {{ $t('common.labels.all') }} {{ $t('common.labels.category') }}
            </SelectItem>
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
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-9"
          @click="dialogs.adjustment = true"
        >
          <LucideIcon
            name="ArrowDownUp"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.inventory.actions.updateStock') }}
        </Button>
        <Button
          size="sm"
          class="h-9 shadow-lg shadow-primary/20"
          @click="dialogs.item = true"
        >
          <LucideIcon
            name="Plus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('modules.school.logistics.inventory.actions.newItem') }}
        </Button>
      </div>
    </div>

    <!-- Inventory Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-left">
      <Card
        v-for="item in items"
        :key="item.id"
        class="border-border/50 relative overflow-hidden group"
      >
        <div
          v-if="item.quantity_on_hand <= item.minimum_stock"
          class="absolute top-0 right-0 p-1"
        >
          <Badge
            variant="destructive"
            class="text-[8px] animate-pulse"
          >
            {{ $t('modules.school.logistics.inventory.labels.lowStock') }}
          </Badge>
        </div>
        <CardHeader class="pb-2">
          <CardTitle class="text-sm font-semibold truncate">
            {{ item.name }}
          </CardTitle>
          <CardDescription class="text-[10px]">
            {{ item.sku }} • {{ item.category?.name }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex justify-between items-end">
            <div class="text-2xl font-semibold">
              {{ item.quantity_on_hand }} <span class="text-xs text-muted-foreground font-normal">{{ item.unit }}</span>
            </div>
            <div class="text-[10px] text-muted-foreground">
              {{ $t('modules.school.logistics.inventory.labels.minimumStock') }}: {{ item.minimum_stock }}
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Recent Transactions -->
    <Card class="border-border/50 text-left">
      <CardHeader>
        <CardTitle class="text-lg">
          {{ $t('modules.school.logistics.inventory.labels.lastLogisticsTransaction') }}
        </CardTitle>
      </CardHeader>
      <CardContent class="p-0">
        <DataTable
          :table="table"
          :loading="loading"
        />
      </CardContent>
    </Card>

    <!-- Dialogs -->
    <ItemDialog
      v-model:open="dialogs.item"
      :categories="categories"
      @save="fetchItems"
    />
    <StockAdjustmentDialog
      v-model:open="dialogs.adjustment"
      :items="items"
      @save="fetchItems"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import {
  Card, CardHeader, CardTitle, CardDescription, CardContent,
  Button, LucideIcon, Badge, DataTable, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import { createColumnHelper, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';

// Components
import ItemDialog from './components/ItemDialog.vue';
import StockAdjustmentDialog from './components/StockAdjustmentDialog.vue';

const { t, locale } = useI18n();
const toast = useToast();
const loading = ref(false);
const items = ref<any[]>([]);
const categories = ref<any[]>([]);
const transactions = ref<any[]>([]);
const filters = ref({ search: '', category_id: '0' });

const dialogs = ref({
   item: false,
   adjustment: false
});

const columnHelper = createColumnHelper<any>();
const columns = [
   columnHelper.accessor('created_at', { 
      header: t('modules.school.logistics.inventory.labels.time'), 
      cell: info => new Date(info.getValue()).toLocaleString(locale.value === 'id' ? 'id-ID' : 'en-US')
   }),
   columnHelper.accessor('item.name', { header: t('modules.school.logistics.inventory.labels.item') }),
   columnHelper.accessor('type', { 
      header: t('common.labels.type'),
      cell: info => h(Badge, { 
         variant: info.getValue() === 'in' ? 'outline' : (info.getValue() === 'out' ? 'destructive' : 'secondary'),
         class: 'text-[10px] font-semibold'
      }, info.getValue())
   }),
   columnHelper.accessor('quantity', { header: t('modules.school.logistics.inventory.labels.quantity') }),
   columnHelper.accessor('reference_number', { header: t('modules.school.logistics.inventory.labels.reference') }),
   columnHelper.accessor('notes', { header: t('common.labels.comment'), cell: info => h('span', { class: 'text-xs' }, info.getValue() || '-') })
];

const table = useVueTable({
   get data() { return transactions.value },
   get columns() { return columns },
   getCoreRowModel: getCoreRowModel()
});

const fetchCategories = async () => {
   try {
      const response = await LogisticsService.getInventoryCategories();
      categories.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   }
}

const fetchItems = async () => {
   try {
      const params: any = {};
      if (filters.value.search) params.search = filters.value.search;
      if (filters.value.category_id !== '0') params.category_id = filters.value.category_id;
      
      const response = await LogisticsService.getInventoryItems(params);
      items.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   }
}

const fetchTransactions = async () => {
   loading.value = true;
   try {
      const response = await LogisticsService.getInventoryTransactions();
      transactions.value = parseResponse(response).data;
   } catch (e) {
      toast.error.fromResponse(e);
   } finally {
      loading.value = false;
   }
}

onMounted(() => {
   fetchCategories();
   fetchItems();
   fetchTransactions();
});
</script>
