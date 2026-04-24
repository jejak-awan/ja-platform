<template>
  <div 
    class="flex flex-col bg-card border-r shadow-2xl transition-colors duration-300 ease-in-out z-10"
    :class="isCollapsed ? 'w-[60px]' : 'w-96'"
  >
    <!-- Header -->
    <div class="h-16 flex items-center px-4 border-b shrink-0 bg-muted/30 transition-colors justify-between">
      <div
        class="flex items-center gap-3 overflow-hidden"
        :class="{'opacity-0 w-0': isCollapsed}"
      >
        <div>
          <h2 class="font-semibold text-lg tracking-tight whitespace-nowrap">
            {{ $t('features.theme_customizer.actions.quick_settings') }}
          </h2>
          <p class="text-xs text-muted-foreground truncate max-w-[150px]">
            {{ themeName }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <!-- Collapse Button -->
        <button 
          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-md transition-colors"
          :title="isCollapsed ? $t('features.theme_customizer.sidebar.items.expand') : $t('features.theme_customizer.sidebar.items.collapse')"
          @click="isCollapsed = !isCollapsed"
        >
          <ChevronsLeft 
            class="w-5 h-5 transition-transform duration-300" 
            :class="{'rotate-180': isCollapsed}" 
          />
        </button>

        <!-- Actions (Hidden when collapsed) -->
        <div
          v-show="!isCollapsed"
          class="flex items-center gap-1 animate-in fade-in duration-200"
        >
          <div class="flex items-center border rounded-md bg-background mr-2 overflow-hidden">
            <button 
              :disabled="!canUndo" 
              class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-30 transition-colors"
              :title="$t('features.theme_customizer.actions.undo')"
              @click="$emit('undo')"
            >
              <Undo2 class="w-4 h-4" />
            </button>
            <div class="w-px h-4 bg-border" />
            <button 
              :disabled="!canRedo" 
              class="p-1.5 hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-30 transition-colors"
              :title="$t('features.theme_customizer.actions.redo')"
              @click="$emit('redo')"
            >
              <Redo2 class="w-4 h-4" />
            </button>
          </div>

          <button 
            class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-full hover:bg-muted"
            :title="$t('features.theme_customizer.actions.revert')"
            @click="$emit('reset')"
          >
            <RotateCcw class="w-4 h-4" />
          </button>
          <button 
            class="p-2 text-muted-foreground hover:text-destructive transition-colors rounded-full hover:bg-muted"
            :title="$t('common.actions.close')"
            @click="$emit('close')"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div
      v-show="!isCollapsed"
      class="flex-1 overflow-y-auto px-6 py-6 custom-scrollbar animate-in fade-in slide-in-from-left-4 duration-300"
    >
      <!-- Loading State -->
      <div
        v-if="loading"
        class="flex items-center justify-center py-12"
      >
        <LoaderCircle class="w-8 h-8 text-primary animate-spin" />
      </div>

      <!-- Settings Sections -->
      <div
        v-else
        class="space-y-6"
      >
        <div
          v-for="section in sections"
          :key="section.id"
          class="space-y-3"
        >
          <button 
            class="w-full flex items-center justify-between py-2 text-sm font-medium hover:text-primary transition-colors border-b group"
            @click="toggleSection(section.id)"
          >
            <span class="group-hover:translate-x-1 transition-transform">{{ section.label }}</span>
            <ChevronDown 
              class="w-4 h-4 transition-transform duration-200"
              :class="{ 'rotate-180': expandedSections.includes(section.id) }"
            />
          </button>

          <div 
            v-show="expandedSections.includes(section.id)" 
            class="space-y-5 pl-2 animate-in slide-in-from-top-2 duration-200"
          >
            <SettingControl 
              v-for="setting in section.settings" 
              :key="setting.key"
              :setting="setting"
              :model-value="formValues[setting.key]"
              @update:model-value="(val) => updateField(setting.key, val)"
              @update:multiple="(patch) => updateFields(patch)"
              @change="$emit('change')"
              @pick-media="openMediaPicker(setting.key)"
            />
          </div>
        </div>

        <!-- Custom CSS Section -->
        <div class="space-y-3 pt-6 border-t">
          <button 
            class="w-full flex items-center justify-between py-2 text-sm font-medium hover:text-primary transition-colors"
            @click="showCustomCss = !showCustomCss"
          >
            <span>{{ $t('features.theme_customizer.sidebar.items.custom_css.title') }}</span>
            <span class="text-[10px] bg-muted px-2 py-0.5 rounded text-muted-foreground font-mono">&lt;/&gt;</span>
          </button>
                    
          <div
            v-show="showCustomCss"
            class="animate-in slide-in-from-top-2"
          >
            <textarea
              :value="customCss"
              rows="8"
              class="w-full p-3 bg-background border rounded-lg text-xs font-mono custom-scrollbar focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-colors leading-relaxed"
              :placeholder="$t('features.theme_customizer.sidebar.items.custom_css.placeholder')"
              spellcheck="false"
              @input="$emit('update:customCss', ($event.target as HTMLTextAreaElement).value)"
              @change="$emit('change')"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Actions -->
    <div
      v-show="!isCollapsed"
      class="p-4 border-t bg-muted/30 shrink-0 animate-in fade-in duration-300"
    >
      <button
        :disabled="saving || !isDirty"
        class="w-full h-10 flex items-center justify-center gap-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-muted transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
        @click="$emit('save')"
      >
        <LoaderCircle
          v-if="saving"
          class="w-4 h-4 animate-spin"
        />
        <span>{{ saving ? $t('features.theme_customizer.status.saving') : $t('features.theme_customizer.actions.publish') }}</span>
      </button>
    </div>

    <!-- Media Picker Modal -->
    <MediaPicker
      v-model:open="showMediaPicker"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <span class="hidden" />
      </template>
    </MediaPicker>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import SettingControl from './SettingControl.vue';
import MediaPicker from '@/modules/Cms/components/media/MediaPicker.vue';

import ChevronsLeft from 'lucide-vue-next/dist/esm/icons/chevrons-left.js';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Redo2 from 'lucide-vue-next/dist/esm/icons/redo-2.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import LoaderCircle from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';

import type { ThemeSection } from '@/types/cms/theme';

const props = withDefaults(defineProps<{
    sections?: ThemeSection[];
    formValues: Record<string, unknown>;
    customCss?: string;
    loading?: boolean;
    saving?: boolean;
    isDirty?: boolean;
    themeName?: string;
    canUndo?: boolean;
    canRedo?: boolean;
}>(), {
    sections: () => [],
    customCss: '',
    loading: false,
    saving: false,
    isDirty: false,
    themeName: '',
    canUndo: false,
    canRedo: false
});

const emit = defineEmits<{
    (e: 'update:formValues', value: Record<string, unknown>): void;
    (e: 'update:customCss', value: string): void;
    (e: 'change'): void;
    (e: 'undo'): void;
    (e: 'redo'): void;
    (e: 'save'): void;
    (e: 'reset'): void;
    (e: 'close'): void;
}>();

const isCollapsed = ref(false);
const expandedSections = ref(['General']);
const showCustomCss = ref(false);
const showMediaPicker = ref(false);
const activeMediaField = ref<string | null>(null);

const toggleSection = (id: string) => {
    if (expandedSections.value.includes(id)) {
        expandedSections.value = expandedSections.value.filter(s => s !== id);
    } else {
        expandedSections.value.push(id);
    }
};

const updateField = (key: string, value: unknown) => {
    emit('change');
    emit('update:formValues', { ...props.formValues, [key]: value });
};

const updateFields = (patch: Record<string, unknown>) => {
    emit('change');
    emit('update:formValues', { ...props.formValues, ...patch });
};

const openMediaPicker = (fieldKey: string) => {
    activeMediaField.value = fieldKey;
    showMediaPicker.value = true;
};

const handleMediaSelect = (media: { url: string }) => {
    if (activeMediaField.value) {
        updateField(activeMediaField.value, media.url);
    }
    showMediaPicker.value = false;
    activeMediaField.value = null;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.3);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.5);
}
</style>
