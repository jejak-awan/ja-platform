<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
    <div class="space-y-2">
      <Label
        for="name"
        class="flex items-center gap-2"
      >
        {{ $t('common.labels.institutionName') }}
        <Badge
          variant="outline"
          class="text-[10px] h-4 px-1.5 opacity-70"
        >{{ $t('common.labels.locked') }}</Badge>
      </Label>
      <Input
        id="name"
        v-model="localForm.name"
        disabled
        class="rounded-xl h-11 bg-muted/30 border-border/40 font-semibold italic"
      />
      <p class="text-[10px] text-muted-foreground italic flex items-center gap-1">
        <LucideIcon
          name="Info"
          class="w-3 h-3"
        />
        {{ $t('features.school.hints.editNameViaWizard') }}
      </p>
    </div>
    <div class="space-y-2 text-left">
      <Label
        for="type"
        class="flex items-center gap-2"
      >
        {{ $t('common.labels.institutionStatus') }}
        <Badge
          variant="outline"
          class="text-[10px] h-4 px-1.5 opacity-70"
        >{{ $t('common.labels.locked') }}</Badge>
      </Label>
      <div class="h-11 px-4 rounded-xl border border-border/40 bg-muted/30 flex items-center gap-3">
        <LucideIcon
          :name="getSchoolIcon(localForm.type)"
          class="w-4 h-4 text-primary"
        />
        <span class="font-bold text-sm capitalize">{{ $t(`features.school.institution.status.${localForm.type?.toLowerCase() || 'undefined'}`) }}</span>
      </div>
      <p class="text-[10px] text-muted-foreground italic flex items-center gap-1">
        <LucideIcon
          name="Info"
          class="w-3 h-3"
        />
        {{ $t('features.school.hints.statusLocked') }}
      </p>
    </div>

    <!-- Multi-Location Settings -->
    <div
      v-if="(localForm.type === 'private' || localForm.type === 'yayasan') && localForm.is_multi_level"
      class="space-y-2 md:col-span-2 p-4 bg-muted/20 border border-border/40 rounded-xl animate-in fade-in slide-in-from-top-2"
    >
      <div class="flex items-center justify-between">
        <div class="space-y-0.5 text-left">
          <Label class="text-base font-bold text-foreground">{{ $t('features.school.wizard.options.multiLocation') }}</Label>
          <p class="text-sm text-muted-foreground">
            {{ $t('features.school.wizard.options.multiLocationDesc') }}
          </p>
        </div>
        <Switch 
          :checked="localForm.is_multi_branch" 
          class="data-[state=checked]:bg-primary shadow-sm" 
          @update:checked="val => localForm.is_multi_branch = val" 
        />
      </div>
    </div>
    
    <!-- Identification Numbers -->
    <div class="space-y-2">
      <Label
        for="npsn"
        class="flex items-center gap-2"
      >
        {{ $t('common.labels.npsnPrimary') }}
        <LucideIcon
          name="ShieldCheck"
          class="w-3.5 h-3.5 text-primary"
        />
      </Label>
      <Input
        id="npsn"
        v-model="localForm.npsn"
        maxlength="8"
        class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-mono tracking-widest"
        :placeholder="$t('common.labels.npsn_placeholder')"
      />
      <p class="text-[10px] text-muted-foreground italic mt-1 flex items-center gap-1">
        <LucideIcon
          name="Info"
          class="w-3 h-3"
        />
        {{ $t('features.school.hints.npsnFormat') }}
      </p>
    </div>
    <div class="space-y-2">
      <Label
        for="status_kepemilikan"
        class="flex items-center gap-2"
      >
        {{ $t('common.labels.ownershipStatus') }}
        <Badge
          variant="outline"
          class="text-[10px] h-4 px-1.5 opacity-70"
        >{{ $t('common.labels.auto') }}</Badge>
      </Label>
      <Input 
        id="status_kepemilikan" 
        :value="localForm.status_kepemilikan ? $t(`common.labels.${localForm.status_kepemilikan.toLowerCase()}`) : ''" 
        disabled 
        class="rounded-xl h-11 bg-muted/30 border-border/40 font-bold" 
      />
      <p class="text-[10px] text-muted-foreground italic flex items-center gap-1">
        <LucideIcon
          name="Info"
          class="w-3 h-3"
        />
        {{ $t('features.school.hints.autoOwnershipStatus') }}
      </p>
    </div>
    <div class="space-y-2">
      <Label for="nss">{{ $t('common.labels.nss') }}</Label>
      <Input
        id="nss"
        v-model="localForm.nss"
        :disabled="isRestricted"
        class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-mono"
        :placeholder="$t('common.labels.nss_placeholder')"
      />
    </div>
    <div class="space-y-2">
      <Label for="nds">{{ $t('common.labels.nds') }}</Label>
      <Input
        id="nds"
        v-model="localForm.nds"
        :disabled="isRestricted"
        class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-mono"
        :placeholder="$t('common.labels.nds_placeholder')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import { 
  Label, Input, Switch, Badge, LucideIcon
} from '@/components/ui';

const props = defineProps<{
  form: any;
  isRestricted: boolean;
}>();

const localForm = reactive({ ...props.form });

watch(() => props.form, (newVal) => {
  Object.assign(localForm, newVal);
}, { deep: true });

watch(localForm, (newVal) => {
  emit('update:form', { ...newVal });
}, { deep: true });

const getSchoolIcon = (type: string) => {
  switch (type) {
    case 'yayasan': return 'Component';
    case 'public': return 'ShieldCheck';
    default: return 'Building';
  }
};
const emit = defineEmits<{
  (e: 'update:form', value: any): void;
}>();
</script>
