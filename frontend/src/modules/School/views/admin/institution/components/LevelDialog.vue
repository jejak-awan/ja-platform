<template>
  <Dialog
    :open="show"
    @update:open="$emit('update:show', $event)"
  >
    <DialogContent class="sm:max-w-[600px] rounded-2xl border-border/40 shadow-2xl p-0 overflow-hidden">
      <!-- Header -->
      <DialogHeader class="p-6 bg-muted/20 border-b border-border/30">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-primary/10 text-primary rounded-xl">
            <LucideIcon
              :name="editingId ? 'Edit3' : 'PlusCircle'"
              class="w-5 h-5"
            />
          </div>
          <div class="text-left">
            <DialogTitle class="text-xl font-bold">
              {{ editingId ? $t('features.school.levels.edit') : $t('features.school.levels.localForm.addTitle') }}
            </DialogTitle>
            <DialogDescription class="text-xs font-bold text-muted-foreground/60 mt-0.5">
              {{ editingId ? $t('common.actions.edit') : $t('common.actions.create') }}
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <!-- Form Content -->
      <div class="p-8 space-y-6 text-left">
        <!-- Level Type -->
        <div class="space-y-2">
          <Label>{{ $t('features.school.levels.localForm.type') }}</Label>
          <Select
            v-model="localForm.level"
            :disabled="isTypeLocked"
          >
            <SelectTrigger 
              class="h-12 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5"
              :class="{ 'opacity-70 cursor-not-allowed': isTypeLocked }"
            >
              <SelectValue :placeholder="$t('features.school.levels.localForm.typePlaceholder')" />
            </SelectTrigger>
            <SelectContent class="rounded-xl border-border/40 shadow-xl">
              <SelectItem value="sd">
                {{ $t('features.school.wizard.options.sd') }}
              </SelectItem>
              <SelectItem value="smp">
                {{ $t('features.school.wizard.options.smp') }}
              </SelectItem>
              <SelectItem value="sma">
                {{ $t('features.school.wizard.options.sma') }}
              </SelectItem>
              <SelectItem value="smk">
                {{ $t('features.school.wizard.options.smk') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p
            v-if="isTypeLocked"
            class="text-[10px] text-muted-foreground italic flex items-center gap-1 px-1"
          >
            <LucideIcon
              name="Lock"
              class="w-3 h-3"
            />
            {{ $t('features.school.hints.statusLocked') }}
          </p>
        </div>

        <!-- Level Name -->
        <div class="space-y-2">
          <Label>{{ $t('features.school.levels.localForm.name') }}</Label>
          <Input 
            v-model="localForm.name" 
            :placeholder="$t('features.school.levels.localForm.namePlaceholder')" 
            class="h-12 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-medium placeholder:font-normal"
          />
          <p class="text-[10px] text-muted-foreground italic mt-1 flex items-center gap-1">
            <LucideIcon
              name="Info"
              class="w-3 h-3"
            />
            {{ $t('features.school.hints.levelNameFormat') }}
          </p>
        </div>

        <!-- Level NPSN -->
        <div class="space-y-2">
          <Label>{{ $t('features.school.levels.localForm.npsn') }}</Label>
          <Input 
            v-model="localForm.npsn" 
            maxlength="8" 
            :placeholder="$t('features.school.levels.localForm.npsnPlaceholder')" 
            class="h-12 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-mono tracking-widest"
          />
          <p class="text-[10px] text-muted-foreground italic mt-1 flex items-center gap-1">
            <LucideIcon
              name="Info"
              class="w-3 h-3"
            />
            {{ $t('features.school.hints.npsnFormat') }}
          </p>
        </div>

        <!-- Level Accreditation -->
        <div class="space-y-2">
          <Label>{{ $t('common.labels.accreditation') }}</Label>
          <Select v-model="localForm.accreditation">
            <SelectTrigger class="h-12 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-bold">
              <SelectValue :placeholder="$t('common.placeholders.selectAccreditation')" />
            </SelectTrigger>
            <SelectContent class="rounded-xl border-border/40 shadow-xl">
              <SelectItem
                v-for="(label, key) in $tm('common.labels.accreditationOptions')"
                :key="key"
                :value="String(key)"
              >
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Branch Location & Contact (Conditional) -->
        <div
          v-if="isMultiBranch"
          class="space-y-6 pt-6 border-t border-border/40"
        >
          <div class="flex items-center justify-between p-4 bg-primary/5 rounded-2xl border border-primary/10">
            <div class="space-y-1">
              <h4 class="font-bold text-sm text-primary">
                {{ $t('common.labels.locationUnit') }}
              </h4>
              <p class="text-[10px] text-muted-foreground">
                {{ $t('common.labels.locationUnitDesc') }}
              </p>
            </div>
            <div class="flex items-center gap-3">
              <span
                class="text-[10px] font-bold"
                :class="localForm.settings?.use_primary_address ? 'text-primary' : 'text-muted-foreground'"
              >
                {{ $t('common.usePrimaryAddress') }}
              </span>
              <Switch 
                :checked="localForm.settings?.use_primary_address" 
                @update:checked="val => {
                  if (!localForm.settings) localForm.settings = {};
                  localForm.settings.use_primary_address = val;
                }"
              />
            </div>
          </div>

          <!-- Extended Address Fields (if not using primary) -->
          <div
            v-if="localForm.settings && !localForm.settings.use_primary_address"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 animate-in fade-in slide-in-from-top-2 duration-300"
          >
            <div class="space-y-2 md:col-span-2">
              <Label>{{ $t('common.labels.addressUnit') }}</Label>
              <Textarea
                v-model="localForm.settings.address"
                class="rounded-xl border-border/50 min-h-[60px]"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.city') }}</Label>
              <Input
                v-model="localForm.settings.kabupaten_kota"
                class="rounded-xl h-10 border-border/50 bg-muted/5"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.phoneNumber') }}</Label>
              <Input
                v-model="localForm.settings.phone"
                class="rounded-xl h-10 border-border/50 bg-muted/5"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DialogFooter class="p-6 bg-muted/10 border-t border-border/30 gap-3">
        <Button
          variant="outline"
          class="rounded-xl px-6 border-border/50 hover:bg-background"
          @click="$emit('update:show', false)"
        >
          {{ $t('common.actions.cancel') }}
        </Button>
        <Button
          class="rounded-xl px-8 bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95 font-bold"
          @click="$emit('save')"
        >
          {{ editingId ? $t('common.actions.saveChanges') : $t('common.actions.add') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import {
  Button, LucideIcon, Input, Label,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Switch, Textarea
} from '@/components/ui';

// Props passed from the parent component (InstitutionTabs)
const props = defineProps<{
  show: boolean;
  editingId: number | null;
  form: any;
  isMultiBranch?: boolean;
  schoolType?: string;
}>();

const localForm = reactive({ ...props.form });

watch(() => props.form, (newVal) => {
  Object.assign(localForm, newVal);
}, { deep: true });

watch(localForm, (newVal) => {
  emit('update:form', { ...newVal });
}, { deep: true });

const isTypeLocked = computed(() => {
  return props.schoolType === 'negeri' || props.schoolType === 'swasta';
});

const emit = defineEmits<{
  (e: 'update:show', value: boolean): void;
  (e: 'update:form', value: any): void;
  (e: 'save'): void;
}>();
</script>
"
,Complexity:5,Description:
