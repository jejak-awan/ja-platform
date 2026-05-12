/**
 * Admin Theme Customizer (route `themes.customizer` → ThemeCustomizerWorkspace.vue).
 * Bindings persist as `settings.theme_data_bindings`.
 */
import { computed, ref, watch } from 'vue';
import api from '@/core/api/client';
import toast from '@/shared/services/legacy-toast';
import { JANARI_PRESETS, type JanariPresetKey } from '@/modules/Cms/config/janariPresets';
import type { ComponentBindings } from '@/modules/Cms/composables/useThemeDataBindings';
import { THEME_DATA_BINDINGS_KEY, isPlainSettingsObject } from '@/modules/Cms/constants/themeBindings';

function omitThemeBindingKeys(settings: Record<string, unknown>): Record<string, unknown> {
    const next = { ...settings };
    delete next[THEME_DATA_BINDINGS_KEY];
    return next;
}

type TranslateFn = (key: string, ...args: unknown[]) => string;

export function useThemeCustomizer(slug: string, t: TranslateFn) {
    const theme = ref<any>(null);
    const loading = ref(true);
    const saving = ref(false);
    const formValues = ref<Record<string, unknown>>({});
    const customCss = ref('');
    const bindings = ref<Record<string, ComponentBindings>>({});

    const initialDataSnapshot = ref('');
    const history = ref<string[]>([]);
    const historyIndex = ref(-1);
    const isInternalChange = ref(false);

    const isDirty = computed(() => {
        const current = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
        return current !== initialDataSnapshot.value;
    });

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    function pushCurrentStateToHistory() {
        const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
        if (history.value[historyIndex.value] === state) return;
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }
        history.value.push(state);
        if (history.value.length > 50) history.value.shift();
        else historyIndex.value++;
    }

    function saveHistory() {
        if (isInternalChange.value) return;
        pushCurrentStateToHistory();
    }

    function restoreState(stateStr: string) {
        isInternalChange.value = true;
        const state = JSON.parse(stateStr);
        formValues.value = JSON.parse(JSON.stringify(state.f));
        customCss.value = state.c;
        bindings.value = JSON.parse(JSON.stringify(state.b));
        setTimeout(() => {
            isInternalChange.value = false;
        }, 0);
    }

    function undo() {
        if (!canUndo.value) return;
        historyIndex.value--;
        const state = history.value[historyIndex.value];
        if (state) restoreState(state);
    }

    function redo() {
        if (!canRedo.value) return;
        historyIndex.value++;
        const state = history.value[historyIndex.value];
        if (state) restoreState(state);
    }

    function recordSettingChange(key: string, val: unknown) {
        formValues.value[key] = val;

        if (key === 'color_primary' && formValues.value.color_preset !== 'custom') {
            const currentPreset = String(formValues.value.color_preset || '');
            const presetColor = JANARI_PRESETS[currentPreset as JanariPresetKey]?.light.toLowerCase();
            if (String(val).toLowerCase() !== presetColor) {
                formValues.value.color_preset = 'custom';
            }
        }

        if (key === 'color_preset' && val !== 'custom') {
            const presetColor = JANARI_PRESETS[val as JanariPresetKey]?.light;
            if (presetColor) {
                formValues.value.color_primary = presetColor;
            }
        }

        saveHistory();
    }

    watch(customCss, (next, prev) => {
        if (!isInternalChange.value && next !== prev) saveHistory();
    });

    async function fetchThemeData() {
        loading.value = true;
        try {
            const response = await api.get(`/admin/cms/themes/${slug}`);
            theme.value = response.data;

            const defaults: Record<string, unknown> = {};
            const schema = theme.value?.manifest?.settings_schema || {};
            Object.keys(schema).forEach((k) => {
                if (schema[k]) defaults[k] = schema[k].default ?? '';
            });

            const rawSettings = (theme.value?.settings || {}) as Record<string, unknown>;
            formValues.value = { ...defaults, ...omitThemeBindingKeys(rawSettings) };
            customCss.value = theme.value?.custom_css || '';

            const rawBindings = rawSettings[THEME_DATA_BINDINGS_KEY];
            if (isPlainSettingsObject(rawBindings)) {
                bindings.value = JSON.parse(JSON.stringify(rawBindings)) as Record<string, ComponentBindings>;
            } else {
                bindings.value = {};
            }

            const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
            initialDataSnapshot.value = state;
            history.value = [state];
            historyIndex.value = 0;
        } catch {
            toast.error(t('features.theme_customizer.messages.error'), t('features.theme_customizer.messages.init_failed'));
        } finally {
            loading.value = false;
        }
    }

    async function saveAll() {
        saving.value = true;
        try {
            const payload = {
                ...formValues.value,
                [THEME_DATA_BINDINGS_KEY]: bindings.value,
            };
            await api.put(`/admin/cms/themes/${slug}/customization`, {
                settings: payload,
                custom_css: customCss.value,
            });

            const state = JSON.stringify({ f: formValues.value, c: customCss.value, b: bindings.value });
            initialDataSnapshot.value = state;
            toast.success(t('features.theme_customizer.messages.success'), t('features.theme_customizer.messages.published'));
        } catch {
            toast.error(t('features.theme_customizer.messages.error'), t('features.theme_customizer.messages.publish_failed'));
        } finally {
            saving.value = false;
        }
    }

    function resetToInitial() {
        restoreState(initialDataSnapshot.value);
        pushCurrentStateToHistory();
    }

    function resetToDefaults() {
        if (!theme.value?.manifest?.settings_schema) return;
        const defaults: Record<string, unknown> = {};
        const schema = theme.value.manifest.settings_schema;
        Object.keys(schema).forEach((k) => {
            if (schema[k]) defaults[k] = schema[k].default ?? '';
        });

        isInternalChange.value = true;
        formValues.value = defaults;
        customCss.value = '';
        bindings.value = {};
        pushCurrentStateToHistory();
        setTimeout(() => {
            isInternalChange.value = false;
        }, 0);
        toast.info(t('features.theme_customizer.messages.info'), t('features.theme_customizer.messages.reset_done'));
    }

    return {
        theme,
        loading,
        saving,
        formValues,
        customCss,
        bindings,
        isDirty,
        canUndo,
        canRedo,
        fetchThemeData,
        saveAll,
        resetToInitial,
        resetToDefaults,
        undo,
        redo,
        saveHistory,
        recordSettingChange,
    };
}
