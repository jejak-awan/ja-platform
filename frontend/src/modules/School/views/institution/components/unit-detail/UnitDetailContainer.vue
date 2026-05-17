<template>
  <div class="space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-500">
    <!-- Accordion Sections -->
    <Accordion
      type="multiple"
      :default-value="['location']"
      class="space-y-3"
    >
      <!-- ========== LOCATION & CONTACT ========== -->
      <LocationSection
        :settings="settings"
        :school="school"
        :level="level"
        :provinces="provinces"
        :regencies="regencies"
        :districts="districts"
        :villages="villages"
        @province-change="handleProvinceChange"
        @regency-change="handleRegencyChange"
        @district-change="handleDistrictChange"
        @village-change="handleVillageChange"
        @update:settings="val => Object.assign(settings, val)"
      />

      <!-- ========== PROFILE & HISTORY ========== -->
      <ProfileSection
        :settings="settings"
        :level="level"
        :school="school"
        @update:settings="val => Object.assign(settings, val)"
      />

      <!-- ========== LEGALITY & BANK ========== -->
      <LegalitySection
        :settings="settings"
        :level="level"
        @update:level="val => $emit('update:level', val)"
        @update:settings="val => Object.assign(settings, val)"
      />
    </Accordion>
  </div>
</template>

<script setup lang="ts">
import { reactive, watch, ref, onMounted } from 'vue';
import type { UnitSettings } from '@/modules/School/stores/unit';
import type { SchoolUnit } from '@/modules/School/types';
import { Accordion } from '@/shared/components/ui';
import { IndonesianLocation, type LocationItem } from '@/modules/Library/services/IndonesianLocation';

// Import split sections
import LocationSection from './LocationSection.vue';
import ProfileSection from './ProfileSection.vue';
import LegalitySection from './LegalitySection.vue';

const props = defineProps<{
  level: SchoolUnit;
  school: any;
  saving?: boolean;
  isSuperAdmin?: boolean;
}>();

const emit = defineEmits<{
  (e: 'save'): void;
  (e: 'dirty', value: boolean): void;
  (e: 'update:level', value: SchoolUnit): void;
}>();

const provinces = ref<LocationItem[]>([]);
const regencies = ref<LocationItem[]>([]);
const districts = ref<LocationItem[]>([]);
const villages = ref<LocationItem[]>([]);

const settings = reactive<UnitSettings>({
  use_primary_address: true,
  accreditation: props.level.accreditation,
  ...props.level.settings,
});

const initialSettings = ref("");

const getSettingsPayload = (source: any) => {
  // Use a strict, ordered schema to ensure JSON.stringify is consistent for comparison
  const schema = {
    use_primary_address: source.use_primary_address ?? true,
    accreditation: source.accreditation ?? props.level.accreditation,
    address: source.address ?? '',
    provinsi_id: source.provinsi_id ?? '',
    kabupaten_kota_id: source.kabupaten_kota_id ?? '',
    kecamatan_id: source.kecamatan_id ?? '',
    desa_kelurahan_id: source.desa_kelurahan_id ?? '',
    rt: source.rt ?? '',
    rw: source.rw ?? '',
    kode_pos: source.kode_pos ?? '',
    phone: source.phone ?? '',
    email: source.email ?? '',
    fax: source.fax ?? '',
    website: source.website ?? '',
    principal_name: source.principal_name ?? '',
    foundation_name: source.foundation_name ?? '',
    vision: source.vision ?? '',
    mission: source.mission ?? '',
    goals: source.goals ?? '',
    history: source.history ?? '',
    npwp: source.npwp ?? '',
    sk_pendirian: source.sk_pendirian ?? '',
    tgl_sk_pendirian: source.tgl_sk_pendirian ?? '',
    sk_operasional: source.sk_operasional ?? '',
    tgl_sk_operasional: source.tgl_sk_operasional ?? '',
    bank_name: source.bank_name ?? '',
    bank_account_number: source.bank_account_number ?? '',
    bank_account_holder: source.bank_account_holder ?? '',
    logo: source.logo ?? '',
    logo_dinas: source.logo_dinas ?? '',
    logo_yayasan: source.logo_yayasan ?? '',
  };
  return JSON.stringify(schema);
};

const captureInitialState = () => {
  initialSettings.value = getSettingsPayload({
    use_primary_address: true,
    accreditation: props.level.accreditation,
    ...props.level.settings,
  });
};

onMounted(async () => {
  captureInitialState();
  checkDirty();
  provinces.value = await IndonesianLocation.getProvinces();
  if (settings.provinsi_id) {
    regencies.value = await IndonesianLocation.getRegencies(settings.provinsi_id);
  }
  if (settings.kabupaten_kota_id) {
    districts.value = await IndonesianLocation.getDistricts(settings.kabupaten_kota_id);
  }
  if (settings.kecamatan_id) {
    villages.value = await IndonesianLocation.getVillages(settings.kecamatan_id);
  }
});

const isDetailDirty = ref(false);

const checkDirty = () => {
  isDetailDirty.value = getSettingsPayload(settings) !== initialSettings.value;
};

watch(isDetailDirty, (val) => {
  emit('dirty', val);
}, { immediate: true });

const revertChanges = async () => {
  const original = JSON.parse(initialSettings.value);
  for (const key in original) {
    (settings as any)[key] = original[key];
  }

  // Re-fetch dependent location data to sync dropdowns
  if (settings.provinsi_id) {
    regencies.value = await IndonesianLocation.getRegencies(settings.provinsi_id);
  } else {
    regencies.value = [];
  }
  
  if (settings.kabupaten_kota_id) {
    districts.value = await IndonesianLocation.getDistricts(settings.kabupaten_kota_id);
  } else {
    districts.value = [];
  }
  
  if (settings.kecamatan_id) {
    villages.value = await IndonesianLocation.getVillages(settings.kecamatan_id);
  } else {
    villages.value = [];
  }

  // Explicitly trigger dirty check after revert
  checkDirty();
};

defineExpose({ revertChanges });

watch(() => props.saving, (isSaving, wasSaving) => {
  if (wasSaving && !isSaving) {
    captureInitialState();
  }
});

watch(() => props.level.id, () => {
  captureInitialState();
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

watch(settings, (newSettings) => {
  checkDirty();
  if (props.level) {
    emit('update:level', {
      ...props.level,
      settings: { ...newSettings },
      accreditation: newSettings.accreditation || props.level.accreditation
    });
  }
}, { deep: true });

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
      npwp: '', sk_pendirian: '', tgl_sk_pendirian: '',
      sk_operasional: '', tgl_sk_operasional: '',
      bank_name: '', bank_account_number: '', bank_account_holder: '',
      logo: '',
      logo_dinas: '',
      logo_yayasan: '',
      ...newLevel.settings,
    });
    
    if (newLevel.settings?.provinsi_id) {
      IndonesianLocation.getRegencies(newLevel.settings.provinsi_id).then(res => regencies.value = res);
    }
    if (newLevel.settings?.kabupaten_kota_id) {
      IndonesianLocation.getDistricts(newLevel.settings.kabupaten_kota_id).then(res => districts.value = res);
    }
    if (newLevel.settings?.kecamatan_id) {
      IndonesianLocation.getVillages(newLevel.settings.kecamatan_id).then(res => villages.value = res);
    }
  }
}, { immediate: false });
</script>
