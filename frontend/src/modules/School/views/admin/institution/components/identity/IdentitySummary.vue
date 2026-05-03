<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10 text-left p-8">
    <!-- Foundation Details (Section 1 if Yayasan) -->
    <div
      v-if="isFoundationType"
      class="md:col-span-2 space-y-6 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border shadow-none">
            <LucideIcon name="FileCheck" class="w-5 h-5" />
          </div>
          <div>
            <h4 class="font-bold text-base text-foreground tracking-tight">{{ $t('common.labels.foundationLegality') }}</h4>
            <p class="text-xs text-muted-foreground mt-0.5 font-medium">{{ $t('common.labels.foundationLegalityDesc') }}</p>
          </div>
        </div>
        <Button
          v-if="!isRestricted"
          variant="outline"
          size="sm"
          class="rounded-xl font-bold h-9 px-4 border-border hover:bg-muted"
          @click="$emit('edit-foundation')"
        >
          <LucideIcon :name="hasFoundationData ? 'Pencil' : 'Plus'" class="w-3.5 h-3.5 mr-2" />
          {{ hasFoundationData ? $t('common.actions.edit') : $t('common.actions.add') }}
        </Button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8 bg-muted/5 p-8 rounded-2xl border border-border/50">
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
        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.skKemenkumham') }}</Label>
          <div class="text-sm font-bold text-foreground/80 pl-1">{{ localForm.sk_kemenkumham || '-' }}</div>
        </div>
        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.tglSkKemenkumham') }}</Label>
          <div class="text-sm font-bold text-foreground/80 pl-1">{{ localForm.tgl_sk_kemenkumham || '-' }}</div>
        </div>
      </div>

      <!-- Institution Primary Address -->
      <div class="mt-8 p-6 bg-muted/5 rounded-2xl border border-border/50">
        <div class="flex items-start gap-4">
          <div class="p-2 bg-primary/5 text-primary rounded-lg border border-primary/10">
            <LucideIcon name="MapPin" class="w-4 h-4" />
          </div>
          <div class="flex-1">
            <h5 class="text-xs font-bold text-foreground/70 uppercase tracking-wider mb-2">Alamat Utama Lembaga</h5>
            <p class="text-sm font-bold text-foreground/80 leading-relaxed">
              {{ localForm.address || $t('common.labels.notSet') }}
            </p>
            <p v-if="localForm.provinsi" class="text-xs text-muted-foreground mt-1.5 font-medium flex items-center gap-1.5">
              <span v-if="localForm.desa_kelurahan">{{ localForm.desa_kelurahan }},</span>
              <span v-if="localForm.kecamatan">{{ localForm.kecamatan }},</span>
              <span v-if="localForm.kabupaten_kota">{{ localForm.kabupaten_kota }},</span>
              <span>{{ localForm.provinsi }}</span>
              <span v-if="localForm.kode_pos" class="ml-1 font-mono">({{ localForm.kode_pos }})</span>
            </p>
            <div v-if="localForm.rt || localForm.rw" class="flex gap-4 mt-2">
              <div v-if="localForm.rt" class="text-[10px] font-bold text-muted-foreground/60 px-2 py-0.5 bg-muted rounded-md border border-border/40">RT {{ localForm.rt }}</div>
              <div v-if="localForm.rw" class="text-[10px] font-bold text-muted-foreground/60 px-2 py-0.5 bg-muted rounded-md border border-border/40">RW {{ localForm.rw }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Unit Info Summary (Section 2 if Yayasan, or Section 1 if Public) -->
    <div 
      class="md:col-span-2 space-y-6"
    >
      <div class="flex items-center gap-4">
        <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border shadow-none">
          <LucideIcon name="Fingerprint" class="w-5 h-5" />
        </div>
        <div>
          <h4 class="font-bold text-base text-foreground tracking-tight">{{ isFoundationType ? $t('common.labels.unitLegality') : $t('common.labels.legalIdentity') }}</h4>
          <p class="text-xs text-muted-foreground mt-0.5 font-medium">{{ isFoundationType ? $t('common.labels.unitLegalityDesc') : $t('common.labels.legalIdentityDesc') }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-8 bg-muted/5 p-8 rounded-2xl border border-border/50">
        <!-- Default Unit Logo + Name -->
        <div class="md:col-span-3 flex items-center gap-4 mb-4 pb-6 border-b border-border/40">
          <div class="w-14 h-14 rounded-xl bg-muted flex items-center justify-center border border-border overflow-hidden bg-card shadow-sm shrink-0">
            <img v-if="defaultLevel?.settings?.logo" :src="defaultLevel.settings.logo" class="w-full h-full object-cover" />
            <LucideIcon v-else name="School" class="w-6 h-6 text-foreground/10" />
          </div>
          <div class="space-y-1">
            <Label class="text-[10px] font-black uppercase tracking-wider text-muted-foreground/60">Unit Utama / Default</Label>
            <div class="flex items-center gap-2">
              <span class="text-lg font-bold text-foreground/80">{{ defaultLevel?.name || localForm.name }}</span>
              <Badge v-if="defaultLevel" variant="outline" class="text-[9px] h-4 bg-muted border-border uppercase tracking-widest">{{ defaultLevel.level }}</Badge>
            </div>
          </div>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.npsnPrimary') }}</Label>
          <div class="flex items-center gap-2 pl-1">
            <LucideIcon name="Fingerprint" class="w-4 h-4 text-foreground/30" />
            <span class="font-mono font-bold text-sm tracking-widest text-foreground/70">{{ defaultLevel?.npsn || localForm.npsn || '---' }}</span>
          </div>
          <p class="text-[10px] text-muted-foreground font-medium pl-1 flex items-center gap-1.5 opacity-70">
            <LucideIcon name="Info" class="w-3.5 h-3.5 opacity-50" />
            {{ $t('features.school.units.hints.editViaLevel') }}
          </p>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.ownershipStatus') }}</Label>
          <div class="flex items-center gap-2 pl-1">
            <LucideIcon name="UserCheck" class="w-4 h-4 text-foreground/30" />
            <span class="font-bold text-sm text-foreground/70">{{ localForm.status_kepemilikan ? $t(`common.labels.${localForm.status_kepemilikan.toLowerCase()}`) : '---' }}</span>
          </div>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.nss') }}</Label>
          <div class="text-sm font-mono font-bold text-foreground/60 pl-1 tracking-widest">{{ localForm.nss || '---' }}</div>
        </div>

        <div class="space-y-1.5">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.nds') }}</Label>
          <div class="text-sm font-mono font-bold text-foreground/60 pl-1 tracking-widest">{{ localForm.nds || '---' }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue';
import { 
  Label, Badge, LucideIcon, Button
} from '@/components/ui';

const props = defineProps<{
  form: any;
  isRestricted: boolean;
  levels: any[];
}>();

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
</script>
