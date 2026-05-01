<template>
  <div class="p-6 max-w-2xl mx-auto">
    <div class="mb-10 text-left">
      <router-link
        :to="{ name: 'schools.index' }"
        class="text-sm text-primary hover:underline flex items-center gap-1 mb-4 font-bold"
      >
        <LucideIcon
          name="ArrowLeft"
          class="w-4 h-4"
        />
        {{ $t('common.actions.backTo') }} Dashboard
      </router-link>
      <h2 class="text-3xl font-bold text-foreground tracking-tight">
        {{ $t('common.actions.edit') }} {{ $t('features.school.institution.title') }}
      </h2>
      <p class="text-muted-foreground mt-2">
        {{ $t('features.school.institution.subtitle') }}
      </p>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading && !form.name"
      class="flex flex-col items-center justify-center py-20 text-muted-foreground animate-pulse"
    >
      <LucideIcon
        name="Loader2"
        class="w-10 h-10 animate-spin mb-4 text-primary"
      />
      <p class="font-bold text-xs">
        {{ $t('common.labels.loading') }}...
      </p>
    </div>

    <!-- Edit Form -->
    <div
      v-else
      class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700"
    >
      <!-- Section 1: Basic Identity -->
      <Card class="shadow-sm border-border/40 rounded-2xl overflow-hidden">
        <CardContent class="p-8">
          <div class="space-y-6 text-left">
            <div class="space-y-1">
              <h3 class="text-lg font-bold tracking-tight">
                {{ $t('features.school.wizard.steps.basic') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('features.school.wizard.labels.institutionNameHint') }}
              </p>
            </div>
            <div class="space-y-2">
              <Label for="name">{{ $t('common.labels.institutionName') }}</Label>
              <Input
                id="name"
                v-model="form.name"
                required
                class="h-12 rounded-xl border-border/50 focus:ring-primary/20 font-bold text-lg"
              />
            </div>
            <div class="space-y-2">
              <Label for="address">{{ $t('common.labels.fullAddress') }}</Label>
              <Textarea
                id="address"
                v-model="form.address"
                rows="3"
                class="rounded-xl border-border/50 focus:ring-primary/20 min-h-[100px]"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Section 2: Institution Status -->
      <Card class="shadow-sm border-border/40 rounded-2xl overflow-hidden">
        <CardContent class="p-8">
          <div class="space-y-6 text-left">
            <div class="space-y-1">
              <h3 class="text-lg font-bold tracking-tight">
                {{ $t('features.school.wizard.labels.statusTitle') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('features.school.wizard.labels.statusSubtitle') }}
              </p>
            </div>
            <div class="grid grid-cols-1 gap-4">
              <div 
                v-for="status in statusOptions" 
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
        </CardContent>
      </Card>

      <!-- Section 3: Operational Configuration -->
      <Card class="shadow-sm border-border/40 rounded-2xl overflow-hidden">
        <CardContent class="p-8">
          <div class="space-y-6 text-left">
            <div class="space-y-1">
              <h3 class="text-lg font-bold tracking-tight">
                {{ $t('features.school.wizard.labels.locationTitle') }}
              </h3>
              <p class="text-xs text-muted-foreground">
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
        </CardContent>
      </Card>

      <!-- Section 4: Contact Information -->
      <Card class="shadow-xl border-border/40 rounded-2xl overflow-hidden">
        <CardContent class="p-8">
          <div class="space-y-6 text-left">
            <h3 class="text-lg font-bold tracking-tight">
              {{ $t('common.labels.locationContact') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="phone">{{ $t('common.labels.phoneNumber') }}</Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  class="h-12 rounded-xl border-border/50 focus:ring-primary/20 font-medium"
                />
              </div>

              <div class="space-y-2">
                <Label for="email">{{ $t('common.labels.email') }}</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="h-12 rounded-xl border-border/50 focus:ring-primary/20 font-medium"
                />
              </div>

              <div class="space-y-2 md:col-span-2">
                <Label for="website">{{ $t('common.labels.url') }}</Label>
                <Input
                  id="website"
                  v-model="form.website"
                  type="url"
                  class="h-12 rounded-xl border-border/50 focus:ring-primary/20 font-medium"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div
        v-if="isSuperAdmin"
        class="pt-6 border-t border-border/40 flex justify-end gap-3 sticky bottom-0 bg-background/80 backdrop-blur-sm p-4 rounded-xl shadow-lg z-10"
      >
        <Button
          variant="ghost"
          as-child
          class="rounded-xl h-11 px-6 font-bold"
        >
          <router-link :to="{ name: 'schools.index' }">
            {{ $t('common.actions.cancel') }}
          </router-link>
        </Button>
        <Button 
          :disabled="loading" 
          class="rounded-xl h-11 px-10 bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95 font-bold transition-all" 
          @click="submit"
        >
          <LucideIcon
            v-if="loading"
            name="Loader2"
            class="w-4 h-4 mr-2 animate-spin"
          />
          <LucideIcon
            v-else
            name="Save"
            class="w-4 h-4 mr-2"
          />
          {{ loading ? 'Saving...' : $t('common.actions.saveChanges') }}
        </Button>
      </div>
      <div
        v-else
        class="p-8 text-center bg-destructive/5 rounded-2xl border-2 border-dashed border-destructive/20 animate-in fade-in zoom-in duration-500"
      >
        <LucideIcon
          name="ShieldAlert"
          class="w-12 h-12 text-destructive mx-auto mb-4"
        />
        <h3 class="text-xl font-bold text-destructive">
          {{ $t('common.errors.accessDenied') }}
        </h3>
        <p class="text-muted-foreground mt-2 max-w-sm mx-auto">
          {{ $t('features.school.rbac.noPermissionEdit') }}
        </p>
        <Button
          as-child
          variant="outline"
          class="mt-6 rounded-xl font-bold border-destructive/20 text-destructive hover:bg-destructive/10"
        >
          <router-link :to="{ name: 'schools.index' }">
            {{ $t('common.actions.backTo') }} Dashboard
          </router-link>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '../../../stores/school';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useToast } from '@/composables/useToast';
import {
  Card, CardContent, Button, Input, Label, Textarea, LucideIcon
} from '@/components/ui';

const router = useRouter();
const route = useRoute();
const schoolStore = useSchoolStore();
const authStore = useAuthStore();
const toast = useToast();
const { t } = useI18n();

const loading = computed(() => schoolStore.loading);
const isSuperAdmin = computed(() => authStore.isAtLeastRole('super-admin'));

/**
 * Form state for institutional basic profile.
 */
const form = reactive({
  name: '',
  address: '',
  phone: '',
  email: '',
  website: '',
  type: 'swasta',
  is_multi_branch: false,
});

/**
 * Helper to detect type from name.
 * - Starts with 'yayasan' -> yayasan
 * - Contains negeri keywords -> negeri
 * - Else -> swasta
 */
const detectedType = computed(() => {
  if (!form.name) return 'swasta';
  const nameLower = form.name.toLowerCase().trim();
  
  if (nameLower.startsWith('yayasan')) return 'yayasan';
  
  if (nameLower.includes('negeri') || /\b(sdn|smpn|sman|smkn)\b/.test(nameLower)) {
    return 'negeri';
  }
  
  return 'swasta';
});

/**
 * Helper to check if a status should be disabled.
 */
const isStatusDisabled = (statusId: string) => {
  if (!form.name) return false;
  return statusId !== detectedType.value;
};

/**
 * Institution status options consistent with setup wizard.
 */
const statusOptions = computed(() => [
  { 
    id: 'negeri', 
    name: t('features.school.institution.status.negeri'), 
    description: t('features.school.wizard.labels.statusNegeriDesc'), 
    icon: 'ShieldCheck' 
  },
  { 
    id: 'yayasan', 
    name: t('features.school.institution.status.yayasan'), 
    description: t('features.school.wizard.labels.statusYayasanDesc'), 
    icon: 'Component' 
  },
  { 
    id: 'swasta', 
    name: t('features.school.institution.status.swasta'), 
    description: t('features.school.wizard.labels.statusSwastaDesc'), 
    icon: 'Building' 
  },
]);

/**
 * Watch name to auto-update type and location setting.
 */
watch(() => form.name, (newName) => {
  if (!newName) return;
  
  form.type = detectedType.value;
  
  if (form.type !== 'yayasan') {
    form.is_multi_branch = false;
  }
});

/**
 * Fetch existing school data on mount.
 */
onMounted(async () => {
  const id = Number(route.params.id);
  await schoolStore.fetchSchool(id);
  if (schoolStore.currentSchool) {
    Object.assign(form, schoolStore.currentSchool);
  }
});

/**
 * Primary save action.
 */
const submit = async () => {
  if (!form.name) {
    toast.error.action(t('features.school.levels.errors.nameRequired'));
    return;
  }

  try {
    const id = Number(route.params.id);
    await schoolStore.updateSchool(id, form as any);
    toast.success.save();
    router.push({ name: 'schools.index' });
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};
</script>
"
,Complexity:5,Description:
