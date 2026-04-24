<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.logistics.sarpras.actions.editBuilding') : $t('features.school.logistics.sarpras.actions.addBuilding') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.logistics.sarpras.labels.location') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.land_asset_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.logistics.sarpras.placeholders.selectLand')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="l in lands"
                :key="l.id"
                :value="l.id"
              >
                {{ l.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="name">{{ $t('features.school.logistics.sarpras.labels.buildingName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            required
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="area">{{ $t('features.school.logistics.sarpras.labels.area') }} (m2)</Label>
            <Input
              id="area"
              v-model="form.area"
              type="number"
            />
          </div>
          <div class="space-y-2">
            <Label for="floor_count">{{ $t('features.school.logistics.sarpras.labels.floorCount') }}</Label>
            <Input
              id="floor_count"
              v-model="form.floor_count"
              type="number"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="year_built">{{ $t('features.school.logistics.sarpras.labels.yearBuilt') }}</Label>
            <Input
              id="year_built"
              v-model="form.year_built"
              type="number"
            />
          </div>
          <div class="space-y-2">
            <Label>{{ $t('features.school.logistics.sarpras.labels.condition') }}</Label>
            <Select v-model="form.condition">
              <SelectTrigger><SelectValue :placeholder="$t('features.school.logistics.sarpras.placeholders.selectCondition')" /></SelectTrigger>
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
} from '@/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{
  open: boolean;
  isEdit?: boolean;
  initialData?: any;
  loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const lands = ref<any[]>([]);
const form = ref({
  land_asset_id: undefined as string | undefined,
  name: '',
  area: 0,
  floor_count: 1,
  year_built: new Date().getFullYear(),
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
    if (form.value.land_asset_id) form.value.land_asset_id = String(form.value.land_asset_id);
  } else {
    form.value = { land_asset_id: undefined, name: '', area: 0, floor_count: 1, year_built: new Date().getFullYear(), condition: 'good' };
  }
}, { immediate: true });

const fetchMetadata = async () => {
    try {
        const response = await LogisticsService.getLands();
        lands.value = parseResponse(response).data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
    // Map back to original Indonesian values if required by API, 
    // but usually backend should handle these tokens or we should standardize.
    // For now, I'll send the tokens.
    emit('submit', form.value);
};

onMounted(fetchMetadata);
</script>
