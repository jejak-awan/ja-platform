<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('modules.core.settings.title') }}
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        {{ $t('modules.core.settings.description') }}
      </p>
    </div>

    <div
      v-if="loading"
      class="bg-card border border-border rounded-lg p-12 text-center"
    >
      <p class="text-muted-foreground">
        {{ $t('modules.core.settings.loading') }}
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
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <component
                :is="getTabIcon(tab.id)"
                class="w-4 h-4 mr-2"
              />
              {{ $t('modules.core.settings.tabs.' + tab.id) }}
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
              {{ $t('modules.core.settings.noSettings') }}
            </p>
          </div>

          <template v-else>
            <TabsContent value="system">
              <GeneralTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="security">
              <SecurityTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>



            <TabsContent value="performance">
              <PerformanceTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
                :cache-status="cacheStatus"
                :clearing-cache="clearingCache"
                :warming-cache="warmingCache"
                @clear-cache="clearSystemCache"
                @warm-cache="warmSystemCache"
              />
            </TabsContent>

            <TabsContent value="email">
              <div class="space-y-6">
                <EmailTab
                  v-model:form-data="formData"
                  :settings="settings"
                  :errors="errors"
                  :validating-config="validatingConfig"
                  :config-validation="configValidation"
                  :testing-connection="testingConnection"
                  :connection-result="connectionResult"
                  @validate-config="validateEmailConfig"
                  @test-connection="testSmtpConnection"
                />

                <EmailTestSection
                  :sending-test-email="sendingTestEmail"
                  :test-email-result="testEmailResult"
                  :test-email="testEmail"
                  :queue-status="queueStatus"
                  :loading-queue-status="loadingQueueStatus"
                  :email-logs="emailLogs"
                  :loading-logs="loadingLogs"
                  @send-test-email="sendTestEmail"
                  @refresh-queue="getQueueStatus"
                  @refresh-logs="getRecentLogs"
                  @update:test-email="testEmail = $event"
                />
              </div>
            </TabsContent>

            <TabsContent value="media">
              <MediaTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="monitoring">
              <MonitoringTab
                v-model:form-data="formData"
                :settings="settings"
                :errors="errors"
              />
            </TabsContent>

            <TabsContent value="ai">
              <AiTab
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
              {{ $t('modules.core.settings.reset') }}
            </Button>
            <Button
              type="submit"
              :disabled="saving || !isDirty"
            >
              {{ saving ? $t('modules.core.settings.saving') : $t('modules.core.settings.save') }}
            </Button>
          </div>
        </form>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, watch, defineAsyncComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import api from '@/engine/api/client';
import { parseResponse, parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
    Button
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useCoreStore } from '@/modules/Core/stores/core';
import { useAuthStore } from '@/modules/Core/stores/auth';
import type { CacheStatus, QueueStatus, EmailLog, SettingValue } from '@/engine/types/settings';
import SettingsIcon from 'lucide-vue-next/dist/esm/icons/settings.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import Activity from 'lucide-vue-next/dist/esm/icons/activity.js';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';

// Async Tab Components
const GeneralTab = defineAsyncComponent(() => import('./tabs/GeneralTab.vue'));
const EmailTab = defineAsyncComponent(() => import('./tabs/EmailTab.vue'));
const SecurityTab = defineAsyncComponent(() => import('./tabs/SecurityTab.vue'));
const PerformanceTab = defineAsyncComponent(() => import('./tabs/PerformanceTab.vue'));
const MediaTab = defineAsyncComponent(() => import('./tabs/MediaTab.vue'));
const AiTab = defineAsyncComponent(() => import('./tabs/AiTab.vue'));
const MonitoringTab = defineAsyncComponent(() => import('./tabs/MonitoringTab.vue'));
const EmailTestSection = defineAsyncComponent(() => import('./EmailTestSection.vue'));

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
const authStore = useAuthStore();
const coreStore = useCoreStore();
const route = useRoute();
const { confirm } = useConfirm();
const toast = useToast();

const loading = ref(false);
const saving = ref(false);
// Initialize tab from query param if present (e.g., ?tab=performance)
const validTabs = ['system', 'security', 'performance', 'monitoring', 'email', 'media', 'ai'];
const initialTab = validTabs.includes(route.query.tab as string) ? (route.query.tab as string) : 'system';
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

// Email testing state
const validatingConfig = ref(false);
const configValidation = ref<{ valid: boolean; errors: string[]; warnings: string[] } | null>(null);
const testingConnection = ref(false);
const connectionResult = ref<{ connected: boolean; host: string; port: string; error?: string } | null>(null);
const sendingTestEmail = ref(false);
const testEmailResult = ref<{ success: boolean; message: string } | null>(null);
const testEmail = ref({
    to: '',
    subject: '',
    message: '',
});
const loadingQueueStatus = ref(false);
const queueStatus = ref<QueueStatus | null>(null);
const loadingLogs = ref(false);
const emailLogs = ref<EmailLog[]>([]);

// Cache Management State
const cacheStatus = ref<CacheStatus | null>(null);
const clearingCache = ref(false);
const warmingCache = ref(false);

const tabs = computed<Tab[]>(() => {
    const allTabs: Tab[] = [
        { id: 'system', label: 'System' },
        { id: 'security', label: 'Security' },
        { id: 'performance', label: 'Performance' },
        { id: 'monitoring', label: 'Monitoring' },
        { id: 'media', label: 'Media' },
        { id: 'ai', label: 'AI Assistance' },
        { id: 'email', label: 'Email' },
    ];

    // If super admin, show all tabs
    if (authStore.isAtLeastRole('super')) {
        return allTabs;
    }

    // Otherwise, only show operational tabs
    const operationalTabs = ['system', 'media'];
    return allTabs.filter(tab => operationalTabs.includes(tab.id));
});

const getTabIcon = (tabId: string) => {
    switch (tabId) {
        case 'system': return SettingsIcon;
        case 'security': return Shield;
        case 'performance': return Activity;
        case 'monitoring': return Activity;
        case 'email': return Mail;
        case 'media': return ImageIcon;
        case 'ai': return Sparkles;
        default: return SettingsIcon;
    }
};

const currentSettings = computed(() => {
    if (!settings.value || !Array.isArray(settings.value)) {
        return [];
    }
    // System tab also shows brand settings
    if (activeTab.value === 'system') {
        return settings.value.filter(s => s && (s.group === 'system' || s.group === 'brand'));
    }
    return settings.value.filter(s => s && s.group === activeTab.value);
});


const fetchSettings = async () => {
    try {
        loading.value = true;
        const response = await api.get('/admin/core/settings');
        const { data } = parseResponse(response);
        const rawSettings = ensureArray(data) as Setting[];

        // De-duplicate settings by key and prepare final array
        const uniqueSettingsMap = new Map<string, Setting>();
        
        // Define special keys that should be forced into specific groups
        const performanceKeys = ['enable_cache', 'cache_driver', 'cache_ttl', 'enable_cdn', 'cdn_url', 'cdn_preset', 'cdn_included_dirs', 'cdn_excluded_extensions'];
        const mediaKeys = [
            'storage_driver', 'max_upload_size', 'allowed_image_types', 'allowed_file_types',
            'thumbnail_width', 'thumbnail_height', 'enable_watermark', 'watermark_text',
            'aws_access_key_id', 'aws_secret_access_key', 'aws_default_region', 'aws_bucket', 'aws_endpoint',
            'google_client_id', 'google_client_secret', 'google_refresh_token', 'google_folder_id',
            'ftp_host', 'ftp_username', 'ftp_password', 'ftp_root', 'ftp_port', 'ftp_ssl',
            'dropbox_authorization_token'
        ];
        const systemKeys = [
            'maintenance_mode', 'maintenance_title', 'maintenance_message', 'maintenance_countdown_enabled', 'maintenance_end_time',
            'timezone', 'date_format', 'time_format', 'items_per_page', 'license_key', 'license_type'
        ];
        const securityKeys = [
            'abuseipdb_api_key', 'threat_intel_auto_block_threshold', 'telegram_bot_token', 'telegram_chat_id',
            'email_to', 'webhook_url'
        ];
        const brandKeys = ['admin_email', 'app_name', 'brand_logo', 'brand_favicon', 'branding_display'];

        rawSettings.forEach(s => {
            if (s && s.key && !uniqueSettingsMap.has(s.key)) {
                if (performanceKeys.includes(s.key)) {
                    s.group = 'performance';
                } else if (mediaKeys.includes(s.key)) {
                    s.group = 'media';
                } else if (systemKeys.includes(s.key)) {
                    s.group = 'system';
                } else if (securityKeys.includes(s.key)) {
                    s.group = 'security';
                } else if (brandKeys.includes(s.key)) {
                    s.group = 'brand';
                }
                uniqueSettingsMap.set(s.key, s);
            }
        });

        settings.value = Array.from(uniqueSettingsMap.values());

        const ensureSetting = (key: string, defaultValue: any, type: string, group: string, description = '') => {
            if (!uniqueSettingsMap.has(key)) {
                settings.value.push({
                    id: 'temp_' + key,
                    key,
                    value: defaultValue,
                    group,
                    type,
                    description: description || ''
                });
            }
        };

        // Ensure Performance Settings
        ensureSetting('enable_cache', true, 'boolean', 'performance');
        ensureSetting('cache_driver', 'file', 'string', 'performance');
        ensureSetting('cache_ttl', 3600, 'integer', 'performance');
        ensureSetting('cdn_preset', 'custom', 'string', 'performance');
        ensureSetting('cdn_included_dirs', 'assets, storage', 'string', 'performance');
        ensureSetting('cdn_excluded_extensions', '.php, .json', 'string', 'performance');

        // Ensure Media Settings
        ensureSetting('aws_access_key_id', '', 'string', 'media');
        ensureSetting('aws_secret_access_key', '', 'password', 'media');
        ensureSetting('aws_default_region', 'us-east-1', 'string', 'media');
        ensureSetting('aws_bucket', '', 'string', 'media');
        ensureSetting('aws_endpoint', '', 'string', 'media');
        ensureSetting('google_client_id', '', 'string', 'media');
        ensureSetting('google_client_secret', '', 'password', 'media');
        ensureSetting('google_refresh_token', '', 'password', 'media');
        ensureSetting('google_folder_id', '', 'string', 'media');
        ensureSetting('ftp_host', '', 'string', 'media');
        ensureSetting('ftp_username', '', 'string', 'media');
        ensureSetting('ftp_password', '', 'password', 'media');
        ensureSetting('ftp_root', '', 'string', 'media');
        ensureSetting('ftp_port', 21, 'integer', 'media');
        ensureSetting('ftp_ssl', false, 'boolean', 'media');
        ensureSetting('dropbox_authorization_token', '', 'password', 'media');
        ensureSetting('storage_driver', 'local', 'string', 'media');
        ensureSetting('max_upload_size', 10240, 'integer', 'media');
        ensureSetting('allowed_image_types', 'jpg,jpeg,png,webp,gif', 'string', 'media');
        ensureSetting('allowed_file_types', 'pdf,doc,docx,xls,xlsx,zip,rar', 'string', 'media');
        ensureSetting('thumbnail_width', 300, 'integer', 'media');
        ensureSetting('thumbnail_height', 300, 'integer', 'media');
        ensureSetting('enable_watermark', false, 'boolean', 'media');
        ensureSetting('watermark_text', 'JA-Platform', 'string', 'media');

        // Ensure Security Settings
        ensureSetting('abuseipdb_api_key', '', 'password', 'security');
        ensureSetting('threat_intel_auto_block_threshold', 75, 'integer', 'security');
        ensureSetting('telegram_bot_token', '', 'password', 'security');
        ensureSetting('telegram_chat_id', '', 'string', 'security');
        ensureSetting('email_to', '', 'string', 'security');
        ensureSetting('webhook_url', '', 'string', 'security');

        // Ensure System Settings
        ensureSetting('maintenance_mode', false, 'boolean', 'system');
        ensureSetting('maintenance_title', 'Coming Soon', 'string', 'system');
        ensureSetting('maintenance_message', 'We are currently working on something awesome.', 'text', 'system');
        ensureSetting('maintenance_countdown_enabled', false, 'boolean', 'system');
        ensureSetting('maintenance_end_time', '', 'string', 'system');
        ensureSetting('timezone', 'Asia/Jakarta', 'string', 'system');
        ensureSetting('date_format', 'Y-m-d', 'string', 'system');
        ensureSetting('time_format', 'H:i:s', 'string', 'system');
        ensureSetting('items_per_page', 20, 'integer', 'system');
        ensureSetting('license_key', 'senja@jejakawan', 'string', 'system');
        ensureSetting('license_type', 'Pro+', 'string', 'system');

        // Ensure Brand Settings
        ensureSetting('admin_email', '', 'string', 'brand');
        ensureSetting('app_name', 'JA-Platform', 'string', 'brand');
        ensureSetting('brand_logo', '', 'image', 'brand');
        ensureSetting('brand_favicon', '', 'image', 'brand');
        ensureSetting('branding_display', 'logo', 'string', 'brand');

        // Ensure AI Settings
        ensureSetting('ai_enabled', true, 'boolean', 'ai');
        ensureSetting('gemini_api_key', '', 'password', 'ai');

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

        await api.post('/admin/core/settings/bulk-update', {
            settings: settingsToUpdate,
        });
        
        toast.success.save();
        await fetchSettings();
        await coreStore.fetchSettingsGroup(activeTab.value); // Force refresh store for reactivity

        // Refresh cache status if on performance tab
        if (activeTab.value === 'performance') {
            getCacheStatus();
        }
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

// Email testing functions
const validateEmailConfig = async () => {
    validatingConfig.value = true;
    configValidation.value = null;
    try {
        const response = await api.get('/admin/core/email-test/validate-config');
        configValidation.value = parseSingleResponse<{ valid: boolean; errors: string[]; warnings: string[] }>(response);
    } catch (error: unknown) {
        let errorMsg = t('modules.core.settings.emailTest.failed');
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            errorMsg = err.response?.data?.message || errorMsg;
        }
        configValidation.value = {
            valid: false,
            errors: [errorMsg],
            warnings: [],
        };
    } finally {
        validatingConfig.value = false;
    }
};

const testSmtpConnection = async () => {
    testingConnection.value = true;
    connectionResult.value = null;
    try {
        const response = await api.post('/admin/core/email-test/test-connection');
        connectionResult.value = parseSingleResponse<{ connected: boolean; host: string; port: string; error?: string }>(response);
    } catch (error: unknown) {
        let errorMsg = t('modules.core.settings.emailTest.failed');
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            errorMsg = err.response?.data?.message || errorMsg;
        }
        connectionResult.value = {
            connected: false,
            host: 'unknown',
            port: 'unknown',
            error: errorMsg,
        };
    } finally {
        testingConnection.value = false;
    }
};

const sendTestEmail = async () => {
    if (!testEmail.value.to) {
        testEmailResult.value = {
            success: false,
            message: t('modules.core.settings.emailTest.recipientRequired'),
        };
        return;
    }

    sendingTestEmail.value = true;
    testEmailResult.value = null;
    try {
        const response = await api.post('/admin/core/email-test/send-test', {
            to: testEmail.value.to,
            subject: testEmail.value.subject || undefined,
            message: testEmail.value.message || undefined,
        });
        const message = response.data?.message;
        testEmailResult.value = {
            success: true,
            message: message || t('modules.core.settings.emailTest.sentSuccess'),
        };
        // Clear form
        testEmail.value.subject = '';
        testEmail.value.message = '';
        // Refresh logs
        await getRecentLogs();
    } catch (error: unknown) {
        let errorMsg = t('modules.core.settings.emailTest.sendFailed');
        if (typeof error === 'object' && error !== null && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            errorMsg = err.response?.data?.message || errorMsg;
        }
        testEmailResult.value = {
            success: false,
            message: errorMsg,
        };
    } finally {
        sendingTestEmail.value = false;
    }
};

const getQueueStatus = async () => {
    loadingQueueStatus.value = true;
    try {
        const response = await api.get('/admin/core/email-test/queue-status');
        queueStatus.value = parseSingleResponse<QueueStatus>(response);
    } catch {
        queueStatus.value = {
            driver: 'unknown',
            connection: 'unknown',
            pending_jobs: 'error',
            failed_jobs: 'error',
        };
    } finally {
        loadingQueueStatus.value = false;
    }
};

const getRecentLogs = async () => {
    loadingLogs.value = true;
    try {
        const response = await api.get('/admin/core/email-test/recent-journal?limit=10');
        const parsed = parseSingleResponse<{ logs: EmailLog[] }>(response);
        emailLogs.value = parsed?.logs || [];
    } catch {
        emailLogs.value = [];
    } finally {
        loadingLogs.value = false;
    }
};

// Cache Management Methods
const getCacheStatus = async () => {
    try {
        const response = await api.get('/admin/core/system/cache-status');
        cacheStatus.value = parseSingleResponse<CacheStatus>(response);
    } catch (error: unknown) {
        logger.error('Failed to get cache status:', error);
    }
};

const clearSystemCache = async () => {
    const confirmed = await confirm({
        title: t('modules.core.settings.cache.clearTitle', 'Clear Cache'),
        message: 'Are you sure you want to clear the system cache?',
        variant: 'warning',
        confirmText: t('modules.core.settings.cache.clearConfirm', 'Clear Cache'),
    });

    if (!confirmed) return;
    
    clearingCache.value = true;
    try {
        await api.post('/admin/core/system/cache/clear');
        toast.success.action(t('modules.core.settings.cache.cleared'));
        getCacheStatus();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        clearingCache.value = false;
    }
};

const warmSystemCache = async () => {
    warmingCache.value = true;
    try {
        await api.post('/admin/core/system/cache/warm');
        toast.success.action(t('modules.core.settings.cache.warmed'));
        getCacheStatus();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        warmingCache.value = false;
    }
};

watch(activeTab, (newTab) => {
    // Reset form when switching tabs
    initializeFormData();
    // Load email test data when switching to email tab
    if (newTab === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (newTab === 'performance') {
        getCacheStatus();
    }
});

onMounted(() => {
    fetchSettings();
    // Load email test data if email tab is active
    if (activeTab.value === 'email') {
        getQueueStatus();
        getRecentLogs();
    } else if (activeTab.value === 'performance') {
        getCacheStatus();
    }
});
</script>

