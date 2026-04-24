<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('features.school.logistics.sarpras.actions.editRoom') : $t('features.school.logistics.sarpras.actions.addRoom') }}</DialogTitle>
      </DialogHeader>
      <form
        class="space-y-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="space-y-2">
          <Label>{{ $t('features.school.logistics.sarpras.tabs.building') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.building_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('features.school.logistics.sarpras.placeholders.selectBuilding')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="b in buildings"
                :key="b.id"
                :value="b.id"
              >
                {{ b.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="space-y-2">
          <Label for="name">{{ $t('features.school.logistics.sarpras.labels.roomName') }} <span class="text-destructive">*</span></Label>
          <Input
            id="name"
            v-model="form.name"
            :placeholder="$t('features.school.logistics.sarpras.placeholders.roomNameHint')"
            required
          />
        </div>
        <div class="space-y-2">
          <Label>{{ $t('features.school.logistics.sarpras.labels.roomType') }}</Label>
          <Select v-model="form.type">
            <SelectTrigger><SelectValue :placeholder="$t('features.school.placeholders.select')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="classroom">
                {{ $t('common.labels.roomTypes.classroom') }}
              </SelectItem>
              <SelectItem value="laboratory">
                {{ $t('common.labels.roomTypes.laboratory') }}
              </SelectItem>
              <SelectItem value="library">
                {{ $t('common.labels.roomTypes.library') }}
              </SelectItem>
              <SelectItem value="teacherRoom">
                {{ $t('common.labels.roomTypes.teacherRoom') }}
              </SelectItem>
              <SelectItem value="other">
                {{ $t('common.labels.other') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label for="capacity">{{ $t('academic.labels.capacity') || 'Kapasitas' }}</Label>
            <Input
              id="capacity"
              v-model="form.capacity"
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

const buildings = ref<any[]>([]);
const form = ref({
  building_id: undefined as string | undefined,
  name: '',
  type: 'classroom',
  capacity: 36,
  condition: 'good',
});

watch(() => props.initialData, (val) => {
  if (val) {
    form.value = { 
      ...val,
      type: val.type === 'Ruang Kelas' ? 'classroom' :
            val.type === 'Laboratorium' ? 'laboratory' :
            val.type === 'Perpustakaan' ? 'library' :
            val.type === 'Ruang Guru' ? 'teacherRoom' :
            (val.type?.toLowerCase() || 'classroom'),
      condition: val.condition === 'Baik' ? 'good' : 
                 val.condition === 'Rusak Ringan' ? 'lightDamage' : 
                 val.condition === 'Rusak Berat' ? 'heavyDamage' : 
                 (val.condition?.toLowerCase() || 'good')
    };
    if (form.value.building_id) form.value.building_id = String(form.value.building_id);
  } else {
    form.value = { building_id: undefined, name: '', type: 'classroom', capacity: 36, condition: 'good' };
  }
}, { immediate: true });

const fetchMetadata = async () => {
    try {
        const response = await LogisticsService.getBuildings();
        buildings.value = parseResponse(response).data;
    } catch (e) {
        console.error(e);
    }
}

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(fetchMetadata);
</script>
