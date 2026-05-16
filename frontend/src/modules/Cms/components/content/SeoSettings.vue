<template>
  <Card class="border-none shadow-sm bg-card/50">
    <CardHeader class="pb-4">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Globe class="w-5 h-5 text-primary" />
        {{ $t('modules.cms.content.form.seoSettings') }}
      </CardTitle>
    </CardHeader>
    <CardContent class="space-y-6">
      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('modules.cms.content.form.metaTitle') }}
        </Label>
        <div class="relative">
          <Type class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/50" />
          <Input
            :model-value="modelValue.meta_title"
            type="text"
            maxlength="255"
            class="pl-9 bg-background/50"
            placeholder="SEO title (defaults to content title)"
            @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_title: val.toString() })"
          />
        </div>
        <div class="flex justify-end">
          <p
            class="text-[10px] font-medium transition-colors"
            :class="(modelValue.meta_title?.length || 0) > 60 ? 'text-warning' : 'text-muted-foreground/60'"
          >
            {{ modelValue.meta_title?.length || 0 }}/255 characters
          </p>
        </div>
      </div>

      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('modules.cms.content.form.metaDescription') }}
        </Label>
        <Textarea
          :model-value="modelValue.meta_description"
          rows="3"
          maxlength="500"
          class="bg-background/50 resize-none"
          placeholder="SEO description (defaults to excerpt)"
          @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_description: val.toString() })"
        />
        <div class="flex justify-end">
          <p
            class="text-[10px] font-medium transition-colors"
            :class="(modelValue.meta_description?.length || 0) > 160 ? 'text-warning' : 'text-muted-foreground/60'"
          >
            {{ modelValue.meta_description?.length || 0 }}/500 characters
          </p>
        </div>
      </div>

      <div class="space-y-1.5">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('modules.cms.content.form.metaKeywords') }}
        </Label>
        <div class="relative">
          <Hash class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/50" />
          <Input
            :model-value="modelValue.meta_keywords"
            type="text"
            class="pl-9 bg-background/50"
            :placeholder="$t('modules.cms.content.form.keywordsPlaceholder')"
            @update:model-value="(val: string | number) => $emit('update:modelValue', { ...modelValue, meta_keywords: val.toString() })"
          />
        </div>
      </div>

      <div class="space-y-3">
        <Label class="text-sm font-semibold tracking-tight">
          {{ $t('modules.cms.content.form.ogImage') }}
        </Label>
        <div
          v-if="modelValue.og_image"
          class="relative group aspect-video"
        >
          <img
            :src="modelValue.og_image"
            alt="OG Image"
            class="w-full h-full object-cover rounded-lg border border-border/40 shadow-sm"
          >
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
            <Button
              variant="destructive"
              size="sm"
              @click="$emit('update:modelValue', { ...modelValue, og_image: null })"
            >
              <Trash2 class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.content.form.remove') }}
            </Button>
          </div>
        </div>
        <div
          v-else
          class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-border/40 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <MediaPicker
            :label="$t('modules.cms.content.form.selectOgImage')"
            :constraints="{
              allowedExtensions: settings.allowed_image_types ? String(settings.allowed_image_types).split(',').map(s => s.trim()) : ['jpg', 'jpeg', 'png', 'webp'],
              minWidth: 1200,
              minHeight: 630
            }"
            @selected="(media) => $emit('update:modelValue', { ...modelValue, og_image: media.url })"
          >
            <template #trigger="{ open }">
              <Button 
                type="button" 
                variant="outline" 
                class="w-full gap-2 border-2 border-dashed h-12 hover:border-primary transition-colors" 
                @click="open"
              >
                <ImageIcon class="w-4 h-4" />
                {{ $t('modules.cms.content.form.selectOgImage') }}
              </Button>
            </template>
          </MediaPicker>
          <div class="mt-4 text-[10px] text-muted-foreground/60 text-center italic leading-relaxed">
            <p>{{ $t('modules.cms.content.form.recommendedHint', { dimensions: '1200x630px' }) }}</p>
            <p>{{ $t('modules.cms.content.form.maxSizeHint', { size: Math.round(maxUploadSizeMB), extensions: String(settings.allowed_image_types || 'JPG, PNG, WEBP').toUpperCase() }) }}</p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useSystemStore } from '@/modules/System/stores/system';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Label,
    Input,
    Textarea,
    Button
} from '@/shared/components/ui';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import Hash from 'lucide-vue-next/dist/esm/icons/hash.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';

interface SeoData {
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    og_image: string | null;
}

const systemStore = useSystemStore();
const { settings } = storeToRefs(systemStore);

const maxUploadSizeMB = computed(() => {
    // Setting is in KB, convert to MB
    const sizeKB = (settings.value as Record<string, unknown>).max_upload_size as number || 10240;
    return sizeKB / 1024;
});

onMounted(async () => {
    // Ensure media settings are loaded for the limits
    await systemStore.fetchSettingsGroup('media');
});

withDefaults(defineProps<{
    modelValue?: SeoData;
}>(), {
    modelValue: () => ({
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        og_image: null,
    }),
});

defineEmits<{
    (e: 'update:modelValue', value: SeoData): void;
}>();
</script>
