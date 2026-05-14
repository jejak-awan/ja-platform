<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('modules.cms.settings.title') }}
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        {{ $t('modules.cms.settings.description') }}
      </p>
    </div>

    <div
      v-if="loading"
      class="bg-card border border-border rounded-lg p-12 text-center"
    >
      <p class="text-muted-foreground">
        {{ $t('modules.cms.settings.loading') }}
      </p>
    </div>

    <div
      v-else
      class="w-full"
    >
      <!-- Shadcn Tabs -->
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0 flex-wrap">
            <TabsTrigger 
              v-for="tab in tabs" 
              :key="tab.id" 
              :value="tab.id"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <component
                :is="getTabIcon(tab.id)"
                class="w-4 h-4 mr-2"
              />
              {{ $t('modules.cms.settings.tabs.' + tab.id) }}
            </TabsTrigger>
          </TabsList>
        </div>

        <!-- Tab Content -->
        <form
          class="space-y-6"
          @submit.prevent="handleSubmit"
        >
          <div
            v-if="currentSettings.length === 0"
            class="text-center py-8"
          >
            <p class="text-muted-foreground">
              {{ $t('modules.cms.settings.noSettings') }}
            </p>
          </div>

          <template v-else>
            <TabsContent value="identity">
              <IdentityTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="comments">
              <DiscussionTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="seo">
              <SeoTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="analytics">
              <AnalyticsTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>
          </template>

          <!-- Actions -->
          <div class="flex justify-end space-x-4 pt-6 border-t">
            <Button
              type="button"
              variant="outline"
              @click="resetForm"
            >
              {{ $t('modules.cms.settings.reset') }}
            </Button>
            <Button
              type="submit"
              :disabled="saving || !isDirty"
            >
              {{ saving ? $t('modules.cms.settings.saving') : $t('modules.cms.settings.save') }}
            </Button>
          </div>
        </form>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch, defineAsyncComponent } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
    Button
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useCmsStore } from '@/modules/Cms/stores/cms';
import type { SettingValue } from '@/engine/types/settings';
// Async Tab Components
const IdentityTab = defineAsyncComponent(() => import('./tabs/IdentityTab.vue'));
const SeoTab = defineAsyncComponent(() => import('./tabs/SeoTab.vue'));
const DiscussionTab = defineAsyncComponent(() => import('./tabs/DiscussionTab.vue'));
const AnalyticsTab = defineAsyncComponent(() => import('./tabs/AnalyticsTab.vue'));

// SVG Icons
const UserCog = { template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>` };
const MessageSquare = { template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>` };
const Search = { template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>` };
const LineChart = { template: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>` };

interface Setting {
    id: number | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
    description?: string;
    is_public?: number;
}

interface Tab {
    id: string;
    label: string;
}

const { t } = useI18n();
const toast = useToast();
const route = useRoute();
const cmsStore = useCmsStore();

const loading = ref(false);
const saving = ref(false);
// Initialize tab from query param if present
const validTabs = ['identity', 'seo', 'comments', 'analytics'];
const initialTab = validTabs.includes(route.query.tab as string) ? (route.query.tab as string) : 'identity';
const activeTab = ref(initialTab);
const settings = ref<Setting[]>([]);
const formData = ref<Record<string, SettingValue>>({});
const initialFormData = ref<Record<string, SettingValue>>({}); // Track initial state
const errors = ref<Record<string, string[]>>({});

const isDirty = computed(() => {
    // Compare only keys present in currentSettings to handle tab switching correctly
    const currentKeys = currentSettings.value.map(s => s.key);
    for (const key of currentKeys) {
        if (JSON.stringify(formData.value[key]) !== JSON.stringify(initialFormData.value[key])) {
            return true;
        }
    }
    return false;
});





import { useAuthStore } from '@/modules/Core/stores/auth';

const authStore = useAuthStore();
const tabs = computed<Tab[]>(() => {
    const allTabs: Tab[] = [
        { id: 'identity', label: t('modules.cms.settings.tabs.identity') },
        { id: 'comments', label: t('modules.cms.settings.tabs.comments') },
        { id: 'seo', label: t('modules.cms.settings.tabs.seo') },
        { id: 'analytics', label: t('modules.cms.settings.tabs.analytics') },
    ];

    if (authStore.isAtLeastRole('super')) {
        return allTabs;
    }

    const operationalTabs = ['identity', 'comments'];
    return allTabs.filter(tab => operationalTabs.includes(tab.id));
});

const getTabIcon = (tabId: string) => {
    switch (tabId) {
        case 'identity': return UserCog;
        case 'comments': return MessageSquare;
        case 'seo': return Search;
        case 'analytics': return LineChart;
        default: return UserCog;
    }
};

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    const group = activeTab.value === 'identity' ? 'general' : activeTab.value;
    return settings.value.filter(s => s && s.group === group);
});


const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/settings');
        const { data } = parseResponse(response);
        settings.value = ensureArray(data) as Setting[];

        // Inject missing CDN settings with defaults
        const ensureSetting = (key: string, value: unknown, type: string, group: string, description = '') => {
            if (!settings.value.find(s => s.key === key)) {
                settings.value.push({
                    id: 'temp_' + key,
                    key,
                    value,
                    type,
                    group,
                    description,
                    is_public: 0
                });
            }
        };

        // Inject CMS-domain defaults only
        // Analytics retention (CMS content analytics)
        ensureSetting('analytics_retention_days', 90, 'integer', 'analytics');
        ensureSetting('analytics_event_retention_days', 60, 'integer', 'analytics');
        ensureSetting('analytics_visitor_retention_days', 30, 'integer', 'analytics');

        // Identity / Branding (CMS domain — group 'general')
        ensureSetting('site_logo', '', 'image', 'general', 'Site Logo');
        ensureSetting('site_favicon', '', 'image', 'general', 'Site Favicon');

        initializeFormData();
    } catch (error: unknown) {
        logger.error('Failed to fetch settings:', error);
        settings.value = [];
    } finally {
        loading.value = false;
    }
};

const initializeFormData = () => {
    formData.value = {};
    settings.value.forEach(setting => {
        let value = setting.value;
        
        // Cast value based on type
        if (setting.type === 'boolean') {
            value = value === '1' || value === 1 || value === 'true' || value === true;
        } else if (setting.type === 'integer') {
            value = value ? parseInt(String(value)) : null;
        } else if (setting.type === 'json') {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                    value = JSON.stringify(value, null, 2);
                } catch {
                    // Invalid JSON, keep original string value
                }
            } else {
                value = JSON.stringify(value, null, 2);
            }
        }
        
        formData.value[setting.key] = value as SettingValue;
    });
    initialFormData.value = JSON.parse(JSON.stringify(formData.value));
};

const resetForm = () => {
    initializeFormData();
};

const handleSubmit = async () => {
    saving.value = true;
    errors.value = {};
    try {
        // Prepare settings array for bulk update
        const settingsToUpdate = currentSettings.value.map(setting => {
            let value = formData.value[setting.key];
            
            // Handle JSON type
            if (setting.type === 'json' && typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch {
                    // Invalid JSON, keep original value
                }
            }
            
            return {
                key: setting.key,
                value: value,
                type: setting.type,
                group: setting.group,
            };
        });

        await api.post('/admin/cms/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        
        toast.success.save();
        await fetchSettings();
        await cmsStore.fetchSettingsGroup(activeTab.value); // Force refresh store for reactivity

        initialFormData.value = JSON.parse(JSON.stringify(formData.value));
    } catch (error: unknown) {
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { status: number; data?: { errors?: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                errors.value = err.response.data?.errors || {};
            } else {
                toast.error.fromResponse(error);
            }
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

watch(activeTab, () => {
    // Reset form when switching tabs
    initializeFormData();
});

onMounted(() => {
    fetchSettings();
});
</script>

