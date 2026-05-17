<template>
  <AccordionItem
    value="location"
    class="border border-border rounded-2xl overflow-hidden shadow-none"
  >
    <AccordionTrigger class="px-6 py-5 hover:no-underline hover:bg-muted transition-colors [&[data-state=open]]:bg-muted/50 border-none">
      <div class="flex items-center gap-4">
        <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border">
          <LucideIcon
            name="MapPin"
            class="w-4 h-4"
          />
        </div>
        <div class="text-left">
          <span class="font-bold text-foreground text-sm tracking-tight">{{ $t('modules.school.units.sections.location') }}</span>
          <p class="text-[10px] text-muted-foreground font-medium mt-0.5 opacity-70">
            {{ $t('modules.school.units.sections.locationDesc') }}
          </p>
        </div>
      </div>
    </AccordionTrigger>
    <AccordionContent class="px-8 pt-6 pb-8">
      <!-- Use Primary Address Toggle (only if multi-branch) -->
      <div
        v-if="school.is_multi_branch"
        class="mb-8 p-6 bg-muted/30 rounded-2xl border border-border text-left"
      >
        <div class="flex items-center justify-between">
          <div class="space-y-1">
            <Label class="text-sm font-bold text-foreground">{{ $t('common.labels.usePrimaryAddress') }}</Label>
            <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">
              {{ $t('common.labels.inheritPrimaryAddress') }}
            </p>
          </div>
          <Switch 
            :checked="localSettings.use_primary_address" 
            class="data-[state=checked]:bg-foreground" 
            @update:checked="val => localSettings.use_primary_address = val"
          />
        </div>
        <div
          v-if="localSettings.use_primary_address"
          class="mt-5 p-5 bg-background rounded-xl border border-border shadow-none text-sm text-muted-foreground animate-in fade-in slide-in-from-top-1 duration-300"
        >
          <div class="flex items-start gap-3">
            <LucideIcon
              name="Info"
              class="w-4 h-4 mt-0.5 shrink-0 opacity-50"
            />
            <div>
              <p class="font-bold text-foreground/80">
                {{ school.address || $t('common.labels.notSet') }}
              </p>
              <p class="text-xs mt-1 font-medium opacity-70">
                {{ [school.desa_kelurahan, school.kecamatan, school.kabupaten_kota, school.provinsi].filter(Boolean).join(', ') || '-' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!school.is_multi_branch || !localSettings.use_primary_address"
        class="space-y-8 text-left"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <!-- Full Address -->
          <div class="md:col-span-2 space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.fullAddress') }}</Label>
            <Textarea
              v-model="localSettings.address"
              class="w-full rounded-xl border border-border focus:ring-foreground/5 min-h-[120px] bg-muted/10 block transition-all focus:border-foreground/20 text-sm font-medium"
              :placeholder="$t('common.placeholders.enterFullAddress')"
            />
          </div>

          <!-- Geographical Selectors -->
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.province') }}</Label>
            <Select
              v-model="localSettings.provinsi_id"
              @update:model-value="id => $emit('province-change', id)"
            >
              <SelectTrigger class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium">
                <SelectValue :placeholder="$t('common.placeholders.selectProvince')" />
              </SelectTrigger>
              <SelectContent class="max-h-[300px]">
                <SelectItem
                  v-for="item in provinces"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.city') }}</Label>
            <Select
              v-model="localSettings.kabupaten_kota_id"
              :disabled="!localSettings.provinsi_id"
              @update:model-value="id => $emit('regency-change', id)"
            >
              <SelectTrigger class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium">
                <SelectValue :placeholder="$t('common.placeholders.selectCity')" />
              </SelectTrigger>
              <SelectContent class="max-h-[300px]">
                <SelectItem
                  v-for="item in regencies"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.subdistrict') }}</Label>
            <Select
              v-model="localSettings.kecamatan_id"
              :disabled="!localSettings.kabupaten_kota_id"
              @update:model-value="id => $emit('district-change', id)"
            >
              <SelectTrigger class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium">
                <SelectValue :placeholder="$t('common.placeholders.selectSubdistrict')" />
              </SelectTrigger>
              <SelectContent class="max-h-[300px]">
                <SelectItem
                  v-for="item in districts"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.village') }}</Label>
            <Select
              v-model="localSettings.desa_kelurahan_id"
              :disabled="!localSettings.kecamatan_id"
              @update:model-value="id => $emit('village-change', id)"
            >
              <SelectTrigger class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium">
                <SelectValue :placeholder="$t('common.placeholders.selectVillage')" />
              </SelectTrigger>
              <SelectContent class="max-h-[300px]">
                <SelectItem
                  v-for="item in villages"
                  :key="item.id"
                  :value="item.id"
                >
                  {{ item.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Map Integration -->
        <div class="space-y-3">
          <Label class="text-xs font-bold text-muted-foreground/80 flex items-center gap-2">
            {{ $t('common.labels.mapLocation') }}
            <Badge
              variant="outline"
              class="text-[9px] h-4 px-1.5 opacity-50 border-border bg-muted/30 uppercase tracking-widest"
            >OSM Integration</Badge>
          </Label>
          <div class="w-full h-[300px] rounded-2xl border border-border overflow-hidden bg-muted/10 relative group">
            <iframe 
              v-if="mapUrl"
              :src="mapUrl"
              class="w-full h-full border-none grayscale contrast-[1.05] transition-all group-hover:grayscale-0 duration-700"
              allowfullscreen
            />
            <div
              v-else
              class="w-full h-full flex flex-col items-center justify-center gap-3 text-muted-foreground italic"
            >
              <LucideIcon
                name="MapPinOff"
                class="w-8 h-8 opacity-20"
              />
              <p class="text-xs">
                {{ $t('modules.school.messages.noMapLocation') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Other Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('modules.school.labels.rt') }}</Label>
            <Input
              v-model="localSettings.rt"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
              placeholder="000"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('modules.school.labels.rw') }}</Label>
            <Input
              v-model="localSettings.rw"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
              placeholder="000"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.postalCode') }}</Label>
            <Input
              v-model="localSettings.kode_pos"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
              placeholder="12345"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.phoneNumber') }}</Label>
            <Input
              v-model="localSettings.phone"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.email') }}</Label>
            <Input
              v-model="localSettings.email"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">Fax</Label>
            <Input
              v-model="localSettings.fax"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
          <div class="space-y-2 lg:col-span-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.url') }}</Label>
            <Input
              v-model="localSettings.website"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
        </div>
      </div>
    </AccordionContent>
  </AccordionItem>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { 
  AccordionItem, AccordionTrigger, AccordionContent, 
  Label, Input, Switch, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Badge, Textarea
} from '@/shared/components/ui';
import type { LocationItem } from '@/modules/Library/services/IndonesianLocation';

const props = defineProps<{
  settings: any;
  school: any;
  level: any;
  provinces: LocationItem[];
  regencies: LocationItem[];
  districts: LocationItem[];
  villages: LocationItem[];
}>();

const emit = defineEmits<{
  (e: 'province-change', id: string): void;
  (e: 'regency-change', id: string): void;
  (e: 'district-change', id: string): void;
  (e: 'village-change', id: string): void;
  (e: 'update:settings', value: any): void;
}>();

const localSettings = reactive({ ...props.settings });

watch(() => props.settings, (newVal) => {
  Object.assign(localSettings, newVal);
}, { deep: true });

watch(localSettings, (newVal) => {
  emit('update:settings', newVal);
}, { deep: true });

const mapUrl = computed(() => {
  const query = [
    localSettings.address,
    localSettings.desa_kelurahan,
    localSettings.kecamatan,
    localSettings.kabupaten_kota,
    localSettings.provinsi
  ].filter(Boolean).join(', ');
  
  if (!query) return null;
  return `https://www.openstreetmap.org/export/embed.html?layer=mapnik&bbox=95.0, -11.0, 141.0, 6.0&q=${encodeURIComponent(query)}`;
});
</script>
