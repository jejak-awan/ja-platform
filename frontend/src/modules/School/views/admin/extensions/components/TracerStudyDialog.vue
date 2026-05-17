<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? $t('modules.school.extensions.messages.saveSuccess') : $t('modules.school.extensions.tabs.tracer') }}</DialogTitle>
        <DialogDescription>
          {{ $t('modules.school.extensions.subtitle') }}
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 py-4 max-h-[70vh] overflow-y-auto pr-2">
        <div
          v-if="!isEdit"
          class="grid gap-2"
        >
          <Label>{{ $t('modules.school.extensions.tabs.alumni') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.alumni_id"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.extensions.placeholders.selectAlumni')" /></SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="a in graduates"
                :key="a.id"
                :value="a.id.toString()"
              >
                {{ a.student?.full_name }} ({{ $t('common.labels.graduated') }}: {{ a.graduation_year }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label>{{ $t('modules.school.extensions.labels.employmentStatus') }} <span class="text-destructive">*</span></Label>
          <Select
            v-model="form.employment_status"
            required
          >
            <SelectTrigger><SelectValue :placeholder="$t('modules.school.extensions.placeholders.selectStatus')" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="Working">
                {{ $t('common.labels.working') || 'Working' }}
              </SelectItem>
              <SelectItem value="Studying">
                {{ $t('common.labels.studying') || 'Studying' }}
              </SelectItem>
              <SelectItem value="Entrepreneur">
                {{ $t('common.labels.entrepreneur') || 'Entrepreneur' }}
              </SelectItem>
              <SelectItem value="Seeking Job">
                {{ $t('common.labels.seekingJob') || 'Seeking Job' }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Career Details -->
        <template v-if="form.employment_status === 'Working' || form.employment_status === 'Entrepreneur'">
          <div class="grid gap-2">
            <Label for="company">{{ form.employment_status === 'Working' ? $t('modules.school.extensions.labels.companyName') : $t('modules.school.extensions.labels.companyName') }}</Label>
            <Input
              id="company"
              v-model="form.company_name"
            />
          </div>
          <div class="grid gap-2">
            <Label for="pos">{{ $t('common.labels.position') || 'Position' }}</Label>
            <Input
              id="pos"
              v-model="form.position"
            />
          </div>
          <div class="grid gap-2">
            <Label>{{ $t('modules.school.extensions.labels.salaryRange') }}</Label>
            <Select v-model="form.salary_range">
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="< 2jt">
                  &lt; 2 {{ $t('common.labels.million') || 'Million' }}
                </SelectItem>
                <SelectItem value="2jt - 5jt">
                  2 - 5 {{ $t('common.labels.million') || 'Million' }}
                </SelectItem>
                <SelectItem value="> 5jt">
                  &gt; 5 {{ $t('common.labels.million') || 'Million' }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </template>

        <!-- Education Details -->
        <template v-if="form.employment_status === 'Studying'">
          <div class="grid gap-2">
            <Label for="uni">{{ $t('modules.school.extensions.labels.universityName') }}</Label>
            <Input
              id="uni"
              v-model="form.university_name"
            />
          </div>
          <div class="grid gap-2">
            <Label for="major">{{ $t('modules.school.extensions.labels.major') }}</Label>
            <Input
              id="major"
              v-model="form.major"
            />
          </div>
        </template>

        <div class="grid gap-2">
          <Label for="start">{{ $t('common.labels.date') }}</Label>
          <Input
            id="start"
            v-model="form.start_date"
            type="date"
          />
        </div>

        <div class="flex items-center gap-3 mt-2">
          <input
            id="relevant"
            v-model="form.is_relevant_to_major"
            type="checkbox"
            class="w-4 h-4 cursor-pointer"
          >
          <Label
            for="relevant"
            class="cursor-pointer"
          >{{ $t('modules.school.extensions.labels.relevantToMajor') }}</Label>
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
          {{ isEdit ? $t('common.labels.save') : $t('common.labels.save') }}
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
  LucideIcon
} from '@/shared/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { parseResponse } from '@/shared/utils/responseParser';

interface Graduate {
  id: string | string;
  graduation_year: string;
  student?: {
    full_name: string;
  };
}

const props = defineProps<{
  open: boolean;
  isEdit: boolean;
  initialData: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:open', 'submit']);

const graduates = ref<Graduate[]>([]);

const form = ref<any>({
  alumni_id: '',
  employment_status: 'Working',
  company_name: '',
  position: '',
  university_name: '',
  major: '',
  salary_range: '2jt - 5jt',
  start_date: new Date().toISOString().split('T')[0],
  is_relevant_to_major: true,
});

const fetchGraduates = async () => {
    try {
        const response = await OperationsService.getAlumni();
        graduates.value = parseResponse<Graduate>(response).data;
    } catch {
        // Silent fail for background fetch
    }
}

watch(() => props.open, (newVal) => {
  if (newVal) {
    if (props.initialData) {
      form.value = { 
          ...props.initialData,
          alumni_id: props.initialData.alumni_id?.toString(),
          start_date: props.initialData.start_date ? new Date(props.initialData.start_date).toISOString().split('T')[0] : '',
      };
    } else {
      form.value = {
        alumni_id: '',
        employment_status: 'Working',
        company_name: '',
        position: '',
        university_name: '',
        major: '',
        salary_range: '2jt - 5jt',
        start_date: new Date().toISOString().split('T')[0],
        is_relevant_to_major: true,
      };
      if (graduates.value.length === 0) fetchGraduates();
    }
  }
});

const handleSubmit = () => {
  emit('submit', form.value);
};

onMounted(() => {
    fetchGraduates();
});
</script>
