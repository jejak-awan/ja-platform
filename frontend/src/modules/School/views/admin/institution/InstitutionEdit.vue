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
        {{ $t('common.actions.edit') }} {{ $t('modules.school.institution.settings.institution.title') }}
      </h2>
      <p class="text-muted-foreground mt-2">
        {{ $t('modules.school.institution.subtitle') }}
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
                {{ $t('modules.school.wizard.steps.basic') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('modules.school.wizard.labels.institutionNameHint') }}
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
                {{ $t('modules.school.wizard.labels.statusTitle') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('modules.school.wizard.labels.statusSubtitle') }}
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
                    {{ $t('modules.school.wizard.labels.lockedByName') }}
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
                {{ $t('modules.school.wizard.labels.locationTitle') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('modules.school.wizard.labels.locationSubtitle') }}
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
                    {{ $t('modules.school.wizard.options.singleLocation') }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ $t('modules.school.wizard.options.singleLocationDesc') }}
                  </div>
                </div>
              </div>

              <div 
                class="p-5 border-2 rounded-2xl transition-all flex items-start gap-4 active:scale-[0.98]"
                :class="[
                  form.type !== 'swasta' ? 'opacity-40 cursor-not-allowed grayscale-[0.5] border-muted bg-muted/5' : (form.is_multi_branch ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5 cursor-pointer hover:bg-primary/5')
                ]"
                @click="form.type === 'swasta' && (form.is_multi_branch = true)"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="form.is_multi_branch && form.type === 'swasta' ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                >
                  <LucideIcon
                    name="Network"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <div class="font-bold text-lg">
                    {{ $t('modules.school.wizard.options.multiLocation') }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ $t('modules.school.wizard.options.multiLocationDesc') }}
                  </div>
                  <div
                    v-if="form.type !== 'swasta'"
                    class="mt-2 text-[10px] font-bold text-primary/70 bg-primary/5 px-2 py-0.5 rounded w-fit uppercase"
                  >
                    {{ $t('modules.school.wizard.labels.onlyForPrivate') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Section 4: Unit Configuration (Multi/Single Level) -->
      <Card 
        v-if="form.type === 'swasta'"
        class="shadow-sm border-border/40 rounded-2xl overflow-hidden"
      >
        <CardContent class="p-8">
          <div class="space-y-6 text-left">
            <div class="space-y-1">
              <h3 class="text-lg font-bold tracking-tight">
                {{ $t('modules.school.units.title') }}
              </h3>
              <p class="text-xs text-muted-foreground">
                {{ $t('modules.school.units.subtitle') }}
              </p>
            </div>
            <div class="grid grid-cols-1 gap-4">
              <div 
                class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex items-start gap-4 active:scale-[0.98]"
                :class="!form.is_multi_unit ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                @click="form.is_multi_unit = false"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="!form.is_multi_unit ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
                >
                  <LucideIcon
                    name="Layers"
                    class="w-6 h-6"
                  />
                </div>
                <div>
                  <div class="font-bold text-lg">
                    {{ $t('modules.school.labels.single_level') }}
                  </div>
                  <div class="text-xs text-muted-foreground mt-1 leading-relaxed">
                    {{ $t('modules.school.units.singleLevelInfo.description') }}
                  </div>
                </div>
              </div>

              <div 
                class="p-5 border-2 rounded-2xl cursor-pointer transition-all hover:bg-primary/5 flex items-start gap-4 active:scale-[0.98]"
                :class="form.is_multi_unit ? 'border-primary bg-primary/5 shadow-md shadow-primary/5' : 'border-muted bg-muted/5'"
                @click="form.is_multi_unit = true"
              >
                <div 
                  class="p-3 rounded-xl shadow-sm border border-muted transition-colors"
                  :class="form.is_multi_unit ? 'bg-primary text-white border-primary/20' : 'bg-white text-primary'"
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
                    {{ $t('modules.school.units.subtitle') }}
                  </div>
                </div>
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
          {{ $t('modules.school.rbac.noPermissionEdit') }}
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

    <!-- Save Confirmation Modal -->
    <Dialog v-model:open="showSaveConfirm">
      <DialogContent class="sm:max-w-[500px] !z-[100050] p-0 overflow-hidden border-none shadow-2xl text-left">
        <DialogHeader class="bg-primary/5 p-8 text-center border-b border-primary/10">
          <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4 ring-8 ring-primary/5">
            <LucideIcon
              name="Save"
              class="w-8 h-8"
            />
          </div>
          <DialogTitle class="text-2xl font-bold text-foreground mb-2">
            {{ $t('common.actions.saveChanges') }}?
          </DialogTitle>
          <DialogDescription class="text-muted-foreground leading-relaxed">
            {{ $t('modules.school.institution.saveStrictWarning') }}
          </DialogDescription>
        </DialogHeader>

        <div class="p-8 space-y-6 text-left">
          <div class="space-y-3">
            <Label
              for="saveConfirmName"
              class="text-sm font-bold text-foreground"
            >
              {{ $t('modules.school.institution.saveStrictConfirmPrompt') }}
            </Label>
            <div class="bg-muted/50 p-3 rounded-lg border border-border/50 font-mono text-sm text-center font-bold text-primary">
              {{ schoolStore.currentSchool?.name }}
            </div>
            <Input 
              id="saveConfirmName"
              v-model="saveConfirmName"
              :placeholder="$t('modules.school.institution.deleteStrictPlaceholder')"
              class="h-12 rounded-xl border-primary/20 focus:ring-primary/20 focus:border-primary/30"
              @keyup.enter="saveConfirmName === schoolStore.currentSchool?.name && executeSubmit()"
            />
          </div>

          <DialogFooter class="flex flex-col sm:flex-row gap-3 sm:gap-0 mt-2">
            <Button 
              variant="ghost" 
              class="rounded-xl h-12 font-bold px-6" 
              :disabled="loading"
              @click="showSaveConfirm = false"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button 
              class="rounded-xl h-12 font-bold px-10 bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95 transition-all"
              :disabled="saveConfirmName !== schoolStore.currentSchool?.name || loading"
              @click="executeSubmit"
            >
              <LucideIcon
                v-if="loading"
                name="Loader2"
                class="w-4 h-4 mr-2 animate-spin"
              />
              <LucideIcon
                v-else
                name="Check"
                class="w-4 h-4 mr-2"
              />
              {{ loading ? $t('common.actions.saving') : $t('common.actions.confirm') }}
            </Button>
          </DialogFooter>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '../../../stores/school';
import { useAuthStore } from '@/modules/System/stores/auth';
import { useToast } from '@/shared/composables/useToast';
import {
  Card, CardContent, Button, Input, Label, Textarea, LucideIcon,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/shared/components/ui';

const router = useRouter();
const route = useRoute();
const schoolStore = useSchoolStore();
const authStore = useAuthStore();
const toast = useToast();
const { t } = useI18n();

const loading = computed(() => schoolStore.loading);
const isSuperAdmin = computed(() => authStore.isAtLeastRole('super'));

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
  is_multi_unit: false,
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
  
  if (nameLower.includes('negeri') || /\b(sdn|smpn|sman|smkn)\b/.test(nameLower)) {
    return 'negeri';
  }
  
  // Everything else is swasta (including yayasan)
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
    name: t('modules.school.institution.status.negeri'), 
    description: t('modules.school.wizard.labels.statusNegeriDesc'), 
    icon: 'ShieldCheck' 
  },
  { 
    id: 'swasta', 
    name: t('modules.school.institution.status.swasta'), 
    description: t('modules.school.wizard.labels.statusSwastaDescCombined'), 
    icon: 'Building' 
  },
]);

/**
 * Watch name to auto-update type and location setting.
 */
watch(() => form.name, (newName) => {
  if (!newName) return;
  
  form.type = detectedType.value;
  
  if (form.type !== 'swasta') {
    form.is_multi_branch = false;
    form.is_multi_unit = false;
  }
});

/**
 * Fetch existing school data on mount.
 */
onMounted(async () => {
  const id = route.params.id as string;
  await schoolStore.fetchSchool(id);
  if (schoolStore.currentSchool) {
    Object.assign(form, schoolStore.currentSchool);
  }
});

const showSaveConfirm = ref(false);
const saveConfirmName = ref('');

/**
 * Primary save action.
 */
const submit = async () => {
  if (!form.name) {
    toast.error.action(t('modules.school.units.errors.nameRequired'));
    return;
  }
  saveConfirmName.value = '';
  showSaveConfirm.value = true;
};

const executeSubmit = async () => {
  if (saveConfirmName.value !== schoolStore.currentSchool?.name) {
    toast.error.action(t('modules.school.units.errors.nameRequired'));
    return;
  }

  try {
    const id = route.params.id as string;
    await schoolStore.updateSchool(id, form as any);
    toast.success.save();
    showSaveConfirm.value = false;
    router.push({ name: 'schools.index' });
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};
</script>
"
,Complexity:5,Description:
