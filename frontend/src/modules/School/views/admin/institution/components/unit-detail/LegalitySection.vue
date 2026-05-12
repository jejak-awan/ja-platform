<template>
  <AccordionItem
    value="legal"
    class="border border-border rounded-2xl overflow-hidden shadow-none"
  >
    <AccordionTrigger class="px-6 py-5 hover:no-underline hover:bg-muted transition-colors [&[data-state=open]]:bg-muted/50 border-none">
      <div class="flex items-center gap-4">
        <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border">
          <LucideIcon
            name="Shield"
            class="w-4 h-4"
          />
        </div>
        <div class="text-left">
          <span class="font-bold text-foreground text-sm tracking-tight">{{ $t('modules.school.units.sections.legality') }}</span>
          <p class="text-[10px] text-muted-foreground font-medium mt-0.5 opacity-70">
            {{ $t('modules.school.units.sections.legalityDesc') }}
          </p>
        </div>
      </div>
    </AccordionTrigger>
    <AccordionContent class="px-8 pb-8 space-y-8 text-left">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mt-4">
        <div class="space-y-2">
          <Label class="flex items-center gap-2 text-xs font-bold text-muted-foreground/80">
            {{ $t('common.labels.npsn') }}
            <LucideIcon name="ShieldCheck" class="w-3 h-3 opacity-50" />
          </Label>
          <Input
            v-model="localLevel.npsn"
            maxlength="8"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium font-mono tracking-widest"
            :placeholder="$t('common.labels.npsn_placeholder')"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.npwp') }}</Label>
          <Input
            v-model="localSettings.npwp"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium font-mono"
            placeholder="00.000.000.0-000.000"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.nss') }}</Label>
          <Input
            v-model="localSettings.nss"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium font-mono"
            :placeholder="$t('common.labels.nss_placeholder')"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.nds') }}</Label>
          <Input
            v-model="localSettings.nds"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium font-mono"
            :placeholder="$t('common.labels.nds_placeholder')"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('modules.school.labels.accreditation') }}</Label>
          <Select 
            v-model="localSettings.accreditation"
          >
            <SelectTrigger class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-bold">
              <SelectValue :placeholder="$t('modules.school.labels.accreditation')" />
            </SelectTrigger>
            <SelectContent class="rounded-xl shadow-xl border-border">
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

      <Separator class="bg-border/60" />

      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.skEstablishment') }}</Label>
          <Input
            v-model="localSettings.sk_pendirian"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.skEstablishmentDate') }}</Label>
          <Input
            v-model="localSettings.tgl_sk_pendirian"
            type="date"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.skOperational') }}</Label>
          <Input
            v-model="localSettings.sk_operasional"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.skOperationalDate') }}</Label>
          <Input
            v-model="localSettings.tgl_sk_operasional"
            type="date"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
      </div>

      <Separator class="bg-border/60" />

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.bankName') }}</Label>
          <Input
            v-model="localSettings.bank_name"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.bankAccount') }}</Label>
          <Input
            v-model="localSettings.bank_account_number"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium font-mono"
          />
        </div>
        <div class="space-y-2">
          <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.bankHolder') }}</Label>
          <Input
            v-model="localSettings.bank_account_holder"
            class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
          />
        </div>
      </div>
    </AccordionContent>
  </AccordionItem>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue';
import { 
  AccordionItem, AccordionTrigger, AccordionContent, 
  Label, Input, Separator, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/shared/components/ui';

const props = defineProps<{
  settings: any;
  level: any;
}>();

const emit = defineEmits<{
  (e: 'update:level', value: any): void;
  (e: 'update:settings', value: any): void;
}>();

const localSettings = reactive({ ...props.settings });
const localLevel = reactive({ ...props.level });

watch(() => props.settings, (newVal) => {
  Object.assign(localSettings, newVal);
}, { deep: true });

watch(() => props.level, (newVal) => {
  Object.assign(localLevel, newVal);
}, { deep: true });

watch(localSettings, (newVal) => {
  emit('update:settings', newVal);
}, { deep: true });

watch(localLevel, (newVal) => {
  emit('update:level', newVal);
}, { deep: true });
</script>
