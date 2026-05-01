<template>
  <div class="p-6 max-w-2xl mx-auto">
    <div v-if="isSuperAdmin">
      <div class="mb-8 text-left">
        <router-link
          :to="{ name: 'schools.index' }"
          class="text-sm text-primary hover:underline flex items-center gap-1 mb-4 font-bold"
        >
          <LucideIcon
            name="ArrowLeft"
            class="w-4 h-4"
          />
          {{ $t('common.actions.backTo') }} {{ $t('features.school.title') }}
        </router-link>
        <h2 class="text-3xl font-bold text-foreground tracking-tight">
          {{ $t('features.school.institution.setupNew') }}
        </h2>
        <p class="text-muted-foreground mt-2">
          {{ $t('features.school.institution.setupDescription') }}
        </p>
      </div>

      <!-- Wizard Progress -->
      <div class="flex items-center mb-8 gap-4 px-2">
        <template
          v-for="s in wizardSteps"
          :key="s.num"
        >
          <div class="flex items-center gap-3">
            <div 
              class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-500 shadow-sm"
              :class="step >= s.num ? 'bg-primary text-white ring-4 ring-primary/10' : 'bg-muted text-muted-foreground'"
            >
              {{ s.display }}
            </div>
            <span
              v-if="step === s.num"
              class="font-bold text-xs text-primary animate-in fade-in slide-in-from-left-2"
            >
              {{ s.label }}
            </span>
          </div>
          <div
            v-if="s.display < totalDisplaySteps"
            class="flex-1 h-0.5 max-w-[40px] transition-colors duration-500"
            :class="step > s.num ? 'bg-primary' : 'bg-muted'"
          />
        </template>
      </div>

      <Card class="shadow-sm border-border/40 rounded-2xl overflow-hidden">
        <CardContent class="p-8">
          <!-- Step 1: Basic Information -->
          <div
            v-if="step === 1"
            class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500 text-left"
          >
            <div class="space-y-1">
              <h2 class="text-xl font-bold tracking-tight">
                {{ $t('features.school.wizard.steps.basic') }}
              </h2>
              <p class="text-sm text-muted-foreground">
                {{ $t('features.school.wizard.labels.institutionNameHint') }}
              </p>
            </div>
            <div class="space-y-3">
              <Label for="name">{{ $t('features.school.wizard.labels.institutionName') }}</Label>
              <Input 
                id="name" 
                v-model="form.name" 
                :placeholder="$t('features.school.wizard.labels.institutionNamePlaceholder')" 
                class="text-lg h-14 rounded-xl border-border/50 focus:ring-primary/20"
              />
            </div>
          </div>

          <!-- Step 2: Institution Status -->
          <div
            v-if="step === 2"
            class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500 text-left"
          >
            <div class="space-y-1">
              <h2 class="text-xl font-bold tracking-tight">
                {{ $t('features.school.wizard.labels.statusTitle') }}
              </h2>
              <p class="text-sm text-muted-foreground">
                {{ $t('features.school.wizard.labels.statusSubtitle') }}
              </p>
            </div>
            <div class="grid grid-cols-1 gap-4">
              <div 
                v-for="status in statuses" 
                :key="status.id"
                class="group p-5 border-2 rounded-2xl transition-all flex items-start gap-4 active:scale-[0.98]"
                :class="[
                  form.type === status.id ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5',
                  isStatusDisabled(status.id) ? 'opacity-40 cursor-not-allowed grayscale-[0.5]' : 'cursor-pointer hover:bg-primary/5'
                ]"
                @click="!isStatusDisabled(status.id) && (form.type = status.id)"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="form.type === status.id ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                >
                  <LucideIcon
                    :name="status.icon"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <div class="font-bold text-lg group-hover:text-primary transition-colors">
                    {{ status.name }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ status.description }}
                  </div>
                  <div
                    v-if="isStatusDisabled(status.id)"
                    class="mt-2 text-[10px] font-bold text-destructive/70 bg-destructive/5 px-2 py-0.5 rounded w-fit uppercase"
                  >
                    {{ $t('features.school.wizard.labels.lockedByName') }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Location / Branch -->
          <div
            v-if="step === 3"
            class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500 text-left"
          >
            <div class="space-y-1">
              <h2 class="text-xl font-bold tracking-tight">
                {{ $t('features.school.wizard.labels.locationTitle') }}
              </h2>
              <p class="text-sm text-muted-foreground">
                {{ $t('features.school.wizard.labels.locationSubtitle') }}
              </p>
            </div>
            <div class="grid grid-cols-1 gap-4">
              <div 
                class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex items-start gap-4 active:scale-[0.98]"
                :class="!form.is_multi_branch ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                @click="form.is_multi_branch = false"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="!form.is_multi_branch ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                >
                  <LucideIcon
                    name="MapPin"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <div class="font-bold text-lg">
                    {{ $t('features.school.wizard.options.singleLocation') }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ $t('features.school.wizard.options.singleLocationDesc') }}
                  </div>
                </div>
              </div>

              <div 
                class="p-5 border-2 rounded-2xl transition-all flex items-start gap-4 active:scale-[0.98]"
                :class="[
                  form.type !== 'yayasan' ? 'opacity-40 cursor-not-allowed grayscale-[0.5] border-muted bg-muted/5' : (form.is_multi_branch ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5 cursor-pointer hover:bg-primary/5')
                ]"
                @click="form.type === 'yayasan' && (form.is_multi_branch = true)"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="form.is_multi_branch && form.type === 'yayasan' ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                >
                  <LucideIcon
                    name="Network"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <div class="font-bold text-lg">
                    {{ $t('features.school.wizard.options.multiLocation') }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ $t('features.school.wizard.options.multiLocationDesc') }}
                  </div>
                  <div
                    v-if="form.type !== 'yayasan'"
                    class="mt-2 text-[10px] font-bold text-primary/70 bg-primary/5 px-2 py-0.5 rounded w-fit uppercase"
                  >
                    {{ $t('features.school.wizard.labels.onlyForFoundations') }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Level Configuration -->
          <div
            v-if="step === 4"
            class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500 text-left"
          >
            <!-- Negeri / Swasta: select level directly -->
            <template v-if="form.type === 'negeri' || form.type === 'swasta'">
              <div class="space-y-1">
                <h2 class="text-xl font-bold tracking-tight">
                  {{ $t('features.school.wizard.labels.levelTitle') }}
                </h2>
                <p class="text-sm text-muted-foreground">
                  {{ $t('features.school.wizard.labels.levelSubtitle') }}
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div 
                  v-for="lv in levelOptions" 
                  :key="lv.id"
                  class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex flex-col items-center gap-3 text-center active:scale-[0.98]"
                  :class="form.initial_level === lv.id ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                  @click="form.initial_level = lv.id; form.initial_level_name = lv.name"
                >
                  <div 
                    class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                    :class="form.initial_level === lv.id ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                  >
                    <LucideIcon
                      :name="lv.icon"
                      class="w-6 h-6"
                    />
                  </div>
                  <div>
                    <div class="font-bold">
                      {{ lv.name }}
                    </div>
                    <div class="text-[11px] text-muted-foreground mt-0.5">
                      {{ lv.description }}
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Foundation: choose single/multi level -->
            <template v-else>
              <div class="space-y-1">
                <h2 class="text-xl font-bold tracking-tight">
                  {{ $t('features.school.levels.title') }}
                </h2>
                <p class="text-sm text-muted-foreground">
                  {{ $t('features.school.levels.subtitle') }}
                </p>
              </div>

              <div class="grid grid-cols-1 gap-4">
                <div 
                  class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex items-start gap-4 active:scale-[0.98]"
                  :class="!form.is_multi_level ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                  @click="form.is_multi_level = false"
                >
                  <div 
                    class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                    :class="!form.is_multi_level ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                  >
                    <LucideIcon
                      name="Layers"
                      class="w-6 h-6"
                    />
                  </div>
                  <div>
                    <div class="font-bold text-lg">
                      {{ $t('common.labels.single_level') }}
                    </div>
                    <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                      {{ $t('features.school.levels.singleLevelInfo.description') }}
                    </div>
                  </div>
                </div>

                <div 
                  class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex items-start gap-4 active:scale-[0.98]"
                  :class="form.is_multi_level ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                  @click="form.is_multi_level = true"
                >
                  <div 
                    class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                    :class="form.is_multi_level ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                  >
                    <LucideIcon
                      name="LayoutGrid"
                      class="w-6 h-6"
                    />
                  </div>
                  <div>
                    <div class="font-bold text-lg">
                      {{ $t('common.labels.multi_level') }}
                    </div>
                    <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                      {{ $t('features.school.levels.subtitle') }}
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between items-center mt-12 pt-8 border-t border-border/40">
            <Button 
              v-if="step > 1" 
              variant="ghost" 
              :disabled="loading"
              class="rounded-xl h-11 px-6 font-bold"
              @click="prevStep"
            >
              <LucideIcon
                name="ArrowLeft"
                class="w-4 h-4 mr-2"
              />
              {{ $t('features.school.wizard.actions.prev') }}
            </Button>
            <div v-else />

            <Button 
              v-if="!isLastStep" 
              :disabled="!isValidStep"
              class="rounded-xl h-11 px-8 font-bold bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20"
              @click="nextStep"
            >
              {{ $t('features.school.wizard.actions.next') }}
              <LucideIcon
                name="ChevronRight"
                class="w-4 h-4 ml-2"
              />
            </Button>
            
            <Button 
              v-else 
              :disabled="loading || !isValidStep" 
              class="rounded-xl h-11 px-10 font-bold bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95"
              @click="submit"
            >
              <LucideIcon
                v-if="loading"
                name="Loader2"
                class="w-4 h-4 mr-2 animate-spin"
              />
              <LucideIcon
                v-else
                name="CircleCheck"
                class="w-4 h-4 mr-2"
              />
              {{ loading ? $t('features.school.wizard.actions.processing') : $t('features.school.wizard.actions.finish') }}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
    <div
      v-else
      class="max-w-md mx-auto pt-10 animate-in fade-in slide-in-from-bottom-4 duration-500"
    >
      <Card class="border-destructive/20 shadow-xl overflow-hidden">
        <div class="bg-destructive/5 p-8 text-center border-b border-destructive/10">
          <LucideIcon
            name="ShieldAlert"
            class="w-16 h-16 text-destructive mx-auto mb-4"
          />
          <h3 class="text-2xl font-bold text-destructive">
            {{ $t('common.errors.accessDenied') }}
          </h3>
          <p class="text-muted-foreground mt-4 leading-relaxed">
            {{ $t('features.school.rbac.noPermissionSetup') }}
          </p>
        </div>
        <CardContent class="p-8 text-center pt-6">
          <Button
            as-child
            variant="ghost"
            class="font-bold"
          >
            <router-link :to="{ name: 'schools.index' }">
              <LucideIcon
                name="ArrowLeft"
                class="w-4 h-4 mr-2"
              />
              {{ $t('common.actions.backTo') }} Dashboard
            </router-link>
          </Button>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '../../../stores/school';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useToast } from '@/composables/useToast';
import {
  Card, CardContent, Button, Input, Label, LucideIcon
} from '@/components/ui';

const router = useRouter();
const schoolStore = useSchoolStore();
const authStore = useAuthStore();
const toast = useToast();
const { t } = useI18n();
const loading = computed(() => schoolStore.loading);

const isSuperAdmin = computed(() => authStore.isAtLeastRole('super-admin'));
const step = ref(1);
const form = reactive({
  name: '',
  type: 'swasta',
  is_multi_level: false,
  is_multi_branch: false,
  initial_level: '' as string,
  initial_level_name: '' as string,
});

const levelOptions = computed(() => [
  { id: 'sd', name: t('features.school.wizard.options.sd'), icon: 'Baby', description: t('features.school.wizard.options.sdDesc') },
  { id: 'smp', name: t('features.school.wizard.options.smp'), icon: 'GraduationCap', description: t('features.school.wizard.options.smpDesc') },
  { id: 'sma', name: t('features.school.wizard.options.sma'), icon: 'BookOpen', description: t('features.school.wizard.options.smaDesc') },
  { id: 'smk', name: t('features.school.wizard.options.smk'), icon: 'Cpu', description: t('features.school.wizard.options.smkDesc') },
]);

// Helper to detect type from name
const detectedType = computed(() => {
  if (!form.name) return 'swasta';
  const nameLower = form.name.toLowerCase().trim();
  
  // Rule: Starts with "yayasan"
  if (nameLower.startsWith('yayasan')) return 'yayasan';
  
  // Rule: Contains "negeri" keywords
  if (nameLower.includes('negeri') || /\b(sdn|smpn|sman|smkn)\b/.test(nameLower)) {
    return 'negeri';
  }
  
  return 'swasta';
});

// Helper to check if a status should be disabled
const isStatusDisabled = (statusId: string) => {
  if (!form.name) return false;
  return statusId !== detectedType.value;
};

// Auto-suggest level & type based on name
watch(() => form.name, (newName) => {
  if (!newName) return;
  
  // Update type automatically
  form.type = detectedType.value;
  
  // Reset multi-branch if not yayasan
  if (form.type !== 'yayasan') {
    form.is_multi_branch = false;
  }

  const nameLower = newName.toLowerCase();
  
  // 2. Suggest Level
  let detectedLevel = '';
  let detectedName = '';

  if (nameLower.includes('smk')) {
    detectedLevel = 'smk';
    detectedName = t('features.school.wizard.options.smk');
  } else if (nameLower.includes('sma') || nameLower.includes(' ma ') || nameLower.endsWith(' ma')) {
    detectedLevel = 'sma';
    detectedName = t('features.school.wizard.options.sma');
  } else if (nameLower.includes('smp') || nameLower.includes('mts')) {
    detectedLevel = 'smp';
    detectedName = t('features.school.wizard.options.smp');
  } else if (nameLower.includes('sd ') || nameLower.includes('mi ') || nameLower.startsWith('sd') || nameLower.startsWith('mi')) {
    detectedLevel = 'sd';
    detectedName = t('features.school.wizard.options.sd');
  }

  if (detectedLevel) {
    form.initial_level = detectedLevel;
    form.initial_level_name = detectedName;
  }
});

const statuses = computed(() => [
  { id: 'negeri', name: t('features.school.institution.status.negeri'), icon: 'ShieldCheck', description: t('features.school.institution.status.negeri') },
  { id: 'swasta', name: t('features.school.institution.status.swasta'), icon: 'Building', description: t('features.school.institution.status.swasta') },
  { id: 'yayasan', name: t('features.school.institution.status.yayasan'), icon: 'Component', description: t('features.school.institution.status.yayasan') },
]);

const isValidStep = computed(() => {
  if (step.value === 1) return form.name.length >= 3;
  if (step.value === 2) return !!form.type;
  if (step.value === 4 && (form.type === 'negeri' || form.type === 'swasta')) {
    return !!form.initial_level;
  }
  return true;
});

// Whether to show step 3 (multi-location) — only for yayasan
const showLocationStep = computed(() => form.type === 'yayasan');

// Whether current step is the last step — always step 4 internally
const isLastStep = computed(() => step.value === 4);

// Dynamic wizard steps for progress indicator
const wizardSteps = computed(() => {
  if (showLocationStep.value) {
    return [
      { num: 1, display: 1, label: t('features.school.wizard.steps.basic') },
      { num: 2, display: 2, label: t('features.school.wizard.steps.status') },
      { num: 3, display: 3, label: t('features.school.wizard.steps.location') },
      { num: 4, display: 4, label: t('features.school.wizard.steps.level') },
    ];
  }
  // negeri/swasta: skip lokasi, remap step 4 → display 3
  return [
    { num: 1, display: 1, label: t('features.school.wizard.steps.basic') },
    { num: 2, display: 2, label: t('features.school.wizard.steps.status') },
    { num: 4, display: 3, label: t('features.school.wizard.steps.level') },
  ];
});

const totalDisplaySteps = computed(() => wizardSteps.value.length);

const nextStep = () => {
  if (isValidStep.value) {
    if (step.value === 2 && !showLocationStep.value) {
      // negeri/swasta: skip lokasi (step 3), go straight to jenjang (step 4)
      form.is_multi_branch = false;
      if (form.type === 'negeri' || form.type === 'swasta') form.is_multi_level = false;
      step.value = 4;
    } else {
      step.value++;
    }
  }
};

const prevStep = () => {
  if (step.value === 4 && !showLocationStep.value) {
    // negeri/swasta: skip back over lokasi (step 3), go to status (step 2)
    step.value = 2;
  } else {
    step.value--;
  }
};

const submit = async () => {
  try {
    await schoolStore.createSchool(form as any);
    toast.success.action(t('features.school.levels.updateSuccess')); // Generic success for now
    router.push({ name: 'schools.index' });
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};
</script>
