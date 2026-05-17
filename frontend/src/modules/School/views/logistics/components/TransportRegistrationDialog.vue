<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[425px] rounded-2xl overflow-hidden border-border/50">
      <DialogHeader>
        <DialogTitle class="text-xl font-bold tracking-tight">{{ $t('modules.school.logistics.transport.actions.registerTransport') }}</DialogTitle>
        <DialogDescription class="text-sm italic font-medium">{{ $t('modules.school.logistics.transport.labels.studentListDesc') }}</DialogDescription>
      </DialogHeader>

      <div class="grid gap-6 py-6">
        <div class="grid gap-2">
          <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('modules.school.logistics.transport.placeholders.searchStudent') }}</Label>
          <div class="relative">
            <LucideIcon
              name="Search"
              class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
            />
            <Input
              v-model="search"
              :placeholder="$t('modules.school.logistics.transport.placeholders.searchHint')"
              class="pl-10 h-11 rounded-xl bg-muted/20 border-border/50"
              @input="fetchStudents"
            />
            <div
              v-if="searching"
              class="absolute right-3 top-2.5"
            >
              <LucideIcon
                name="Loader2"
                class="w-4 h-4 animate-spin text-primary"
              />
            </div>
          </div>
          <div
            v-if="students.length > 0"
            class="mt-2 max-h-[150px] overflow-y-auto border border-border/50 rounded-xl divide-y divide-border/30 bg-muted/10"
          >
            <div 
              v-for="s in students" 
              :key="s.id" 
              class="p-3 hover:bg-primary/5 cursor-pointer text-xs flex justify-between items-center transition-colors"
              :class="{ 'bg-primary/10 border-l-2 border-primary': form.student_id === s.id }"
              @click="form.student_id = s.id"
            >
              <div class="flex flex-col">
                <span class="font-bold">{{ s.full_name }}</span>
                <span class="text-[10px] text-muted-foreground uppercase tracking-widest">{{ s.nis }}</span>
              </div>
              <LucideIcon v-if="form.student_id === s.id" name="Check" class="w-4 h-4 text-primary" />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('modules.school.logistics.transport.labels.vehicle') }}</Label>
            <Select v-model="form.vehicle_id">
              <SelectTrigger class="h-11 rounded-xl bg-muted/20 border-border/50"><SelectValue :placeholder="$t('modules.school.logistics.transport.placeholders.selectFleet')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="v in vehicles"
                  :key="v.id"
                  :value="String(v.id)"
                >
                  {{ v.plate_number }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="grid gap-2">
            <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('modules.school.logistics.transport.labels.route') }}</Label>
            <Select v-model="form.route_id">
              <SelectTrigger class="h-11 rounded-xl bg-muted/20 border-border/50"><SelectValue :placeholder="$t('modules.school.logistics.transport.placeholders.selectRoute')" /></SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="r in routes"
                  :key="r.id"
                  :value="String(r.id)"
                >
                  {{ r.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="grid gap-2">
          <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('modules.school.logistics.transport.labels.pickupPoint') }}</Label>
          <Input
            id="pickup"
            v-model="form.pickup_point"
            :placeholder="$t('modules.school.logistics.transport.placeholders.pickupHint')"
            class="h-11 rounded-xl bg-muted/20 border-border/50"
          />
        </div>

        <div class="grid gap-2">
          <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $t('modules.school.logistics.transport.labels.startService') }}</Label>
          <Input
            id="start"
            v-model="form.start_date"
            type="date"
            class="h-11 rounded-xl bg-muted/20 border-border/50"
          />
        </div>
      </div>

      <DialogFooter class="bg-muted/30 p-4 border-t border-border/40">
        <Button
          variant="ghost"
          class="rounded-xl font-bold"
          @click="$emit('update:open', false)"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          :loading="loading"
          :disabled="!form.student_id"
          class="rounded-xl font-bold shadow-md transition-all active:scale-95"
          @click="handleSubmit"
        >
          <LucideIcon name="CircleCheck2" class="w-4 h-4 mr-2" />
          {{ $t('common.actions.confirm') }}
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
  Button, Input, Label, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, LucideIcon
} from '@/shared/components/ui';
import { LogisticsService } from '@/modules/School/services/LogisticsService';
import { StudentService } from '@/modules/School/services/StudentService';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse } from '@/shared/utils/responseParser';

const { t } = useI18n();
const toast = useToast();

defineProps<{ 
   open: boolean;
   vehicles: any[];
   routes: any[];
}>();

const emit = defineEmits(['update:open', 'save']);
const loading = ref(false);
const searching = ref(false);
const search = ref('');
const students = ref<any[]>([]);

const form = ref({
   student_id: null as number | null,
   vehicle_id: '',
   route_id: '',
   pickup_point: '',
   start_date: new Date().toISOString().split('T')[0]
});

const fetchStudents = async () => {
   if (search.value.length < 2) return;
   searching.value = true;
   try {
      const response = await StudentService.searchStudents(search.value);
      students.value = parseResponse(response).data;
   } catch {
      // Ignored
   } finally {
      searching.value = false;
   }
}

const handleSubmit = async () => {
   if (!form.value.student_id || !form.value.vehicle_id || !form.value.route_id) {
      return toast.error.action(t('modules.school.logistics.transport.messages.fillAll'));
   }

   loading.value = true;
   try {
      await LogisticsService.registerTransport(form.value);
      toast.success.action(t('modules.school.logistics.transport.messages.saveSuccess'));
      emit('save');
      emit('update:open', false);
      form.value = { student_id: null, vehicle_id: '', route_id: '', pickup_point: '', start_date: new Date().toISOString().split('T')[0] };
   } catch (_e) {
      toast.error.fromResponse(_e);
   } finally {
      loading.value = false;
   }
};
</script>
