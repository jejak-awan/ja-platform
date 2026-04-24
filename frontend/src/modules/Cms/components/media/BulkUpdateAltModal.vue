<template>
  <div
    class="fixed inset-0 bg-background/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
  >
    <div class="relative w-full max-w-md p-6 border border-border/40 rounded-xl bg-card shadow-none">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-foreground mb-4">
          {{ $t('features.media.modals.updateAlt.title') }}
        </h3>
        <p class="text-sm text-muted-foreground mb-4">
          {{ $t('features.media.modals.updateAlt.description', { count: selectedCount }) }}
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-foreground mb-2">
            {{ $t('features.media.modals.updateAlt.label') }}
          </label>
          <Input
            v-model="altText"
            type="text"
            :placeholder="$t('features.media.modals.updateAlt.placeholder')"
          />
        </div>
        <div class="flex justify-end space-x-3">
          <Button
            variant="outline"
            @click="$emit('close')"
          >
            {{ $t('features.media.modals.updateAlt.cancel') }}
          </Button>
          <Button
            :disabled="processing"
            @click="$emit('submit', altText)"
          >
            {{ processing ? $t('features.media.modals.updateAlt.updating') : $t('features.media.modals.updateAlt.update') }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Button, Input } from '@/components/ui';

defineProps<{
    selectedCount: number;
    processing: boolean;
}>();

defineEmits(['close', 'submit']);

const altText = ref('');
</script>
