<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>
          {{ t('features.developer.plugins.modal.settings_title', { name: plugin?.name }) }}
        </DialogTitle>
      </DialogHeader>

      <div class="py-6 min-h-[100px] flex items-center justify-center">
        <p class="text-muted-foreground italic text-sm">
          {{ t('features.developer.plugins.modal.settings_placeholder') }}
        </p>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('close')"
        >
          {{ t('features.developer.plugins.modal.close') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { 
    Button,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter
} from '@/shared/components/ui';

const { t } = useI18n();

interface Plugin {
    name: string;
    [key: string]: unknown;
}

defineProps<{
    plugin?: Plugin | null;
}>();

defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();
</script>

