<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('modules.school.logistics.sarpras.actions.updateMaintenance') : $t('modules.school.logistics.sarpras.actions.createMaintenance') }}</DialogTitle>
        <DialogDescription>
          {{ $t('modules.school.logistics.sarpras.descriptions.maintenance') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4">
        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('modules.school.logistics.sarpras.tabs.asset') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.school_asset_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectAsset')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="asset in assets"
                :key="asset.id"
                :value="asset.id.toString()"
              >
                {{ asset.name }} ({{ asset.code || 'No Code' }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('modules.school.logistics.sarpras.labels.reporter') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.reported_by"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectStaff')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="s in staff"
                :key="s.id"
                :value="s.id.toString()"
              >
                {{ s.full_name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label for="date">{{ $t('common.labels.date') }} <span class="text-destructive">*</span></Label>
          <Input
            id="date"
            v-model="form.date_reported"
            type="date"
            required
          />
        </div>

        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label for="desc">{{ $t('modules.school.logistics.sarpras.labels.issueDescription') }} <span class="text-destructive">*</span></Label>
          <Textarea
            id="desc"
            v-model="form.issue_description"
            required
            :placeholder="$t('modules.school.logistics.sarpras.placeholders.issueDescriptionHint')"
          />
        </div>

        <div class="grid gap-2">
          <Label>{{ $t('modules.school.logistics.sarpras.labels.priority') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.priority"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectPriority')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="low">
                {{ $t('common.labels.priorities.low') }}
              </SelectItem>
              <SelectItem value="medium">
                {{ $t('common.labels.priorities.medium') }}
              </SelectItem>
              <SelectItem value="high">
                {{ $t('common.labels.priorities.high') }}
              </SelectItem>
              <SelectItem value="urgent">
                {{ $t('common.labels.priorities.urgent') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('common.labels.status') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.status"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.logistics.sarpras.placeholders.selectStatus')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="open">
                {{ $t('common.labels.maintenanceStatus.open') }}
              </SelectItem>
              <SelectItem value="inProgress">
                {{ $t('common.labels.maintenanceStatus.inProgress') }}
              </SelectItem>
              <SelectItem value="resolved">
                {{ $t('common.labels.maintenanceStatus.resolved') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div
          v-if="isEdit"
          class="grid gap-2"
        >
          <Label for="notes">{{ $t('modules.school.logistics.sarpras.labels.resolutionNotes') }}</Label>
          <Textarea
            id="notes"
            v-model="form.resolution_notes"
            :placeholder="$t('modules.school.logistics.sarpras.placeholders.resolutionNotesHint')"
          />
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="ghost"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.labels.cancel') }}
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
          {{ isEdit ? $t('common.labels.save') : $t('modules.school.logistics.sarpras.actions.sendReport') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, 
  LucideIcon, Textarea
} from '@/shared/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { HRService } from '@/modules/School/services/HRService';
import { parseResponse } from '@/shared/utils/responseParser';

interface SchoolAsset {
  id: number | string;
  name: string;
  code?: string;
}

interface StaffMember {
  id: number | string;
  full_name: string;
}

const props = defineProps<{
  open: boolean;
  isEdit: boolean;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const assets = ref<SchoolAsset[]>([]);
const staff = ref<StaffMember[]>([]);

const form = ref<any>({
  school_asset_id: '',
  reported_by: '',
  date_reported: new Date().toISOString().split('T')[0],
  issue_description: '',
  priority: 'medium',
  status: 'open',
  resolution_notes: '',
});

const fetchAssets = async () => {
    try {
        const response = await LogisticsService.getAssets();
        assets.value = parseResponse<SchoolAsset>(response).data;
    } catch {
        // Silent fail for background fetch
    }
}

const fetchStaff = async () => {
    try {
        const response = await HRService.getStaff();
        staff.value = parseResponse<StaffMember>(response).data;
    } catch {
        // Silent fail for background fetch
    }
}

watch(() => props.open, (newVal) => {
  if (newVal) {
    if (props.initialData) {
      form.value = { 
          ...props.initialData,
          school_asset_id: props.initialData.school_asset_id?.toString(),
          reported_by: props.initialData.reported_by?.toString(),
          priority: props.initialData.priority?.toLowerCase() || 'medium',
          status: props.initialData.status === 'Open' ? 'open' :
                  props.initialData.status === 'In Progress' ? 'inProgress' :
                  props.initialData.status === 'Resolved' ? 'resolved' :
                  (props.initialData.status?.toLowerCase() || 'open')
      };
    } else {
      form.value = {
        school_asset_id: '',
        reported_by: '',
        date_reported: new Date().toISOString().split('T')[0],
        issue_description: '',
        priority: 'medium',
        status: 'open',
        resolution_notes: '',
      };
      if (assets.value.length === 0) fetchAssets();
      if (staff.value.length === 0) fetchStaff();
    }
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(() => {
    fetchAssets();
    fetchStaff();
});
</script>
