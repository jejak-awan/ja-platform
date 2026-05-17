<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold tracking-tight text-foreground">
          {{ $t('modules.school.institution.settings.institution.title') }}
        </h2>
        <p class="text-muted-foreground">
          {{ $t('modules.school.institution.settings.institution.description') }}
        </p>
      </div>
      <Button 
        :disabled="saving || !hasChanges" 
        class="relative overflow-hidden group shadow-lg shadow-primary/20"
        @click="handleSave"
      >
        <div v-if="saving" class="flex items-center gap-2">
          <Loader2 class="w-4 h-4 animate-spin" />
          <span>{{ $t('common.actions.saving') }}</span>
        </div>
        <div v-else class="flex items-center gap-2">
          <Save class="w-4 h-4 transition-transform group-hover:scale-110" />
          <span>{{ $t('common.actions.saveChanges') }}</span>
        </div>
      </Button>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <!-- School Identity Card -->
      <Card class="border-border/40 bg-card/50 backdrop-blur-sm">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Building2 class="w-5 h-5 text-primary" />
            {{ $t('modules.school.institution.settings.institution.identity_section') }}
          </CardTitle>
          <CardDescription>
            {{ $t('modules.school.institution.settings.institution.identity_description') }}
          </CardDescription>
        </CardHeader>
        <CardContent class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2 md:col-span-2">
            <Label for="school_name">{{ $t('modules.school.labels.institutionName') }}</Label>
            <Input 
              id="school_name" 
              v-model="form.name" 
              :placeholder="$t('modules.school.institution.settings.institution.fields.name.placeholder')"
              class="bg-background/50 border-border/60 focus:border-primary/50 transition-colors font-semibold"
            />
          </div>

          <div class="space-y-2">
            <Label for="npsn">{{ $t('modules.school.labels.npsn') }}</Label>
            <Input 
              id="npsn" 
              v-model="form.npsn" 
              :placeholder="$t('modules.school.labels.npsn_placeholder')"
              class="bg-background/50 border-border/60 focus:border-primary/50 transition-colors"
            />
          </div>

          <div class="space-y-2">
            <Label for="admin_email">{{ $t('common.labels.email') }}</Label>
            <Input 
              id="admin_email" 
              v-model="form.email" 
              type="email"
              :placeholder="$t('modules.school.institution.settings.institution.fields.email.placeholder')"
              class="bg-background/50 border-border/60 focus:border-primary/50 transition-colors"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Logo & Branding Card -->
      <Card class="border-border/40 bg-card/50 backdrop-blur-sm">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <ImageIcon class="w-5 h-5 text-primary" />
            {{ $t('modules.school.institution.settings.institution.branding_section') }}
          </CardTitle>
          <CardDescription>
            {{ $t('modules.school.institution.settings.institution.branding_description') }}
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="flex flex-col items-center gap-4">
            <div class="relative group">
              <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-border/60 flex items-center justify-center overflow-hidden bg-muted/30 group-hover:border-primary/50 transition-all duration-300">
                <img v-if="logoPreview || form.logo" :src="logoPreview || form.logo" class="w-full h-full object-contain" />
                <Building2 v-else class="w-12 h-12 text-muted-foreground/40" />
              </div>
              <label 
                for="logo-upload" 
                class="absolute inset-0 flex items-center justify-center bg-background/80 opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity duration-300 rounded-2xl"
              >
                <div class="flex flex-col items-center gap-1">
                  <Upload class="w-5 h-5 text-primary" />
                  <span class="text-[10px] font-bold uppercase tracking-wider">{{ $t('common.actions.change') }}</span>
                </div>
              </label>
              <input id="logo-upload" type="file" class="hidden" accept="image/*" @change="handleLogoChange" />
            </div>
            <p class="text-xs text-center text-muted-foreground max-w-[200px]">
              {{ $t('modules.school.institution.settings.institution.logo_hint') }}
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- Contact & Address Card -->
      <Card class="border-border/40 bg-card/50 backdrop-blur-sm md:col-span-2">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <MapPin class="w-5 h-5 text-primary" />
            {{ $t('modules.school.institution.settings.institution.contact_section') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="grid gap-6 md:grid-cols-2">
          <div class="space-y-2">
            <Label for="school_phone">{{ $t('modules.school.institution.settings.institution.fields.phone.label') }}</Label>
            <Input id="school_phone" v-model="form.phone" class="bg-background/50" />
          </div>
          <div class="space-y-2">
            <Label for="school_address">{{ $t('modules.school.institution.settings.institution.fields.address.label') }}</Label>
            <textarea 
              id="school_address" 
              v-model="form.address"
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            ></textarea>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useSchoolStore } from '@/modules/School/stores/school';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/shared/components/ui';
import { Button } from '@/shared/components/ui';
import { Input } from '@/shared/components/ui';
import { Label } from '@/shared/components/ui';
import { Building2, Save, ImageIcon, Upload, MapPin, Loader2 } from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast';

const schoolStore = useSchoolStore();
const { success, error: toastError } = useToast();

const saving = ref(false);
const logoPreview = ref<string | null>(null);
const logoFile = ref<File | null>(null);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  logo: '',
  npsn: '',
});

const originalData = ref<any>(null);

const hasChanges = computed(() => {
  if (!originalData.value) return false;
  return JSON.stringify(form) !== JSON.stringify(originalData.value) || logoFile.value !== null;
});

const handleLogoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
  }
};

const handleSave = async () => {
  saving.value = true;
  try {
    // 1. Upload logo if changed
    if (logoFile.value) {
      await schoolStore.uploadLogo(logoFile.value);
    }

    // 2. Update institution data
    await schoolStore.saveInstitution({
      name: form.name,
      email: form.email,
      phone: form.phone,
      address: form.address,
      npsn: form.npsn,
    });

    success.save();
    
    // Update baseline
    originalData.value = JSON.parse(JSON.stringify(form));
    logoFile.value = null;
  } catch (error) {
    toastError.action(error);
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  if (!schoolStore.currentSchool) {
    await schoolStore.fetchSchool();
  }
  
  if (schoolStore.currentSchool) {
    const s = schoolStore.currentSchool;
    form.name = s.name || '';
    form.email = s.email || '';
    form.phone = s.phone || '';
    form.address = s.address || '';
    form.logo = s.logo || '';
    form.npsn = s.npsn || '';
    
    originalData.value = JSON.parse(JSON.stringify(form));
  }
});
</script>
