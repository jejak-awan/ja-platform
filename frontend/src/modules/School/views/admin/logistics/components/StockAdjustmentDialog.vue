<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ $t('features.school.logistics.inventory.actions.updateStock') }}</DialogTitle>
        <DialogDescription>Input mutasi stok barang (Masuk/Keluar/Penyesuaian).</DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label>{{ $t('features.school.logistics.inventory.labels.item') }}</Label>
          <Select v-model="form.item_id">
            <SelectTrigger><SelectValue :placeholder="$t('common.actions.select')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="item in items"
                :key="item.id"
                :value="String(item.id)"
              >
                {{ item.name }} ({{ item.quantity_on_hand }} {{ item.unit }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="grid gap-2">
            <Label>{{ $t('common.labels.type') }}</Label>
            <Select v-model="form.type">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="in">
                  Barang Masuk (+)
                </SelectItem>
                <SelectItem value="out">
                  Barang Keluar (-)
                </SelectItem>
                <SelectItem value="adjustment">
                  Penyesuaian
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label>{{ $t('features.school.logistics.inventory.labels.quantity') }}</Label>
            <Input
              v-model="form.quantity"
              type="number"
              min="1"
            />
          </div>
        </div>
        <div class="grid gap-2">
          <Label for="notes">{{ $t('common.labels.description') }} / {{ $t('features.school.logistics.inventory.labels.reference') }}</Label>
          <Textarea
            id="notes"
            v-model="form.notes"
            :placeholder="$t('features.school.academic.placeholders.notesHint')"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :loading="loading"
          :disabled="!form.item_id"
          @click="handleSubmit"
        >
          {{ $t('features.school.logistics.inventory.actions.updateStock') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea
} from '@/components/ui';
import api from '@/services/api';
import { useToast } from '@/composables/useToast';

defineProps<{ 
   open: boolean;
   items: Record<string, any>[];
}>();

const { t } = useI18n();
const emit = defineEmits(['update:open', 'save']);
const toast = useToast();
const loading = ref(false);

const form = ref({
   item_id: '',
   type: 'in',
   quantity: 1,
   notes: '',
});

const handleSubmit = async () => {
   if (!form.value.item_id) return;

   loading.value = true;
   try {
      await api.post('/admin/logistics/inventory/adjust', form.value);
      toast.success.action(t('features.school.academic.messages.updateSuccess'));
      emit('save');
      emit('update:open', false);
      form.value = { item_id: '', type: 'in', quantity: 1, notes: '' };
   } catch (e) {
      toast.error.fromResponse(e);
   } finally {
      loading.value = false;
   }
};
</script>
