<template>
  <AccordionItem
    value="profile"
    class="border border-border rounded-2xl overflow-hidden shadow-none"
  >
    <AccordionTrigger class="px-6 py-5 hover:no-underline hover:bg-muted transition-colors [&[data-state=open]]:bg-muted/50 border-none">
      <div class="flex items-center gap-4">
        <div class="p-2.5 bg-muted text-foreground/50 rounded-xl border border-border">
          <LucideIcon
            name="FileText"
            class="w-4 h-4"
          />
        </div>
        <div class="text-left">
          <span class="font-bold text-foreground text-sm tracking-tight">{{ $t('modules.school.units.sections.profile') }}</span>
          <p class="text-[10px] text-muted-foreground font-medium mt-0.5 opacity-70">
            {{ $t('modules.school.units.sections.profileDesc') }}
          </p>
        </div>
      </div>
    </AccordionTrigger>
    <AccordionContent class="px-8 pb-8 space-y-8 text-left">
      <!-- Logo Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 pb-8 border-b border-border/40">
        <!-- 1. Unit Logo -->
        <div class="flex flex-col items-center gap-4 p-6 rounded-2xl bg-muted/5 border border-border/50">
          <Label class="text-xs font-black uppercase tracking-widest text-muted-foreground/60">Logo Unit / Sekolah</Label>
          <div class="relative group">
            <div class="w-32 h-32 rounded-2xl bg-muted flex items-center justify-center border-2 border-dashed border-border group-hover:border-primary/30 transition-all overflow-hidden bg-card shadow-sm">
              <img v-if="localForm.logo" :src="localForm.logo" class="w-full h-full object-cover" />
              <LucideIcon v-else name="ImagePlus" class="w-12 h-12 text-foreground/10" />
              
              <div 
                class="absolute inset-0 bg-background/80 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer"
                @click="triggerMediaPicker('unit')"
              >
                <LucideIcon name="Camera" class="w-6 h-6 text-primary" />
                <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Ubah Logo</span>
              </div>
            </div>
          </div>
          <p class="text-[10px] text-muted-foreground text-center font-medium max-w-[200px]">Logo resmi unit pendidikan yang akan tampil di kop surat dan profil</p>
        </div>

        <!-- 2. Second Logo (Dinas or Foundation) -->
        <div class="flex flex-col items-center gap-4 p-6 rounded-2xl bg-muted/5 border border-border/50">
          <Label class="text-xs font-black uppercase tracking-widest text-muted-foreground/60">
            {{ isPublic ? 'Logo Dinas Pendidikan' : 'Logo Yayasan / Penyelenggara' }}
          </Label>
          <div class="relative group">
            <div class="w-32 h-32 rounded-2xl bg-muted flex items-center justify-center border-2 border-dashed border-border group-hover:border-primary/30 transition-all overflow-hidden bg-card shadow-sm">
              <img v-if="displaySecondLogo" :src="displaySecondLogo" class="w-full h-full object-cover" />
              <LucideIcon v-else :name="isPublic ? 'ShieldCheck' : 'Building'" class="w-12 h-12 text-foreground/10" />
              
              <div 
                class="absolute inset-0 bg-background/80 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 cursor-pointer"
                @click="triggerMediaPicker('secondary')"
              >
                <LucideIcon name="Camera" class="w-6 h-6 text-primary" />
                <span class="text-[10px] font-bold text-primary uppercase tracking-wider">Ubah Logo</span>
              </div>
            </div>
            <!-- Indicator if inherited from Foundation -->
            <div 
              v-if="!isPublic && !localForm.logo_yayasan && school.logo"
              class="absolute -top-2 -right-2 px-2 py-0.5 rounded-md bg-primary text-primary-foreground text-[8px] font-black uppercase tracking-tighter shadow-lg border border-background"
            >
              Inherited
            </div>
          </div>
          <p class="text-[10px] text-muted-foreground text-center font-medium max-w-[200px]">
            {{ isPublic ? 'Logo Dinas Pendidikan Kabupaten/Provinsi yang membawahi unit' : 'Logo yayasan atau badan hukum penyelenggara pendidikan' }}
          </p>
        </div>
      </div>

      <div class="flex-1 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mt-4">
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.principalName') }}</Label>
            <Input
              v-model="localForm.principal_name"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground/80">{{ $t('common.labels.foundationName') }}</Label>
            <Input
              v-model="localForm.foundation_name"
              class="rounded-xl h-11 border-border bg-muted/10 focus:ring-foreground/5 text-sm font-medium"
            />
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-12 mt-4 pt-4 border-t border-border/40">
        <div class="space-y-4">
          <Label class="text-sm font-bold text-foreground/80">{{ $t('modules.school.labels.vision') }}</Label>
          <TiptapEditor
            v-model="localForm.vision"
            :placeholder="$t('modules.school.labels.vision')"
            compact
            class="min-h-[120px]"
          />
        </div>
        <div class="space-y-4">
          <Label class="text-sm font-bold text-foreground/80">{{ $t('modules.school.labels.mission') }}</Label>
          <TiptapEditor
            v-model="localForm.mission"
            :placeholder="$t('modules.school.labels.mission')"
            compact
            class="min-h-[120px]"
          />
        </div>
        <div class="space-y-4">
          <Label class="text-sm font-bold text-foreground/80">{{ $t('common.labels.goals') }}</Label>
          <TiptapEditor
            v-model="localForm.goals"
            :placeholder="$t('common.labels.goals')"
            compact
            class="min-h-[120px]"
          />
        </div>
        <div class="space-y-4">
          <Label class="text-sm font-bold text-foreground/80">{{ $t('common.labels.history') }}</Label>
          <TiptapEditor
            v-model="localForm.history"
            :placeholder="$t('common.labels.history')"
            compact
            class="min-h-[120px]"
          />
        </div>
      </div>
    </AccordionContent>
    <!-- Media Picker Modal -->
    <MediaPicker
      v-model:open="showMediaPicker"
      :path="`school/school_${level.school_id}/level_${level.id}/identity`"
      module="school"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <div class="hidden"></div>
      </template>
    </MediaPicker>
  </AccordionItem>
</template>

<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue';
import { 
  AccordionItem, AccordionTrigger, AccordionContent, 
  Label, Input, LucideIcon
} from '@/shared/components/ui';
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';

const props = defineProps<{
  settings: any;
  level: any;
  school: any;
}>();

const emit = defineEmits<{
  (e: 'update:settings', value: any): void;
}>();

const localForm = reactive({ ...props.settings });
const showMediaPicker = ref(false);
const pickerTarget = ref<'unit' | 'secondary'>('unit');

watch(() => props.settings, (newVal) => {
  Object.assign(localForm, newVal);
}, { deep: true });

// Sync back to parent if needed
watch(localForm, (newVal) => {
  emit('update:settings', newVal);
}, { deep: true });

const isPublic = computed(() => {
  const type = props.school?.type?.toLowerCase();
  return type === 'negeri' || type === 'public';
});

const displaySecondLogo = computed(() => {
  if (isPublic.value) {
    return localForm.logo_dinas;
  }
  return localForm.logo_yayasan || props.school?.logo;
});

const triggerMediaPicker = (target: 'unit' | 'secondary') => {
  pickerTarget.value = target;
  showMediaPicker.value = true;
};

const handleMediaSelect = (media: any) => {
  const url = media?.url || media?.path;
  if (url) {
    if (pickerTarget.value === 'unit') {
      localForm.logo = url;
    } else {
      if (isPublic.value) {
        localForm.logo_dinas = url;
      } else {
        localForm.logo_yayasan = url;
      }
    }
  }
  showMediaPicker.value = false;
};
</script>
