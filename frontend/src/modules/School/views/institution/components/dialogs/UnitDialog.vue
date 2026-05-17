<template>
  <Dialog
    :open="show"
    @update:open="$emit('update:show', $event)"
  >
    <DialogContent class="sm:max-w-[600px] max-h-[90vh] md:max-h-[85vh] rounded-2xl border-border/40 shadow-2xl p-0 overflow-hidden flex flex-col">
      <!-- Header -->
      <DialogHeader class="p-6 bg-muted/20 border-b border-border/30 shrink-0">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-primary/10 text-primary rounded-xl">
            <LucideIcon
              :name="editingId ? 'Edit3' : 'PlusCircle'"
              class="w-5 h-5"
            />
          </div>
          <div class="text-left">
            <DialogTitle class="text-xl font-bold">
              {{ editingId ? $t('modules.school.units.edit') : (isMultiLevel ? $t('modules.school.units.form.addTitle') : $t('modules.school.units.form.addTitleLevel')) }}
            </DialogTitle>
            <DialogDescription class="text-xs font-bold text-muted-foreground/60 mt-0.5">
              {{ editingId ? $t('common.actions.edit') : $t('common.actions.create') }}
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <!-- Form Content -->
      <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 text-left custom-scrollbar">
        <!-- Single-level info banner -->
        <div
          v-if="isSingleLevel"
          class="p-4 bg-primary/5 rounded-2xl border border-primary/10 flex items-start gap-3"
        >
          <LucideIcon name="Info" class="w-5 h-5 text-primary shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-bold text-primary">{{ schoolName }}</p>
            <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
              Jenjang akan otomatis menggunakan nama dan tipe dari sekolah utama Anda. Silakan konfirmasi NPSN dan Akreditasi.
            </p>
          </div>
        </div>

        <!-- Primary Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Level Type -->
          <div class="space-y-2">
            <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('modules.school.units.form.type') }}</Label>
            <Select
              v-model="localForm.level"
              :disabled="isSingleLevel"
            >
              <SelectTrigger 
                class="h-11 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5"
                :class="{ 'opacity-70 cursor-not-allowed': isSingleLevel }"
              >
                <SelectValue :placeholder="$t('modules.school.units.form.typePlaceholder')" />
              </SelectTrigger>
              <SelectContent class="rounded-xl border-border/40 shadow-xl">
                <SelectItem value="sd">{{ $t('modules.school.wizard.options.sd') }}</SelectItem>
                <SelectItem value="smp">{{ $t('modules.school.wizard.options.smp') }}</SelectItem>
                <SelectItem value="sma">{{ $t('modules.school.wizard.options.sma') }}</SelectItem>
                <SelectItem value="smk">{{ $t('modules.school.wizard.options.smk') }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Level NPSN -->
          <div class="space-y-2">
            <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('modules.school.units.form.npsn') }}</Label>
            <Input 
              v-model="localForm.npsn" 
              maxlength="8" 
              :placeholder="$t('modules.school.units.form.npsnPlaceholder')" 
              class="h-11 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-mono tracking-widest"
            />
          </div>

          <!-- Level Name (only for multi-level / yayasan) -->
          <div
            v-if="!isSingleLevel"
            class="space-y-2 md:col-span-2"
          >
            <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('modules.school.units.form.name') }}</Label>
            <Input 
              v-model="localForm.name" 
              :placeholder="$t('modules.school.units.form.namePlaceholder')" 
              class="h-11 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-medium placeholder:font-normal"
            />
            <p class="text-[10px] text-muted-foreground italic flex items-center gap-1 opacity-70">
              <LucideIcon name="Info" class="w-3 h-3" />
              {{ $t('modules.school.hints.levelNameFormat') }}
            </p>
          </div>

          <!-- Level Accreditation -->
          <div class="space-y-2 md:col-span-2">
            <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('modules.school.labels.accreditation') }}</Label>
            <Select v-model="localForm.accreditation">
              <SelectTrigger class="h-11 rounded-xl border-border/50 focus:ring-primary/20 bg-muted/5 font-bold">
                <SelectValue :placeholder="$t('common.placeholders.selectAccreditation')" />
              </SelectTrigger>
              <SelectContent class="rounded-xl border-border/40 shadow-xl">
                <SelectItem
                  v-for="(label, key) in $tm('modules.school.labels.accreditationOptions')"
                  :key="key"
                  :value="String(key)"
                >
                  {{ label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <DialogFooter class="p-6 bg-muted/10 border-t border-border/30 gap-3 shrink-0">
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
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';

// Props passed from the parent component (InstitutionTabs)
const props = defineProps<{
  show: boolean;
  editingId: string | null;
  form: any;
  isMultiBranch?: boolean;
  isMultiLevel?: boolean;
  schoolType?: string;
  schoolName?: string;
}>();

const localForm = reactive({ ...props.form });

const isSingleLevel = computed(() => {
  return !props.isMultiLevel && (props.schoolType === 'negeri' || props.schoolType === 'swasta');
});

/**
 * Auto-detect level type from school name for single-level schools.
 */
const detectLevelFromName = (name: string): string | null => {
  const upper = name.trim().toUpperCase();
  if (upper.startsWith('SD')) return 'sd';
  if (upper.startsWith('SMP') || upper.startsWith('MTS')) return 'smp';
  if (upper.startsWith('SMA') || upper.startsWith('MA ')) return 'sma';
  if (upper.startsWith('SMK')) return 'smk';
  return null;
};

// Synchronization logic: only sync from props to localForm when dialog opens
watch(() => props.show, (isOpen) => {
  if (isOpen && props.form) {
    // Completely reset localForm to prevent stale keys
    Object.keys(localForm).forEach(key => delete (localForm as any)[key]);
    
    // Deep clone to break reference
    const freshData = JSON.parse(JSON.stringify(props.form));
    Object.assign(localForm, freshData);
    
    // Flatten accreditation for the select UI
    if (freshData.settings?.accreditation) {
      (localForm as any).accreditation = freshData.settings.accreditation;
    }

    // Auto-detect for single-level create mode
    if (isSingleLevel.value && !props.editingId && props.schoolName) {
      localForm.name = props.schoolName;
      const detected = detectLevelFromName(props.schoolName);
      if (detected) localForm.level = detected;
    }
  }
});

// Emit changes back to parent, but avoid recursive prop watching
watch(localForm, (newVal) => {
  if (!props.show) return; // Only emit if dialog is active
  
  const updatedForm = JSON.parse(JSON.stringify(newVal));
  if (newVal.accreditation) {
    updatedForm.settings = {
      ...(updatedForm.settings || {}),
      accreditation: newVal.accreditation
    };
  }
  emit('update:form', updatedForm);
}, { deep: true });

// Auto-detect level type from name prefix (for multi-level / yayasan)
watch(() => localForm.name, (newName) => {
  if (isSingleLevel.value || props.editingId) return;
  const detected = detectLevelFromName(newName);
  if (detected) localForm.level = detected;
});

const emit = defineEmits<{
  (e: 'update:show', value: boolean): void;
  (e: 'update:form', value: any): void;
  (e: 'save'): void;
}>();
</script>
"
,Complexity:5,Description:
