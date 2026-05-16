<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10 text-left p-8">
    <!-- Foundation Details: Primary in Global Mode, Secondary/Collapsed in Unit Mode -->
    <div
      v-if="isFoundationType"
      class="md:col-span-2 space-y-6 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border shadow-none">
            <LucideIcon :name="unitStore.activeUnitId === 0 ? 'FileCheck' : 'Building'" class="w-5 h-5" />
          </div>
          <div>
            <h4 class="font-bold text-base text-foreground tracking-tight">
              {{ unitStore.activeUnitId === 0 ? $t('common.labels.foundationLegality') : $t('common.labels.foundationInfo') }}
            </h4>
            <p class="text-xs text-muted-foreground mt-0.5 font-medium">
              {{ unitStore.activeUnitId === 0 ? $t('common.labels.foundationLegalityDesc') : $t('common.labels.foundationInfoDesc') }}
            </p>
          </div>
        </div>
        <Button
          v-if="!isRestricted && unitStore.activeUnitId === 0"
          variant="outline"
          size="sm"
          class="rounded-xl font-bold h-9 px-4 border-border hover:bg-muted"
          @click="$emit('edit-foundation')"
        >
          <LucideIcon :name="hasFoundationData ? 'Pencil' : 'Plus'" class="w-3.5 h-3.5 mr-2" />
          {{ hasFoundationData ? $t('common.actions.edit') : $t('common.actions.add') }}
        </Button>
      </div>

      <!-- Compact view for Unit Mode, Full view for Global Mode -->
      <div 
        v-if="unitStore.activeUnitId === 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8 bg-muted/5 p-8 rounded-2xl border border-border/50"
      >
        <!-- Foundation Name + Logo -->
        <div class="md:col-span-3 flex items-center gap-4 mb-4 pb-6 border-b border-border/40">
          <div class="w-14 h-14 rounded-xl bg-muted flex items-center justify-center border border-border overflow-hidden bg-card shadow-sm shrink-0">
            <img v-if="localForm.logo" :src="localForm.logo" class="w-full h-full object-cover" />
            <LucideIcon v-else name="Building" class="w-6 h-6 text-foreground/10" />
          </div>
          <div class="space-y-1">
            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground/60">{{ $t('common.labels.foundationName') }}</Label>
            <div class="text-lg font-bold text-foreground/80">{{ localForm.foundation_name || '-' }}</div>
          </div>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.nib') }}</Label>
          <div class="text-sm font-mono font-bold text-foreground/80 pl-1 tracking-widest">{{ localForm.nib || '-' }}</div>
        </div>
        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.aktaPendirian') }}</Label>
          <div class="text-sm font-bold text-foreground/80 pl-1">{{ localForm.akta_pendirian_yayasan || '-' }}</div>
        </div>
        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.tglAktaPendirian') }}</Label>
          <div class="text-sm font-bold text-foreground/80 pl-1">{{ localForm.tgl_akta_pendirian_yayasan || '-' }}</div>
        </div>
      </div>
      
      <!-- Simplified Header for Unit Mode -->
      <div v-else class="flex items-center gap-4 p-4 rounded-xl bg-muted/20 border border-border/40">
        <div class="w-10 h-10 rounded-lg bg-card border border-border flex items-center justify-center overflow-hidden">
          <img v-if="localForm.logo" :src="localForm.logo" class="w-full h-full object-cover" />
          <LucideIcon v-else name="Building" class="w-4 h-4 text-muted-foreground/20" />
        </div>
        <div>
          <p class="text-[10px] font-black uppercase text-muted-foreground/60 tracking-wider">{{ $t('common.labels.organizingInstitution') }}</p>
          <h5 class="text-sm font-bold text-foreground/80">{{ localForm.foundation_name || localForm.name }}</h5>
        </div>
      </div>
    </div>

    <!-- Unit Identity Section: Primary in Unit Mode -->
    <div 
      class="md:col-span-2 space-y-6"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="p-2.5 bg-primary/5 text-primary rounded-xl border border-primary/10 shadow-none">
            <LucideIcon name="Fingerprint" class="w-5 h-5" />
          </div>
          <div>
            <h4 class="font-bold text-base text-foreground tracking-tight">
                {{ unitStore.activeUnitId === 0 ? $t('common.labels.unitMainIdentity') : $t('modules.school.labels.unitEduIdentity') }}
            </h4>
            <p class="text-xs text-muted-foreground mt-0.5 font-medium">
                {{ unitStore.activeUnitId === 0 ? $t('common.labels.unitMainIdentityDesc') : $t('modules.school.labels.unitActiveIdentityDesc') }}
            </p>
          </div>
        </div>
        <Badge v-if="currentUnit" variant="outline" class="rounded-lg bg-success/5 text-success border-success/20 font-black px-3">
            {{ $t('modules.school.messages.activeStatus', { name: currentUnit.name }) }}
        </Badge>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8 bg-card p-8 rounded-2xl border border-border shadow-sm relative overflow-hidden">
        <!-- Visual Accent -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-3xl" />
        
        <!-- Unit Name Header -->
        <div class="md:col-span-3 flex items-center gap-4 mb-4 pb-6 border-b border-border/40">
          <div class="w-16 h-16 rounded-xl bg-muted flex items-center justify-center border border-border overflow-hidden bg-card shadow-sm shrink-0">
            <img v-if="displayUnit?.settings?.logo" :src="displayUnit.settings.logo" class="w-full h-full object-cover" />
            <LucideIcon v-else name="School" class="w-7 h-7 text-primary/30" />
          </div>
          <div class="space-y-1">
            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground/60">{{ $t('common.labels.unitEducation') }}</Label>
            <div class="flex items-center gap-3">
              <span class="text-xl font-black text-foreground">{{ displayUnit?.name || localForm.name }}</span>
              <Badge v-if="displayUnit" variant="secondary" class="text-[9px] h-4 bg-primary/10 text-primary border-none uppercase tracking-widest font-black px-2">{{ displayUnit.level }}</Badge>
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80 uppercase tracking-tight">{{ $t('modules.school.labels.npsnFull') }}</Label>
          <div class="flex items-center gap-3 p-3 rounded-xl bg-muted/30 border border-border/40">
            <div class="p-1.5 rounded-lg bg-background border border-border">
                <LucideIcon name="Fingerprint" class="w-4 h-4 text-primary" />
            </div>
            <span class="font-mono font-black text-base tracking-[0.2em] text-foreground">{{ displayUnit?.npsn || localForm.npsn || '---' }}</span>
          </div>
        </div>

        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80 uppercase tracking-tight">{{ $t('modules.school.labels.ownershipStatus') }}</Label>
          <div class="flex items-center gap-3 p-3 rounded-xl bg-muted/30 border border-border/40">
            <div class="p-1.5 rounded-lg bg-background border border-border">
                <LucideIcon name="UserCheck" class="w-4 h-4 text-primary" />
            </div>
            <span class="font-bold text-sm text-foreground/80">{{ localForm.status_kepemilikan ? $t(`common.labels.${localForm.status_kepemilikan.toLowerCase()}`) : '---' }}</span>
          </div>
        </div>

        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80 uppercase tracking-tight">{{ $t('modules.school.labels.accreditation') }}</Label>
          <div class="flex items-center gap-3 p-3 rounded-xl bg-muted/30 border border-border/40">
            <div class="p-1.5 rounded-lg bg-background border border-border">
                <LucideIcon name="Award" class="w-4 h-4 text-primary" />
            </div>
            <span class="font-black text-lg text-primary">{{ displayUnit?.settings?.accreditation || '-' }}</span>
          </div>
        </div>

        <!-- Address specific to unit if available -->
        <div class="md:col-span-3 mt-4 space-y-3">
            <Label class="text-xs font-bold text-muted-foreground/80 uppercase tracking-tight">{{ $t('common.labels.operationalLocation') }}</Label>
            <div class="p-5 rounded-xl bg-muted/10 border border-border/60 flex gap-4">
                <LucideIcon name="MapPin" class="w-5 h-5 text-muted-foreground/40 shrink-0" />
                <div>
                    <p class="text-sm font-medium text-foreground/70 leading-relaxed">
                        {{ displayUnit?.settings?.address || localForm.address || $t('modules.school.messages.unitAddressNotSet') }}
                    </p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue';
import { 
  Label, Badge, LucideIcon, Button
} from '@/shared/components/ui';
import { useUnitStore } from '@/modules/School/stores/unit';

const props = defineProps<{
  form: any;
  isRestricted: boolean;
  levels: any[];
}>();

const unitStore = useUnitStore();

const emit = defineEmits<{
  (e: 'edit-foundation'): void;
}>();

const localForm = reactive({ ...props.form });

watch(() => props.form, (newVal) => {
  Object.assign(localForm, newVal);
}, { deep: true });

const isFoundationType = computed(() => {
  const type = localForm.type?.toLowerCase();
  return type === 'swasta' || type === 'yayasan' || type === 'private';
});

const hasFoundationData = computed(() => {
  return !!localForm.foundation_name;
});

const defaultLevel = computed(() => {
  return props.levels?.find(l => l.settings?.is_default) || props.levels?.[0] || null;
});

const currentUnit = computed(() => {
    if (unitStore.activeUnitId === '0') return null;
    return props.levels?.find(l => l.id === unitStore.activeUnitId) || null;
});

const displayUnit = computed(() => {
    return currentUnit.value || defaultLevel.value;
});
</script>
