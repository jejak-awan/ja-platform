<template>
  <Dialog
    :open="show"
    @update:open="$emit('update:show', $event)"
  >
    <DialogContent class="sm:max-w-[700px] max-h-[90vh] rounded-2xl border-border/40 shadow-2xl p-0 overflow-hidden flex flex-col">
      <!-- Header -->
      <DialogHeader class="p-6 bg-muted/20 border-b border-border/30 shrink-0">
        <div class="flex items-center gap-3">
          <div class="p-2.5 bg-primary/10 text-primary rounded-xl">
            <LucideIcon
              name="Building"
              class="w-5 h-5"
            />
          </div>
          <div class="text-left">
            <DialogTitle class="text-xl font-bold">
              {{ hasData ? $t('common.actions.edit') : $t('common.actions.add') }} {{ $t('common.labels.foundationIdentity') }}
            </DialogTitle>
            <DialogDescription class="text-xs font-bold text-muted-foreground/60 mt-0.5">
              {{ $t('common.labels.foundationLegalityDesc') }}
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <!-- Form Content -->
      <div class="flex-1 overflow-y-auto p-8 space-y-8 text-left custom-scrollbar">
        <!-- 0. Logo Section -->
        <div class="flex flex-col items-center justify-center pb-8 border-b border-border/30 gap-4">
          <div class="relative group">
            <div class="w-32 h-32 rounded-2xl bg-muted flex items-center justify-center border-2 border-dashed border-border group-hover:border-primary/30 transition-all overflow-hidden bg-card shadow-sm">
              <img v-if="localForm.logo" :src="localForm.logo" class="w-full h-full object-cover" />
              <LucideIcon v-else name="Building" class="w-12 h-12 text-foreground/10" />
              
              <div 
                class="absolute inset-0 bg-background/80 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer"
                @click="showMediaPicker = true"
              >
                <LucideIcon name="Camera" class="w-6 h-6 text-primary" />
                <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Logo Yayasan</span>
              </div>
            </div>
          </div>
          <div class="text-center">
            <p class="text-sm font-bold text-foreground">Logo Badan Hukum / Yayasan</p>
            <p class="text-[10px] text-muted-foreground mt-1 font-medium">Klik untuk mengubah atau mengunggah logo baru</p>
          </div>
        </div>

        <!-- 1. Legality Info -->
        <div class="space-y-6 pt-6">
          <h4 class="text-xs font-black uppercase tracking-widest text-primary/70 flex items-center gap-2">
            <LucideIcon name="FileCheck" class="w-3.5 h-3.5" />
            Legalitas & Badan Hukum
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2 md:col-span-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.foundationName') }}</Label>
              <Input 
                v-model="localForm.foundation_name" 
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-medium"
              />
            </div>

            <div class="space-y-2 md:col-span-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.nib') }}</Label>
              <Input 
                v-model="localForm.nib" 
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-mono tracking-widest"
              />
            </div>

            <div class="space-y-2 md:col-span-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.aktaPendirian') }}</Label>
              <Input 
                v-model="localForm.akta_pendirian_yayasan" 
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-medium"
              />
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.tglAktaPendirian') }}</Label>
              <Input 
                v-model="localForm.tgl_akta_pendirian_yayasan" 
                type="date"
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-medium"
              />
            </div>

            <div class="space-y-2 md:col-span-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.skKemenkumham') }}</Label>
              <Input 
                v-model="localForm.sk_kemenkumham" 
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-medium"
              />
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.tglSkKemenkumham') }}</Label>
              <Input 
                v-model="localForm.tgl_sk_kemenkumham" 
                type="date"
                class="h-11 rounded-xl border-border/50 bg-muted/5 font-medium"
              />
            </div>
          </div>
        </div>

        <!-- 2. Primary Address (Location Info) -->
        <div class="space-y-6 pt-6 border-t border-border/30">
          <h4 class="text-xs font-black uppercase tracking-widest text-primary/70 flex items-center gap-2">
            <LucideIcon name="MapPin" class="w-3.5 h-3.5" />
            Alamat Utama Lembaga
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Full Address -->
            <div class="md:col-span-2 space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.fullAddress') }}</Label>
              <Textarea 
                v-model="localForm.address"
                class="rounded-xl border-border/50 bg-muted/5 min-h-[100px] text-sm font-medium"
                :placeholder="$t('common.placeholders.enterFullAddress')"
              />
            </div>

            <!-- Province -->
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.province') }}</Label>
              <Select
                v-model="localForm.provinsi_id"
                @update:model-value="handleProvinceChange"
              >
                <SelectTrigger class="h-11 rounded-xl border-border/50 bg-muted/5">
                  <SelectValue :placeholder="$t('common.placeholders.selectProvince')" />
                </SelectTrigger>
                <SelectContent class="max-h-[300px]">
                  <SelectItem v-for="item in provinces" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- City -->
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.city') }}</Label>
              <Select
                v-model="localForm.kabupaten_kota_id"
                :disabled="!localForm.provinsi_id"
                @update:model-value="handleRegencyChange"
              >
                <SelectTrigger class="h-11 rounded-xl border-border/50 bg-muted/5">
                  <SelectValue :placeholder="$t('common.placeholders.selectCity')" />
                </SelectTrigger>
                <SelectContent class="max-h-[300px]">
                  <SelectItem v-for="item in regencies" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Subdistrict -->
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.subdistrict') }}</Label>
              <Select
                v-model="localForm.kecamatan_id"
                :disabled="!localForm.kabupaten_kota_id"
                @update:model-value="handleDistrictChange"
              >
                <SelectTrigger class="h-11 rounded-xl border-border/50 bg-muted/5">
                  <SelectValue :placeholder="$t('common.placeholders.selectSubdistrict')" />
                </SelectTrigger>
                <SelectContent class="max-h-[300px]">
                  <SelectItem v-for="item in districts" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Village -->
            <div class="space-y-2">
              <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.village') }}</Label>
              <Select
                v-model="localForm.desa_kelurahan_id"
                :disabled="!localForm.kecamatan_id"
                @update:model-value="handleVillageChange"
              >
                <SelectTrigger class="h-11 rounded-xl border-border/50 bg-muted/5">
                  <SelectValue :placeholder="$t('common.placeholders.selectVillage')" />
                </SelectTrigger>
                <SelectContent class="max-h-[300px]">
                  <SelectItem v-for="item in villages" :key="item.id" :value="item.id">{{ item.name }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- RT/RW/Postal -->
            <div class="grid grid-cols-3 gap-4 md:col-span-2">
              <div class="space-y-2">
                <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.rt') }}</Label>
                <Input v-model="localForm.rt" class="h-11 rounded-xl border-border/50 bg-muted/5" placeholder="000" />
              </div>
              <div class="space-y-2">
                <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.rw') }}</Label>
                <Input v-model="localForm.rw" class="h-11 rounded-xl border-border/50 bg-muted/5" placeholder="000" />
              </div>
              <div class="space-y-2">
                <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground/80">{{ $t('common.labels.postalCode') }}</Label>
                <Input v-model="localForm.kode_pos" class="h-11 rounded-xl border-border/50 bg-muted/5" placeholder="12345" />
              </div>
            </div>
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
          @click="handleSave"
        >
          {{ $t('common.actions.saveChanges') }}
        </Button>
      </DialogFooter>
    </DialogContent>

    <!-- Media Picker Modal -->
    <MediaPicker
      v-model:open="showMediaPicker"
      :path="`school/school_${localForm.id}/identity`"
      module="school"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <div class="hidden"></div>
      </template>
    </MediaPicker>
  </Dialog>
</template>

<script setup lang="ts">
import { reactive, watch, computed, ref, onMounted } from 'vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Button, LucideIcon, Input, Label, Textarea,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem
} from '@/components/ui';
import { IndonesianLocation, type LocationItem } from '@/services/IndonesianLocation';
import MediaPicker from '@/components/shared/media/MediaPicker.vue';

const props = defineProps<{
  show: boolean;
  form: any;
}>();

const emit = defineEmits<{
  (e: 'update:show', value: boolean): void;
  (e: 'save', form: any): void;
}>();

const localForm = reactive({ ...props.form });
const showMediaPicker = ref(false);

const handleMediaSelect = (media: any) => {
  const url = media?.url || media?.path;
  if (url) {
    localForm.logo = url;
  }
  showMediaPicker.value = false;
};

const provinces = ref<LocationItem[]>([]);
const regencies = ref<LocationItem[]>([]);
const districts = ref<LocationItem[]>([]);
const villages = ref<LocationItem[]>([]);

onMounted(async () => {
  provinces.value = await IndonesianLocation.getProvinces();
  if (localForm.provinsi_id) {
    regencies.value = await IndonesianLocation.getRegencies(localForm.provinsi_id);
  }
  if (localForm.kabupaten_kota_id) {
    districts.value = await IndonesianLocation.getDistricts(localForm.kabupaten_kota_id);
  }
  if (localForm.kecamatan_id) {
    villages.value = await IndonesianLocation.getVillages(localForm.kecamatan_id);
  }
});

watch(() => props.form, (newVal) => {
  Object.assign(localForm, newVal);
}, { deep: true });

const hasData = computed(() => !!props.form.foundation_name);

const handleProvinceChange = async (id: string) => {
  const p = provinces.value.find(x => x.id === id);
  if (p) localForm.provinsi = p.name;
  localForm.kabupaten_kota_id = '';
  localForm.kecamatan_id = '';
  localForm.desa_kelurahan_id = '';
  regencies.value = await IndonesianLocation.getRegencies(id);
};

const handleRegencyChange = async (id: string) => {
  const r = regencies.value.find(x => x.id === id);
  if (r) localForm.kabupaten_kota = r.name;
  localForm.kecamatan_id = '';
  localForm.desa_kelurahan_id = '';
  districts.value = await IndonesianLocation.getDistricts(id);
};

const handleDistrictChange = async (id: string) => {
  const d = districts.value.find(x => x.id === id);
  if (d) localForm.kecamatan = d.name;
  localForm.desa_kelurahan_id = '';
  villages.value = await IndonesianLocation.getVillages(id);
};

const handleVillageChange = (id: string) => {
  const v = villages.value.find(x => x.id === id);
  if (v) localForm.desa_kelurahan = v.name;
};

const handleSave = () => {
  emit('save', { ...localForm });
};
</script>
