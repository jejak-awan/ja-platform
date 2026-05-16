<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Move to Folder</DialogTitle>
        <DialogDescription>
          Select the destination folder for the selected items.
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <label class="text-sm font-medium mb-2 block">Select Folder</label>
        <Select v-model="selectedFolderId">
          <SelectTrigger>
            <SelectValue placeholder="Select a folder" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="root">
              No Folder (Root)
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

      <DialogFooter>
        <Button
          variant="outline"
          @click="$emit('close')"
        >
          Cancel
        </Button>
        <Button @click="handleMove">
          Move
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { 
    Button, 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogDescription, 
    DialogFooter, 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/shared/components/ui';
import type { MediaFolder } from '@/modules/Media/types/media';

defineProps<{
    folders?: MediaFolder[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'moved', folderId: string | null): void;
}>();

const selectedFolderId = ref('root');

const handleMove = () => {
    // Convert 'root' back to null for API
    const folderId = selectedFolderId.value === 'root' ? null : selectedFolderId.value;
    emit('moved', folderId);
};
</script>

