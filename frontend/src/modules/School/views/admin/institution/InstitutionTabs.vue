<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-8 text-left">
      <h2 class="text-3xl font-bold tracking-tight text-foreground">
        {{ $t('features.school.title') }}
      </h2>
      <p class="text-muted-foreground mt-1">
        {{ $t('features.school.subtitle') }}
      </p>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex justify-center p-16"
    >
      <LucideIcon
        name="Loader2"
        class="w-10 h-10 animate-spin text-primary"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!school"
      class="p-16 border-2 border-dashed rounded-2xl text-center space-y-5 bg-muted/20 border-border/50 animate-in fade-in duration-500"
    >
      <div class="p-4 bg-primary/10 text-primary rounded-full w-fit mx-auto ring-8 ring-primary/5">
        <LucideIcon
          name="Building"
          class="w-10 h-10"
        />
      </div>
      <h3 class="text-2xl font-bold text-foreground tracking-tight">
        {{ $t('features.school.institution.empty') }}
      </h3>
      <p class="text-muted-foreground max-w-md mx-auto leading-relaxed">
        {{ $t('features.school.institution.emptyDescription') }}
      </p>
      <Button
        v-if="isSuperAdmin"
        as-child
        class="rounded-xl px-8 h-12 font-bold shadow-lg shadow-primary/20 text-base"
      >
        <router-link :to="{ name: 'schools.create' }">
          <LucideIcon
            name="Plus"
            class="w-5 h-5 mr-2"
          />
          {{ $t('features.school.institution.setupNow') }}
        </router-link>
      </Button>
      <div
        v-else
        class="text-sm text-muted-foreground italic"
      >
        {{ $t('features.school.rbac.noPermissionSetup') }}
      </div>
    </div>

    <!-- Main Content -->
    <div
      v-else
      class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500"
    >
      <!-- Institution Header Card -->
      <Card class="overflow-hidden border-border/50 shadow-sm hover:shadow-md transition-shadow duration-300 rounded-2xl">
        <div class="bg-muted/30 p-6 border-b border-border/40 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-left">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-background rounded-xl shadow-sm border border-border/50 flex items-center justify-center">
              <LucideIcon
                :name="getSchoolIcon(school.type || '')"
                class="w-8 h-8 text-primary"
              />
            </div>
            <div>
              <h2 class="text-xl font-bold text-foreground leading-tight">
                {{ school.name }}
              </h2>
              <div class="text-sm text-muted-foreground flex items-center gap-2 mt-1">
                <Badge
                  variant="secondary"
                  class="capitalize font-semibold text-[10px] px-2 h-5"
                >
                  {{ $t(`features.school.institution.status.${school?.type?.toLowerCase() || 'undefined'}`) }}
                </Badge>
                <span class="opacity-30">•</span>
                <span class="text-xs">{{ school.is_multi_level ? $t('common.labels.multi_level') : $t('common.labels.single_level') }}</span>
                <span class="opacity-30">•</span>
                <span class="text-xs">{{ school.is_multi_branch ? $t('features.school.wizard.options.multiLocation') : $t('features.school.wizard.options.singleLocation') }}</span>
              </div>
            </div>
          </div>
          <div
            v-if="isSuperAdmin"
            class="flex gap-2 w-full sm:w-auto"
          >
            <Button
              variant="outline"
              as-child
              size="sm"
              class="rounded-xl h-9 flex-1 sm:flex-none font-bold"
            >
              <router-link
                v-if="school && school.id"
                :to="{ name: 'schools.edit', params: { id: school.id } }"
              >
                <LucideIcon
                  name="Edit2"
                  class="w-3.5 h-3.5 mr-2 opacity-70"
                />
                {{ $t('common.actions.editProfile') }}
              </router-link>
            </Button>
            <Button 
              variant="outline"
              size="sm" 
              :disabled="isInstitutionDeleteRestricted"
              class="rounded-xl h-9 flex-1 sm:flex-none text-destructive hover:bg-destructive/5 hover:text-destructive border-border/50 font-bold" 
              @click="confirmDelete"
            >
              <LucideIcon
                name="Trash2"
                class="w-3.5 h-3.5 mr-2 opacity-70"
              />
              {{ $t('common.actions.delete') }}
            </Button>
          </div>
        </div>
      </Card>

      <!-- ==================== 2 TAB LAYOUT ==================== -->
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="flex items-center justify-between border-b">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger 
              value="identity" 
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors font-bold"
            >
              <LucideIcon
                name="Building"
                class="w-4 h-4 mr-2"
              />
              {{ $t('features.school.tabs.identityConfig') }}
            </TabsTrigger>
            <TabsTrigger 
              value="levels" 
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors font-bold"
            >
              <LucideIcon
                name="Layers"
                class="w-4 h-4 mr-2"
              />
              {{ $t('features.school.tabs.levels') }}
              <Badge
                v-if="levels.length > 0"
                variant="secondary"
                class="ml-2 h-5 px-1.5 text-[10px] font-bold"
              >
                {{ levels.length }}
              </Badge>
            </TabsTrigger>
          </TabsList>
        </div>

        <!-- ========== TAB 1: IDENTITY & CONFIGURATION ========== -->
        <TabsContent
          value="identity"
          class="mt-6 border-none p-0 focus-visible:ring-0"
        >
          <Card class="rounded-2xl border-border/50 shadow-sm overflow-hidden text-left">
            <div class="bg-muted/20 border-b border-border/30 p-6 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-foreground">
                  {{ $t('features.school.institution.title') }}
                </h3>
                <p class="text-sm text-muted-foreground mt-0.5">
                  {{ $t('features.school.institution.subtitle') }}
                </p>
              </div>
              <Button 
                v-if="isSuperAdmin"
                :disabled="savingIdentity" 
                size="sm" 
                class="rounded-xl px-6 h-9 font-bold bg-primary hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all active:scale-95"
                @click="saveIdentity"
              >
                <LucideIcon
                  v-if="savingIdentity"
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
            <CardContent class="p-6">
              <ConfigIdentity
                v-model:form="identityForm"
                :is-restricted="isIdentityEditRestricted"
              />
            </CardContent>
          </Card>
        </TabsContent>

        <!-- ========== TAB 2: EDUCATIONAL LEVELS ========== -->
        <TabsContent
          value="levels"
          class="mt-6 border-none p-0 focus-visible:ring-0"
        >
          <div class="space-y-6">
            <!-- Level Pills -->
            <div class="flex items-center gap-3 flex-wrap">
              <button
                v-for="lv in levels"
                :key="lv.id"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 font-bold text-sm transition-all duration-300 active:scale-95"
                :class="selectedLevelId === lv.id 
                  ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20' 
                  : 'border-border/50 bg-background text-foreground hover:border-primary/30 hover:bg-primary/5'"
                @click="selectLevel(lv)"
              >
                <LucideIcon
                  :name="getLevelIcon(lv.level)"
                  class="w-4 h-4"
                />
                {{ lv.name }}
              </button>

              <button
                v-if="canAddMoreLevels"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-dashed border-border/50 text-muted-foreground hover:border-primary/40 hover:text-primary hover:bg-primary/5 font-bold text-sm transition-all duration-300 active:scale-95"
                @click="showLevelDialog = true"
              >
                <LucideIcon
                  name="Plus"
                  class="w-4 h-4"
                />
                {{ $t('features.school.levels.add') }}
              </button>
            </div>

            <!-- Empty Levels State -->
            <div
              v-if="levels.length === 0"
              class="p-12 border-2 border-dashed rounded-2xl text-center bg-muted/10 border-border/40"
            >
              <LucideIcon
                name="Layers"
                class="w-10 h-10 text-muted-foreground/30 mx-auto mb-4"
              />
              <p class="text-muted-foreground font-medium">
                {{ $t('features.school.levels.empty') }}
              </p>
              <Button
                variant="link"
                class="mt-1 font-bold"
                @click="showLevelDialog = true"
              >
                {{ $t('features.school.levels.emptyAction') }}
              </Button>
            </div>

            <!-- Selected Level Detail -->
            <div
              v-if="selectedLevel"
              class="animate-in fade-in slide-in-from-bottom-2 duration-300"
            >
              <!-- Level Info Bar -->
              <div class="flex items-center justify-between mb-4 p-4 bg-muted/20 rounded-xl border border-border/30">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-primary/10 text-primary rounded-lg">
                    <LucideIcon
                      :name="getLevelIcon(selectedLevel.level)"
                      class="w-5 h-5"
                    />
                  </div>
                  <div>
                    <h4 class="font-bold text-foreground">
                      {{ selectedLevel.name }}
                    </h4>
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                      <span>NPSN: {{ selectedLevel.npsn || $t('common.none') }}</span>
                      <span class="opacity-30">•</span>
                      <span>{{ $t('common.labels.accreditation') }}: {{ selectedLevel.accreditation || '-' }}</span>
                    </div>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Button
                    variant="ghost"
                    size="sm"
                    class="rounded-xl h-8 px-3 font-bold text-xs"
                    @click="editLevel(selectedLevel)"
                  >
                    <LucideIcon
                      name="Settings2"
                      class="w-3.5 h-3.5 mr-1.5"
                    />
                    {{ $t('common.actions.edit') }}
                  </Button>
                  <Button 
                    v-if="!isLevelDeleteRestricted"
                    variant="ghost" 
                    size="sm" 
                    class="rounded-xl h-8 px-3 font-bold text-xs text-destructive hover:bg-destructive/10" 
                    @click="handleDeleteLevel(selectedLevel.id!)"
                  >
                    <LucideIcon
                      name="Trash2"
                      class="w-3.5 h-3.5 mr-1.5"
                    />
                    {{ $t('common.actions.delete') }}
                  </Button>
                </div>
              </div>

              <!-- Level Detail Sub-Tabs with Accordion -->
              <LevelDetailTabs 
                :key="selectedLevelId ?? undefined"
                :level="selectedLevel!" 
                :school="school" 
                :school-type="school?.type"
                :saving="savingLevel"
                :is-super-admin="isSuperAdmin"
                @save="saveLevelData" 
                @update:level="handleLevelUpdate"
              />
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>

    <!-- Level Create/Edit Dialog -->
    <LevelDialog
      v-model:show="showLevelDialog"
      v-model:form="levelForm"
      :editing-id="editingLevelId"
      :is-multi-branch="school?.is_multi_branch"
      :school-type="school?.type"
      @save="handleSaveLevel"
    />

    <!-- Strict Delete Confirmation Modal -->
    <Dialog v-model:open="showDeleteConfirm">
      <DialogContent class="sm:max-w-[500px] !z-[100050] p-0 overflow-hidden border-none shadow-2xl">
        <DialogHeader class="bg-destructive/5 p-8 text-center border-b border-destructive/10">
          <div class="w-16 h-16 bg-destructive/10 text-destructive rounded-full flex items-center justify-center mx-auto mb-4 ring-8 ring-destructive/5">
            <LucideIcon
              name="AlertTriangle"
              class="w-8 h-8"
            />
          </div>
          <DialogTitle class="text-2xl font-bold text-destructive mb-2">
            {{ $t('features.school.institution.deleteStrictTitle') }}
          </DialogTitle>
          <DialogDescription class="text-muted-foreground leading-relaxed">
            {{ $t('features.school.institution.deleteStrictWarning') }}
          </DialogDescription>
        </DialogHeader>

        <div class="p-8 space-y-6">
          <div class="space-y-3">
            <Label
              for="confirmName"
              class="text-sm font-bold text-foreground"
            >
              {{ $t('features.school.institution.deleteStrictConfirmPrompt') }}
            </Label>
            <div class="bg-muted/50 p-3 rounded-lg border border-border/50 font-mono text-sm text-center font-bold text-destructive">
              {{ school?.name }}
            </div>
            <Input 
              id="confirmName"
              v-model="deleteConfirmName"
              :placeholder="$t('features.school.institution.deleteStrictPlaceholder')"
              class="h-12 rounded-xl border-destructive/20 focus:ring-destructive/20 focus:border-destructive/30"
              @keyup.enter="deleteConfirmName === school?.name && executeDelete()"
            />
          </div>

          <DialogFooter class="flex flex-col sm:flex-row gap-3 sm:gap-0 mt-2">
            <Button 
              variant="ghost" 
              class="rounded-xl h-12 font-bold px-6" 
              :disabled="isDeleting"
              @click="showDeleteConfirm = false"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button 
              variant="destructive" 
              class="rounded-xl h-12 font-bold px-8 shadow-lg shadow-destructive/20 active:scale-95 transition-all"
              :disabled="deleteConfirmName !== school?.name || isDeleting"
              @click="executeDelete"
            >
              <LucideIcon
                v-if="isDeleting"
                name="Loader2"
                class="w-4 h-4 mr-2 animate-spin"
              />
              <LucideIcon
                v-else
                name="Trash2"
                class="w-4 h-4 mr-2"
              />
              {{ $t('features.school.institution.deleteStrictAction') }}
            </Button>
          </DialogFooter>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSchoolStore } from '../../../stores/school';
import { useLevelStore } from '../../../stores/level';
import type { SchoolLevel } from '@/types';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { useToast } from '@/composables/useToast';
import {
  Card, CardContent, Button, LucideIcon, Badge,
  Tabs, TabsList, TabsTrigger, TabsContent,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
  Input, Label
} from '@/components/ui';

import ConfigIdentity from './components/config/ConfigIdentity.vue';
import LevelDetailTabs from './components/config/LevelDetailTabs.vue';
import LevelDialog from './components/LevelDialog.vue';

const schoolStore = useSchoolStore();
const levelStore = useLevelStore();
const authStore = useAuthStore();
const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const isSuperAdmin = computed(() => authStore.isAtLeastRole('super-admin'));
const isInstitutionDeleteRestricted = computed(() => {
  if (isSuperAdmin.value) return false;
  return identityForm.value.type === 'negeri' || identityForm.value.type === 'swasta';
});
const isLevelDeleteRestricted = computed(() => {
  // Strict: hide for single-level institutions strictly following business rules
  if (school.value?.is_multi_level) return false;
  return school.value?.type === 'public' || school.value?.type === 'private';
});
const isIdentityEditRestricted = computed(() => {
  if (isSuperAdmin.value) return false;
  return identityForm.value.type === 'negeri' || identityForm.value.type === 'swasta';
});
const activeTab = ref('identity');
const savingIdentity = ref(false);
const savingLevel = ref(false);
const showLevelDialog = ref(false);
const editingLevelId = ref<number | null>(null);
const selectedLevelId = ref<number | null>(null);
const showDeleteConfirm = ref(false);
const deleteConfirmName = ref('');
const isDeleting = ref(false);

const school = computed(() => schoolStore.schools.length > 0 ? schoolStore.schools[0] : null);
const levels = computed(() => levelStore.levels);
const selectedLevel = computed(() => levels.value.find(l => l.id === selectedLevelId.value) || null);

const canAddMoreLevels = computed(() => {
  if (!school.value) return false;
  if (school.value.is_multi_level) return true;
  return levels.value.length === 0;
});

// Identity form state
const identityForm = ref({
  id: undefined as number | undefined,
  name: '',
  type: '',
  is_multi_level: false,
  is_multi_branch: false,
  npsn: '',
  nss: '',
  nds: '',
  status_kepemilikan: '',
});

// Level dialog form state
const levelForm = reactive({
  level: 'smk' as any,
  name: '',
  npsn: '',
  school_id: 1,
  settings: {} as any,
});

// ============== Lifecycle ==============

onMounted(async () => {
  loading.value = true;
  try {
    await schoolStore.fetchSchools();
    if (school.value && school.value.id) {
      Object.assign(identityForm.value, school.value);
      await levelStore.fetchLevels(school.value.id);
      const firstLevel = levels.value[0];
      if (firstLevel?.id != null) {
        selectedLevelId.value = firstLevel.id;
      }
    }
  } finally {
    loading.value = false;
  }
});

// Auto-map ownership status based on type
watch(() => identityForm.value.type, (newType) => {
  if (newType === 'public') {
    identityForm.value.status_kepemilikan = 'government';
  } else if (newType === 'private' || newType === 'yayasan') {
    identityForm.value.status_kepemilikan = 'foundation';
  }
});

// ============== Icon Helpers ==============

const getSchoolIcon = (type: string) => {
  switch (type) {
    case 'yayasan': return 'Component';
    case 'public': return 'ShieldCheck';
    default: return 'Building';
  }
};

const getLevelIcon = (levelType: string) => {
  switch (levelType) {
    case 'sd': return 'Baby';
    case 'smp': return 'GraduationCap';
    case 'sma': return 'BookOpen';
    case 'smk': return 'Cpu';
    default: return 'School';
  }
};

// ============== Actions ==============

const selectLevel = (lv: SchoolLevel) => {
  selectedLevelId.value = lv.id!;
};

const saveIdentity = async () => {
  if (!identityForm.value.name) {
    toast.error.action(t('features.school.levels.errors.nameRequired'));
    return;
  }
  savingIdentity.value = true;
  try {
    if (identityForm.value.id) {
      await schoolStore.updateSchool(identityForm.value.id, identityForm.value as any);
      if (identityForm.value.id) await levelStore.fetchLevels(identityForm.value.id);
      toast.success.save();
    }
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    savingIdentity.value = false;
  }
};

const saveLevelData = async () => {
  if (!selectedLevelId.value || !selectedLevel.value) return;
  savingLevel.value = true;
  try {
    const schoolId = school.value?.id;
    await levelStore.updateLevel(selectedLevelId.value, {
      ...selectedLevel.value,
      school_id: schoolId // Ensure school_id is passed for correct refresh
    });
    toast.success.action(t('features.school.levels.updateSuccess'));
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    savingLevel.value = false;
  }
};

const handleLevelUpdate = (updatedLevel: any) => {
  const index = levelStore.levels.findIndex(l => l.id === updatedLevel.id);
  if (index !== -1) {
    // We replace the item in the array to maintain reactivity without direct prop mutation
    levelStore.levels[index] = updatedLevel;
  }
};

const editLevel = (level: SchoolLevel) => {
  editingLevelId.value = level.id!;
  levelForm.level = level.level;
  levelForm.name = level.name;
  levelForm.npsn = level.npsn || '';
  showLevelDialog.value = true;
};

const closeLevelDialog = () => {
  showLevelDialog.value = false;
  editingLevelId.value = null;
  levelForm.name = '';
  levelForm.npsn = '';
};

const handleSaveLevel = async () => {
  if (!levelForm.name) {
    toast.error.action(t('features.school.levels.errors.nameRequired'));
    return;
  }
  try {
    const schoolId = school.value?.id ?? 1;
    if (editingLevelId.value) {
      await levelStore.updateLevel(editingLevelId.value, {
        ...levelForm,
        school_id: schoolId
      } as any);
      toast.success.save();
    } else {
      levelForm.school_id = schoolId;
      await levelStore.createLevel(levelForm as any);
      toast.success.save();
    }
    closeLevelDialog();
    const firstLv = levels.value[0];
    if (!selectedLevelId.value && firstLv?.id != null) {
      selectedLevelId.value = firstLv.id;
    }
  } catch (e: any) {
    toast.error.fromResponse(e);
  }
};

const handleDeleteLevel = async (id: number) => {
  if (confirm(t('features.school.levels.deleteConfirm'))) {
    try {
      await levelStore.deleteLevel(id);
      toast.success.action(t('features.school.levels.deleteSuccess'));
      if (selectedLevelId.value === id) {
        const nextLevel = levels.value[0];
        selectedLevelId.value = nextLevel?.id ?? null;
      }
    } catch (e: any) {
      toast.error.fromResponse(e);
    }
  }
};

const confirmDelete = () => {
  if (school.value?.id) {
    deleteConfirmName.value = '';
    showDeleteConfirm.value = true;
  }
};

const executeDelete = async () => {
  if (!school.value?.id) return;
  
  if (deleteConfirmName.value !== school.value.name) {
    toast.error.action(t('features.school.levels.errors.nameRequired'));
    return;
  }

  isDeleting.value = true;
  try {
    await schoolStore.deleteSchool(school.value.id);
    toast.success.action(t('features.school.levels.deleteSuccess'));
    showDeleteConfirm.value = false;
    await schoolStore.fetchSchools();
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    isDeleting.value = false;
  }
};
</script>
"
,Complexity:5,Description:
