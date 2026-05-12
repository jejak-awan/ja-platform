<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('modules.school.logistics.sarpras.actions.editAsset') : $t('modules.school.logistics.sarpras.actions.addAsset') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('modules.school.logistics.sarpras.placeholders.selectRoom') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.room_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectRoom')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="r in rooms"
                :key="r.id"
                :value="r.id"
              >
                {{ r.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="name">{{ $t('modules.school.logistics.sarpras.labels.assetName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="code">{{ $t('modules.school.logistics.sarpras.labels.assetCode') }}</Label>
            <Input
              id="code"
              v-model="form.code"
            />
          </div>
          <div class="space-y-2">
            <Label for="quantity">{{ $t('common.labels.count') }}</Label>
            <Input
              id="quantity"
              v-model="form.quantity"
              type="number"
              min="0"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>{{ $t('common.labels.category') }}</Label>
            <Input
              id="category"
              v-model="form.category"
              :placeholder="$t('common.labels.other')"
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('modules.school.logistics.sarpras.labels.condition') }}</Label>
            <Select v-model="form.condition">
              <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectCondition')" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="good">
                  {{ $t('common.labels.conditions.good') }}
                </SelectItem>
                <SelectItem value="lightDamage">
                  {{ $t('common.labels.conditions.lightDamage') }}
                </SelectItem>
                <SelectItem value="heavyDamage">
                  {{ $t('common.labels.conditions.heavyDamage') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        <DialogFooter>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ $t('common.labels.save') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
  Button, Label, Input, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { parseResponse } from '@/shared/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const rooms = ref<any[]>([]);
const form = ref({
  room_id: undefined as string | undefined,
  name: '',
  code: '',
  category: '',
  quantity: 1,
  condition: 'good',
});

watch(() => props.initialData, (val) => {
  if (val) {
    form.value = { 
      ...val,
      condition: val.condition === 'Baik' ? 'good' : 
                 val.condition === 'Rusak Ringan' ? 'lightDamage' : 
                 val.condition === 'Rusak Berat' ? 'heavyDamage' : 
                 (val.condition?.toLowerCase() || 'good')
    };
    if (form.value.room_id) form.value.room_id = String(form.value.room_id);
  } else {
    form.value = { room_id: undefined, name: '', code: '', category: '', quantity: 1, condition: 'good' };
  }
}, { immediate: true });

const fetchMetadata = async () => {
    try {
        const response = await LogisticsService.getRooms();
        rooms.value = parseResponse(response).data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(fetchMetadata);
</script>
