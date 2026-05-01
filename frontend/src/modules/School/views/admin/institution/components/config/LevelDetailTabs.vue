<template>
  <div class="space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-500">
    <!-- Save Button -->
    <div class="flex justify-end">
      <Button 
        :disabled="saving" 
        size="sm" 
        class="rounded-xl px-6 h-9 font-bold bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95"
        @click="$emit('save')"
      >
        <LucideIcon
          v-if="saving"
          name="Loader2"
          class="w-3.5 h-3.5 mr-2 animate-spin"
        />
        <LucideIcon
          v-else
          name="Save"
          class="w-3.5 h-3.5 mr-2"
        />
        {{ $t('common.actions.saveChanges') }}
      </Button>
    </div>

    <!-- Accordion Sections -->
    <Accordion
      type="multiple"
      :default-value="['location']"
      class="space-y-3"
    >
      <!-- ========== LOCATION & CONTACT ========== -->
      <AccordionItem
        value="location"
        class="border border-border/50 rounded-2xl overflow-hidden shadow-sm"
      >
        <AccordionTrigger class="px-6 py-4 hover:no-underline hover:bg-muted/20 transition-colors [&[data-state=open]]:bg-muted/20">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-500/10 text-blue-600 rounded-lg">
              <LucideIcon
                name="MapPin"
                class="w-4 h-4"
              />
            </div>
            <div class="text-left">
              <span class="font-bold text-foreground">{{ $t('features.school.levels.sections.location') }}</span>
              <p class="text-xs text-muted-foreground font-normal mt-0.5">
                {{ $t('features.school.levels.sections.locationDesc') }} ({{ level.name }})
              </p>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent class="px-6 pb-6">
          <!-- Use Primary Address Toggle (only if multi-branch) -->
          <div
            v-if="school.is_multi_branch"
            class="mb-6 p-4 bg-primary/5 rounded-2xl border border-primary/10 text-left"
          >
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label class="text-sm font-bold text-primary">{{ $t('common.usePrimaryAddress') }}</Label>
                <p class="text-[11px] text-muted-foreground">
                  {{ $t('common.inheritPrimaryAddress') }}
                </p>
              </div>
              <Switch 
                :checked="settings.use_primary_address" 
                class="data-[state=checked]:bg-primary" 
                @update:checked="val => settings.use_primary_address = val"
              />
            </div>
            <div
              v-if="settings.use_primary_address"
              class="mt-4 p-4 bg-muted/30 rounded-xl border border-dashed border-border/60 text-sm text-muted-foreground"
            >
              <div class="flex items-start gap-2">
                <LucideIcon
                  name="Info"
                  class="w-4 h-4 mt-0.5 shrink-0"
                />
                <div>
                  <p class="font-medium text-foreground/70">
                    {{ school.address || $t('common.notSet') }}
                  </p>
                  <p class="text-xs mt-1">
                    {{ [school.desa_kelurahan, school.kecamatan, school.kabupaten_kota, school.provinsi].filter(Boolean).join(', ') || '-' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="!school.is_multi_branch || !settings.use_primary_address"
            class="space-y-6 text-left"
          >
            <!-- Full Address -->
            <div class="space-y-2">
              <Label>{{ $t('common.labels.fullAddress') }}</Label>
              <Textarea
                v-model="settings.address"
                class="rounded-xl border-border/50 focus:ring-primary/20 min-h-[80px]"
                :placeholder="$t('common.placeholders.enterFullAddress')"
              />
            </div>

            <!-- Geographical Selectors -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label>{{ $t('common.labels.province') }}</Label>
                <Select
                  v-model="settings.provinsi_id"
                  @update:model-value="handleProvinceChange"
                >
                  <SelectTrigger class="rounded-xl h-11 border-border/50 bg-muted/5">
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
                <Label>{{ $t('common.labels.city') }}</Label>
                <Select
                  v-model="settings.kabupaten_kota_id"
                  :disabled="!settings.provinsi_id"
                  @update:model-value="handleRegencyChange"
                >
                  <SelectTrigger class="rounded-xl h-11 border-border/50 bg-muted/5">
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
                <Label>{{ $t('common.labels.subdistrict') }}</Label>
                <Select
                  v-model="settings.kecamatan_id"
                  :disabled="!settings.kabupaten_kota_id"
                  @update:model-value="handleDistrictChange"
                >
                  <SelectTrigger class="rounded-xl h-11 border-border/50 bg-muted/5">
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
                <Label>{{ $t('common.labels.village') }}</Label>
                <Select
                  v-model="settings.desa_kelurahan_id"
                  :disabled="!settings.kecamatan_id"
                  @update:model-value="handleVillageChange"
                >
                  <SelectTrigger class="rounded-xl h-11 border-border/50 bg-muted/5">
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
            <div class="space-y-2">
              <Label class="flex items-center gap-2">
                {{ $t('common.labels.mapLocation') }}
                <Badge
                  variant="outline"
                  class="text-[10px] h-4 px-1.5 opacity-70"
                >OSM Integration</Badge>
              </Label>
              <div class="w-full h-[250px] rounded-2xl border border-border/40 overflow-hidden bg-muted/30 relative group">
                <iframe 
                  v-if="mapUrl"
                  :src="mapUrl"
                  class="w-full h-full border-none grayscale-[0.5] contrast-[1.1] transition-all group-hover:grayscale-0"
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
                    {{ $t('common.messages.noMapLocation') }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Other Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="space-y-2">
                <Label>{{ $t('common.labels.rt') }}</Label>
                <Input
                  v-model="settings.rt"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                  placeholder="000"
                />
              </div>
              <div class="space-y-2">
                <Label>{{ $t('common.labels.rw') }}</Label>
                <Input
                  v-model="settings.rw"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                  placeholder="000"
                />
              </div>
              <div class="space-y-2">
                <Label>{{ $t('common.labels.postalCode') }}</Label>
                <Input
                  v-model="settings.kode_pos"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                  placeholder="12345"
                />
              </div>
              <div class="space-y-2">
                <Label>{{ $t('common.labels.phoneNumber') }}</Label>
                <Input
                  v-model="settings.phone"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                />
              </div>
              <div class="space-y-2">
                <Label>{{ $t('common.labels.email') }}</Label>
                <Input
                  v-model="settings.email"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                />
              </div>
              <div class="space-y-2">
                <Label>Fax</Label>
                <Input
                  v-model="settings.fax"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                />
              </div>
              <div class="space-y-2 lg:col-span-2">
                <Label>{{ $t('common.labels.url') }}</Label>
                <Input
                  v-model="settings.website"
                  class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                />
              </div>
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <!-- ========== PROFILE & HISTORY ========== -->
      <AccordionItem
        value="profile"
        class="border border-border/50 rounded-2xl overflow-hidden shadow-sm"
      >
        <AccordionTrigger class="px-6 py-4 hover:no-underline hover:bg-muted/20 transition-colors [&[data-state=open]]:bg-muted/20">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-500/10 text-emerald-600 rounded-lg">
              <LucideIcon
                name="FileText"
                class="w-4 h-4"
              />
            </div>
            <div class="text-left">
              <span class="font-bold text-foreground">{{ $t('features.school.levels.sections.profile') }}</span>
              <p class="text-xs text-muted-foreground font-normal mt-0.5">
                {{ $t('features.school.levels.sections.profileDesc') }} ({{ level.name }})
              </p>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent class="px-6 pb-6 space-y-6 text-left">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.principalName') }}</Label>
              <Input
                v-model="settings.principal_name"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.foundationName') }}</Label>
              <Input
                v-model="settings.foundation_name"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
          </div>

          <Separator />

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.kurikulum') }}</Label>
              <Input
                v-model="settings.kurikulum"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
                :placeholder="$t('common.labels.untitled')"
              />
            </div>
          </div>

          <Separator />

          <div class="grid grid-cols-1 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.vision') }}</Label>
              <TiptapEditor
                v-model="settings.vision"
                :placeholder="$t('common.labels.vision')"
                compact
                class="min-h-[150px]"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.mission') }}</Label>
              <TiptapEditor
                v-model="settings.mission"
                :placeholder="$t('common.labels.mission')"
                compact
                class="min-h-[150px]"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.goals') }}</Label>
              <TiptapEditor
                v-model="settings.goals"
                :placeholder="$t('common.labels.goals')"
                compact
                class="min-h-[150px]"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.history') }}</Label>
              <TiptapEditor
                v-model="settings.history"
                :placeholder="$t('common.labels.history')"
                compact
                class="min-h-[150px]"
              />
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>

      <!-- ========== LEGALITY & FINANCE ========== -->
      <AccordionItem
        value="legal"
        class="border border-border/50 rounded-2xl overflow-hidden shadow-sm"
      >
        <AccordionTrigger class="px-6 py-4 hover:no-underline hover:bg-muted/20 transition-colors [&[data-state=open]]:bg-muted/20">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-500/10 text-amber-600 rounded-lg">
              <LucideIcon
                name="Shield"
                class="w-4 h-4"
              />
            </div>
            <div class="text-left">
              <span class="font-bold text-foreground">{{ $t('features.school.levels.sections.legality') }}</span>
              <p class="text-xs text-muted-foreground font-normal mt-0.5">
                {{ $t('features.school.levels.sections.legalityDesc') }} ({{ level.name }})
              </p>
            </div>
          </div>
        </AccordionTrigger>
        <AccordionContent class="px-6 pb-6 space-y-6 text-left">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.npwp') }}</Label>
              <Input
                v-model="settings.npwp"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-mono"
                placeholder="00.000.000.0-000.000"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.accreditation') }}</Label>
              <Select v-model="settings.accreditation">
                <SelectTrigger class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-bold">
                  <SelectValue :placeholder="$t('common.labels.accreditation')" />
                </SelectTrigger>
                <SelectContent class="rounded-xl shadow-xl">
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
          </div>

          <Separator />

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.skEstablishment') }}</Label>
              <Input
                v-model="settings.sk_pendirian"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.skEstablishmentDate') }}</Label>
              <Input
                v-model="settings.tgl_sk_pendirian"
                type="date"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.skOperational') }}</Label>
              <Input
                v-model="settings.sk_operasional"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.skOperationalDate') }}</Label>
              <Input
                v-model="settings.tgl_sk_operasional"
                type="date"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
          </div>

          <Separator />

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-2">
              <Label>{{ $t('common.labels.bankName') }}</Label>
              <Input
                v-model="settings.bank_name"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.bankAccount') }}</Label>
              <Input
                v-model="settings.bank_account_number"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20 font-mono"
              />
            </div>
            <div class="space-y-2">
              <Label>{{ $t('common.labels.bankHolder') }}</Label>
              <Input
                v-model="settings.bank_account_holder"
                class="rounded-xl h-11 border-border/50 focus:ring-primary/20"
              />
            </div>
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref, onMounted, computed } from 'vue';
import type { LevelSettings } from '../../../../../stores/level';
import type { SchoolLevel } from '@/types';
import {
  Accordion, AccordionItem, AccordionTrigger, AccordionContent,
  Button, Label, Input, Switch, Separator, LucideIcon, Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Badge
} from '@/components/ui';
import TiptapEditor from '@/components/shared/editor/TiptapEditor.vue';
import { IndonesianLocation, type LocationItem } from '@/services/IndonesianLocation';

const provinces = ref<LocationItem[]>([]);
const regencies = ref<LocationItem[]>([]);
const districts = ref<LocationItem[]>([]);
const villages = ref<LocationItem[]>([]);

const mapUrl = computed(() => {
  const query = [
    settings.address,
    settings.desa_kelurahan,
    settings.kecamatan,
    settings.kabupaten_kota,
    settings.provinsi
  ].filter(Boolean).join(', ');
  
  if (!query) return null;
  return `https://www.openstreetmap.org/export/embed.html?layer=mapnik&bbox=95.0, -11.0, 141.0, 6.0&q=${encodeURIComponent(query)}`;
});

onMounted(async () => {
  provinces.value = await IndonesianLocation.getProvinces();
});

const handleProvinceChange = async (id: string) => {
  const p = provinces.value.find(x => x.id === id);
  if (p) settings.provinsi = p.name;
  settings.kabupaten_kota_id = '';
  settings.kecamatan_id = '';
  settings.desa_kelurahan_id = '';
  regencies.value = await IndonesianLocation.getRegencies(id);
};

const handleRegencyChange = async (id: string) => {
  const r = regencies.value.find(x => x.id === id);
  if (r) settings.kabupaten_kota = r.name;
  settings.kecamatan_id = '';
  settings.desa_kelurahan_id = '';
  districts.value = await IndonesianLocation.getDistricts(id);
};

const handleDistrictChange = async (id: string) => {
  const d = districts.value.find(x => x.id === id);
  if (d) settings.kecamatan = d.name;
  settings.desa_kelurahan_id = '';
  villages.value = await IndonesianLocation.getVillages(id);
};

const handleVillageChange = (id: string) => {
  const v = villages.value.find(x => x.id === id);
  if (v) settings.desa_kelurahan = v.name;
};

const props = defineProps<{
  level: SchoolLevel;
  school: any;
  saving?: boolean;
  isSuperAdmin?: boolean;
}>();

// No local restrictions, managed by parent or props

const emit = defineEmits<{
  (e: 'save'): void;
  (e: 'update:level', value: SchoolLevel): void;
}>();

/**
 * Create a reactive copy of settings to bind to form fields.
 * This prevents direct mutation of the level prop.
 */
const settings = reactive<LevelSettings>({
  use_primary_address: true,
  accreditation: props.level.accreditation,
  ...props.level.settings,
});

/**
 * Sync local settings back to the level object when they change.
 * NOTE: This still technically mutates the prop object, but in a deeper way.
 * To be 100% clean, the parent should own the state and we should emit.
 * For now, focusing on removing the direct property assignment errors.
 */
watch(settings, (newSettings) => {
  if (props.level) {
    emit('update:level', {
      ...props.level,
      settings: { ...newSettings },
      accreditation: newSettings.accreditation || props.level.accreditation
    });
  }
}, { deep: true });

/**
 * Watch for changes in the level prop (e.g., when the user selects a different level tab).
 * Resets or populates the local settings state.
 */
watch(() => props.level, (newLevel) => {
  if (newLevel) {
    Object.assign(settings, {
      use_primary_address: true,
      accreditation: newLevel.accreditation,
      address: '', rt: '', rw: '', dusun: '',
      desa_kelurahan: '', kecamatan: '', kabupaten_kota: '',
      provinsi: '', kode_pos: '', phone: '', email: '',
      fax: '', website: '',
      principal_name: '', foundation_name: '',
      vision: '', mission: '', goals: '', history: '',
      kurikulum: '',
      npwp: '', sk_pendirian: '', tgl_sk_pendirian: '',
      sk_operasional: '', tgl_sk_operasional: '',
      bank_name: '', bank_account_number: '', bank_account_holder: '',
      ...newLevel.settings,
    });
  }
}, { immediate: false });
</script>
