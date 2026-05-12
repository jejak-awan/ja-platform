<template>
  <Dialog
    :open="isOpen"
    @update:open="handleClose"
  >
    <DialogContent class="sm:max-w-md !z-[100050]">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <component 
            :is="variantIcons[variant]" 
            :class="variantColors[variant]"
            class="w-5 h-5" 
          />
          {{ title }}
        </DialogTitle>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>

      <div
        v-if="message"
        class="py-4"
      >
        <p class="text-sm text-muted-foreground whitespace-pre-wrap break-words">
          {{ message }}
        </p>
      </div>

      <div
        v-if="input"
        class="pb-4"
      >
        <Input
          v-model="inputValue"
          :placeholder="inputPlaceholder"
          class="w-full"
          autofocus
          @keyup.enter="handleConfirm"
        />
      </div>

      <DialogFooter class="gap-2 sm:gap-0">
        <Button
          variant="outline"
          @click="handleCancel"
        >
          {{ cancelText }}
        </Button>
        <Button
          :variant="confirmVariant"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import Dialog from './Dialog.vue';
import DialogContent from './DialogContent.vue';
import DialogDescription from './DialogDescription.vue';
import DialogFooter from './DialogFooter.vue';
import DialogHeader from './DialogHeader.vue';
import DialogTitle from './DialogTitle.vue';
import Input from './Input.vue';
import Button from './Button.vue';
import { useI18n } from 'vue-i18n';

type ConfirmVariant = 'warning' | 'danger' | 'destructive' | 'info' | 'question' | 'success';

const { t } = useI18n();

const props = withDefaults(defineProps<{
    isOpen?: boolean;
    title?: string;
    description?: string;
    message?: string;
    variant?: ConfirmVariant;
    confirmText?: string;
    cancelText?: string;
    input?: boolean;
    inputPlaceholder?: string;
}>(), {
    isOpen: false,
    title: undefined,
    description: undefined,
    message: undefined,
    variant: 'warning',
    confirmText: undefined,
    cancelText: undefined,
    input: false,
    inputPlaceholder: '',
});

const _isOpen = ref(false);
const emit = defineEmits<{
    'confirm': [value: string | boolean];
    'cancel': [];
    'update:isOpen': [value: boolean];
}>();

const _title = ref('');
const _description = ref('');
const _message = ref('');
const _variant = ref<ConfirmVariant>('warning');
const _confirmText = ref('');
const _cancelText = ref('');
const _input = ref(false);
const _inputPlaceholder = ref('');

const isOpen = computed(() => props.isOpen || _isOpen.value);
const title = computed(() => props.title || _title.value || t('common.messages.confirm.title'));
const description = computed(() => props.description || _description.value);
const message = computed(() => props.message || _message.value);
const variant = computed(() => props.variant || _variant.value);
const confirmText = computed(() => props.confirmText || _confirmText.value || t('common.labels.ok') || t('common.labels.save') || 'OK');
const cancelText = computed(() => props.cancelText || _cancelText.value || t('common.labels.cancel'));
const input = computed(() => props.input || _input.value);
const inputPlaceholder = computed(() => props.inputPlaceholder || _inputPlaceholder.value);

const resolvePromise = ref<((value: string | boolean) => void) | null>(null);

const confirm = (options: any) => {
    _title.value = options.title || 'Confirm';
    _description.value = options.description || options.message || '';
    _message.value = options.message || '';
    _variant.value = options.variant || 'warning';
    _confirmText.value = options.confirmText || 'OK';
    _cancelText.value = options.cancelText || 'Cancel';
    _input.value = options.input || false;
    _inputPlaceholder.value = options.inputPlaceholder || '';
    _isOpen.value = true;
    
    return new Promise<string | boolean>((resolve) => {
        resolvePromise.value = resolve;
    });
};

const handleConfirm = () => {
    const val = input.value ? inputValue.value : true;
    emit('confirm', val);
    if (resolvePromise.value) {
        resolvePromise.value(val);
        resolvePromise.value = null;
    }
    _isOpen.value = false;
    emit('update:isOpen', false);
};

const handleCancel = () => {
    emit('cancel');
    if (resolvePromise.value) {
        resolvePromise.value(false);
        resolvePromise.value = null;
    }
    _isOpen.value = false;
    emit('update:isOpen', false);
};

defineExpose({ confirm });

const inputValue = ref('');

// Reset input when modal opens
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        inputValue.value = '';
    }
});

import type { Component } from 'vue';

const variantIcons: Record<ConfirmVariant, Component> = {
    warning: AlertTriangle,
    danger: Trash2,
    destructive: Trash2,
    info: Info,
    question: HelpCircle,
    success: CheckCircle2
};

const variantColors: Record<ConfirmVariant, string> = {
    warning: 'text-amber-500',
    danger: 'text-destructive',
    destructive: 'text-destructive',
    info: 'text-blue-500',
    question: 'text-primary',
    success: 'text-emerald-500'
};

const confirmVariant = computed(() => {
    if (props.variant === 'danger' || props.variant === 'destructive') return 'destructive';
    return 'default';
});

const handleClose = (value: boolean) => {
    if (!value) {
        emit('cancel');
    }
    emit('update:isOpen', value);
};
</script>
