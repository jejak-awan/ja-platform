<template>
  <Card class="border-none shadow-sm bg-card/50 overflow-hidden">
    <CardHeader class="pb-4">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Image class="w-5 h-5 text-primary" />
        {{ $t('features.content.form.featuredImage') }}
      </CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        <div
          v-if="modelValue"
          class="relative group aspect-video"
        >
          <img
            :src="modelValue"
            alt="Featured Image"
            class="w-full h-full object-cover rounded-lg border border-border/40 shadow-sm"
          >
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
            <Button
              variant="destructive"
              size="sm"
              @click="$emit('update:modelValue', null)"
            >
              <Trash2 class="w-4 h-4 mr-2" />
              {{ $t('features.content.form.remove') }}
            </Button>
          </div>
        </div>
        <div
          v-else
          class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-border/40 rounded-lg bg-background/30 hover:bg-background/50 transition-colors"
        >
          <MediaPicker
            :label="$t('features.content.form.selectImage')"
            :constraints="{
              allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
              minWidth: 600,
              minHeight: 400
            }"
            @selected="(media) => $emit('update:modelValue', media.url)"
          >
            <template #trigger="{ open }">
              <Button 
                type="button" 
                variant="outline" 
                class="gap-2 border-2 border-dashed h-12 hover:border-primary transition-colors" 
                @click="open"
              >
                <Plus class="w-4 h-4" />
                {{ $t('features.content.form.selectImage') }}
              </Button>
            </template>
          </MediaPicker>
          <div class="mt-4 text-[10px] text-muted-foreground/60 text-center italic leading-relaxed">
            <p>{{ $t('features.content.form.recommendedHint', { dimensions: '1200x630px' }) }} {{ $t('features.content.form.minHint', { dimensions: '600x400px' }) }}</p>
            <p>{{ $t('features.content.form.maxSizeHint', { size: Math.round(maxUploadSizeMB), extensions: 'JPG, PNG, WEBP' }) }}</p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import MediaPicker from '@/modules/Cms/components/media/MediaPicker.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    Button
} from '@/components/ui';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import { useCmsStore } from '@/modules/Cms/stores/cms';

const cmsStore = useCmsStore();
const { settings } = storeToRefs(cmsStore);

const maxUploadSizeMB = computed(() => {
    // Setting is in KB, convert to MB
    const sizeKB = (settings.value as Record<string, unknown>).max_upload_size as number || 10240;
    return sizeKB / 1024;
});

onMounted(async () => {
    // Ensure media settings are loaded for the limits
    await cmsStore.fetchSettingsGroup('media');
});

defineProps<{
    modelValue: string | null;
}>();

defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();
</script>
