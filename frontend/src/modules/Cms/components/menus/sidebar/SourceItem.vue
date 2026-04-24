<template>
  <div 
    class="flex items-center gap-3 p-3 text-sm bg-card border border-border rounded-md cursor-move hover:shadow-sm transition-shadow group"
    :class="typeColorClass"
  >
    <GripVertical class="w-4 h-4 text-muted-foreground group-hover:text-foreground shrink-0" />
    <component 
      :is="iconComponent" 
      class="w-4 h-4 shrink-0" 
      :class="iconColorClass"
    />
    <span class="font-medium truncate flex-1">{{ label }}</span>
    <Button 
      size="icon" 
      variant="ghost" 
      class="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
      @click.stop="$emit('add')"
    >
      <Plus class="w-3 h-3" />
    </Button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import GripVertical from 'lucide-vue-next/dist/esm/icons/grip-vertical.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import File from 'lucide-vue-next/dist/esm/icons/file.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import LinkIcon from 'lucide-vue-next/dist/esm/icons/link.js';

const props = defineProps<{
    item: Record<string, unknown>;
    type?: string;
}>();

defineEmits<{
    (e: 'add'): void;
}>();

const label = computed(() => props.item.title || props.item.name || 'Untitled');

const iconComponent = computed(() => {
    switch (props.type) {
        case 'page': return FileText;
        case 'post': return File;
        case 'category': return Tag;
        default: return LinkIcon;
    }
});

const iconColorClass = computed(() => {
    switch (props.type) {
        case 'page': return 'text-blue-500';
        case 'post': return 'text-orange-500';
        case 'category': return 'text-purple-500';
        default: return 'text-emerald-500';
    }
});

const typeColorClass = computed(() => {
    switch (props.type) {
        case 'page': return 'hover:border-blue-500/50';
        case 'post': return 'hover:border-orange-500/50';
        case 'category': return 'hover:border-purple-500/50';
        default: return 'hover:border-emerald-500/50';
    }
});
</script>
