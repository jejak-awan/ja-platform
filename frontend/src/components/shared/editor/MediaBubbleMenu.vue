<template>
  <bubble-menu 
    v-if="editor" 
    :editor="editor" 
    :tippy-options="{ duration: 100, placement: 'top', offset: [0, 10] }" 
    :should-show="shouldShow"
    class="flex items-center gap-1 p-1 bg-background border border-border rounded-md shadow-md z-40"
  >
    <div class="flex items-center gap-1 border-r pr-1 mr-1 border-border/50">
      <Button
        size="icon"
        variant="ghost"
        class="h-7 w-7"
        :class="{ 'bg-muted': currentAlign === 'left' }"
        @click="updateAlign('left')"
      >
        <AlignLeft class="w-3.5 h-3.5" />
      </Button>
      <Button
        size="icon"
        variant="ghost"
        class="h-7 w-7"
        :class="{ 'bg-muted': currentAlign === 'center' }"
        @click="updateAlign('center')"
      >
        <AlignCenter class="w-3.5 h-3.5" />
      </Button>
      <Button
        size="icon"
        variant="ghost"
        class="h-7 w-7"
        :class="{ 'bg-muted': currentAlign === 'right' }"
        @click="updateAlign('right')"
      >
        <AlignRight class="w-3.5 h-3.5" />
      </Button>
    </div>
        
    <Button
      size="sm"
      variant="ghost"
      class="h-7 px-2 flex items-center gap-1.5"
      @click="$emit('openProperties')"
    >
      <SettingsIcon class="w-3.5 h-3.5" />
      <span class="text-xs">Properties</span>
    </Button>
  </bubble-menu>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import { Button } from '@/components/ui';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import SettingsIcon from 'lucide-vue-next/dist/esm/icons/settings.js';
import type { Editor } from '@tiptap/vue-3';

const props = defineProps<{
    editor: Editor | undefined;
}>();

defineEmits<{
    (e: 'openProperties'): void;
}>();

 
const shouldShow = (props: any) => {
    const { editor } = props;
    return editor.isActive('image') || editor.isActive('video');
};

const currentAlign = computed(() => {
    if (!props.editor) return 'center';
    const type = props.editor.isActive('image') ? 'image' : 'video';
    const attrs = props.editor.getAttributes(type);
    return attrs.textAlign || attrs.align || 'center';
});

function updateAlign(align: string) {
    if (!props.editor) return;
    props.editor.chain().focus().setTextAlign(align).run();
}
</script>
