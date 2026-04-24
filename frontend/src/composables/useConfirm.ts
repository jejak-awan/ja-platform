import { ref } from 'vue';

export interface ConfirmOptions {
    title: string;
    message?: string; // Legacy support
    description?: string;
    variant?: 'warning' | 'danger' | 'destructive' | 'info' | 'question' | 'success';
    confirmText?: string;
    cancelText?: string;
    input?: boolean;
    inputPlaceholder?: string;
}

interface ConfirmState extends ConfirmOptions {
    isOpen: boolean;
    onConfirm: (val: string | boolean) => void;
    onCancel: () => void;
}

const confirmState = ref<ConfirmState>({
    isOpen: false,
    title: 'Confirm',
    message: '',
    description: '',
    variant: 'warning',
    confirmText: 'OK',
    cancelText: 'Cancel',
    onConfirm: () => { },
    onCancel: () => { }
});

export function useConfirm() {
    const confirm = (options: ConfirmOptions): Promise<string | boolean> => {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title: options.title || 'Confirm',
                message: options.message || '',
                description: options.description || '',
                variant: options.variant || 'warning',
                confirmText: options.confirmText || 'OK',
                cancelText: options.cancelText || 'Cancel',
                input: options.input || false,
                onConfirm: (val?: string | boolean) => {
                    resolve(options.input ? (val ?? '') : true);
                    confirmState.value.isOpen = false;
                },
                onCancel: () => {
                    resolve(false);
                    confirmState.value.isOpen = false;
                }
            };
        });
    };

    return {
        confirmState,
        confirm
    };
}
