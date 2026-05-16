<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ $t('modules.system.media.modals.folder.title') }}</DialogTitle>
        <DialogDescription>
          {{ $t('modules.system.media.modals.folder.placeholder') || 'Create a new folder to organize your media.' }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="grid gap-4 py-4"
        @submit.prevent="handleSubmit"
      >
        <div class="grid gap-2">
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            {{ $t('modules.system.media.modals.folder.name') }} <span class="text-destructive">*</span>
          </label>
          <Input
            v-model="form.name"
            type="text"
            required
            :placeholder="$t('modules.system.media.modals.folder.placeholder')"
          />
        </div>

        <div class="grid gap-2">
          <label class="text-sm font-medium leading-none">
            {{ $t('modules.system.media.modals.folder.parent') }}
          </label>
          <Select 
            :model-value="form.parent_id ? String(form.parent_id) : 'none'"
            @update:model-value="(val) => form.parent_id = val === 'none' ? null : val"
          >
            <SelectTrigger class="w-full bg-background border-border/40 rounded-xl">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="none">
                {{ $t('modules.system.media.modals.folder.noParent') }}
              </SelectItem>
              <SelectItem
                v-for="folder in folders"
                :key="folder.id"
                :value="String(folder.id)"
              >
                {{ folder.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Shared Status (Admin Only) -->
        <div
          v-if="canManageMedia"
          class="flex items-start space-x-3 p-3 bg-muted/40 border border-border/60 rounded-xl mt-2 transition-colors hover:bg-muted/60"
        >
          <Checkbox 
            id="folder_is_shared" 
            v-model:checked="form.is_shared"
            class="mt-0.5 border-primary/50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
          />
          <div class="flex-1">
            <label
              for="folder_is_shared"
              class="text-sm font-bold text-foreground cursor-pointer select-none"
            >
              {{ $t('modules.system.media.modals.edit.isShared') }}
            </label>
            <p class="text-[10px] text-muted-foreground mt-0.5 leading-relaxed">
              {{ $t('modules.system.media.modals.edit.isSharedHelp') }}
            </p>
          </div>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          class="rounded-xl"
          @click="$emit('close')"
        >
          {{ $t('modules.system.media.actions.cancel') }}
        </Button>
        <Button
          :disabled="saving || !isValid"
          class="rounded-xl shadow-lg shadow-primary/20"
          @click="handleSubmit"
        >
          <Loader2
            v-if="saving"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ saving ? $t('modules.system.media.modals.folder.creating') : $t('modules.system.media.modals.folder.create') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import api from '@/engine/api/client';
import { 
    Button, 
    Input, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue, 
    Checkbox,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/shared/components/ui';
import { useAuthStore } from '@/modules/System/stores/auth';
import type { MediaFolder } from '@/modules/Media/types/media';

interface FolderForm {
    name: string;
    parent_id: string | null;
    is_shared: boolean;
}

const authStore = useAuthStore();

const toast = useToast();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'created'): void;
}>();

const saving = ref(false);
const folders = ref<MediaFolder[]>([]);

const form = ref<FolderForm>({
    name: '',
    parent_id: null,
    is_shared: false,
});

const isValid = computed(() => {
    return !!form.value.name?.trim();
});

const canManageMedia = authStore.hasPermission('manage media');

const fetchFolders = async () => {
    try {
        const response = await api.get('/manage/media-folders');
        const data = response.data || [];
        folders.value = data.filter((f: MediaFolder) => !f.is_trashed);
    } catch (error) {
        logger.error('Failed to fetch folders:', error);
    }
};

const handleSubmit = async () => {
    if (!form.value.name.trim()) return;

    saving.value = true;
    try {
        await api.post('/manage/media-folders', form.value);
        toast.success.create('Folder');
        emit('created');
        emit('close');
    } catch (error: unknown) {
        logger.error('Failed to create folder:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchFolders();
});
</script>

