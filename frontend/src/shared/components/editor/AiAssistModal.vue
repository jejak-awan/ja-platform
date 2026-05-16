<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Sparkles class="w-5 h-5 text-indigo-500" />
          <span>Magic AI Assist</span>
        </DialogTitle>
        <DialogDescription>
          Use AI to improve your writing. Select an option or type a custom command.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <!-- Presets -->
        <div class="grid grid-cols-2 gap-2">
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand('Fix grammar and spelling')"
          >
            <CheckCircle2 class="w-4 h-4 text-green-500" />
            Fix Grammar
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand('Paraphrase this text to be more engaging')"
          >
            <RefreshCw class="w-4 h-4 text-blue-500" />
            Re-write
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand('Summarize this text in 1 sentence')"
          >
            <FileText class="w-4 h-4 text-orange-500" />
            Summarize
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand('Expand this text with more details')"
          >
            <Maximize2 class="w-4 h-4 text-purple-500" />
            Expand
          </Button>
        </div>

        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t" />
          </div>
          <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-background px-2 text-muted-foreground">Or type custom command</span>
          </div>
        </div>

        <!-- Custom Input -->
        <div class="flex gap-2">
          <Input
            v-model="customPrompt"
            url=""
            placeholder="e.g. Translate to Indonesian..."
            @keydown.enter="handleCommand(customPrompt)"
          />
          <Button
            :disabled="!customPrompt || loading"
            @click="handleCommand(customPrompt)"
          >
            <ArrowRight class="w-4 h-4" />
          </Button>
        </div>

        <!-- Loading State -->
        <div
          v-if="loading"
          class="flex items-center justify-center p-4 text-sm text-muted-foreground animate-pulse"
        >
          <Sparkles class="w-4 h-4 mr-2 animate-spin" />
          Generating magic...
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref } from 'vue';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Maximize2 from 'lucide-vue-next/dist/esm/icons/maximize.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogDescription,
    Button,
    Input
} from '@/shared/components/ui';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';

interface AiGenerateResponse {
    content: string;
}

const props = defineProps<{
    open: boolean;
    context?: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'result', content: string): void;
}>();

const toast = useToast();

const customPrompt = ref('');
const loading = ref(false);

const handleCommand = async (prompt: string) => {
    if (!prompt) return;
    
    loading.value = true;
    try {
        const response = await api.post<AiGenerateResponse>('/manage/cms/ai/generate', {
            prompt: prompt,
            context: props.context
        });

        if (response.data?.content) {
            emit('result', response.data.content);
            emit('update:open', false);
            customPrompt.value = '';
        }
    } catch (error: unknown) {
        logger.error('AI Ops Error:', error);
        const err = error as { response?: { data?: { message?: string } } };
        toast.service.error('AI Error', err.response?.data?.message || 'Failed to generate content.');
    } finally {
        loading.value = false;
    }
};
</script>
