<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-foreground">
        {{ t('features.developer.webhooks.title') }}
      </h1>
      <button
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/80"
        @click="showCreateModal = true"
      >
        <Plus class="w-5 h-5 mr-2" />
        {{ t('features.developer.webhooks.create') }}
      </button>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"
    >
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <Zap class="h-8 w-8 text-indigo-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.developer.webhooks.stats.total') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.total || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CheckCircle class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.developer.webhooks.stats.active') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.active || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <BarChart3 class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.developer.webhooks.stats.total_calls') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.total_calls || 0 }}
            </p>
          </div>
        </div>
      </div>
      <div class="bg-card border border-border rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <AlertCircle class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">
              {{ t('features.developer.webhooks.stats.failed_calls') }}
            </p>
            <p class="text-2xl font-semibold text-foreground">
              {{ statistics.failed_calls || 0 }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-card border border-border rounded-lg">
      <div class="px-6 py-4 border-b border-border">
        <div class="flex items-center space-x-4">
          <input
            v-model="search"
            type="text"
            :placeholder="t('features.developer.webhooks.search')"
            class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
        </div>
      </div>

      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.developer.webhooks.loading') }}
        </p>
      </div>

      <div
        v-else-if="filteredWebhooks.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('features.developer.webhooks.empty') }}
        </p>
      </div>

      <table
        v-else
        class="min-w-full divide-y divide-border"
      >
        <thead class="bg-muted">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.name') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.url') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.events') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.calls') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.status') }}
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
              {{ t('features.developer.webhooks.table.actions') }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-card divide-y divide-border">
          <tr
            v-for="webhook in filteredWebhooks"
            :key="webhook.id"
            class="hover:bg-muted"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-foreground">
                {{ webhook.name }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-foreground font-mono truncate max-w-xs">
                {{ webhook.url }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="event in (webhook.events || [])"
                  :key="event"
                  class="px-2 py-1 text-xs bg-secondary text-secondary-foreground rounded"
                >
                  {{ t('features.developer.webhooks.events.' + event) }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
              {{ webhook.total_calls || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="webhook.is_active ? 'bg-green-500/20 text-green-400' : 'bg-secondary text-secondary-foreground'"
              >
                {{ webhook.is_active ? t('features.developer.plugins.status.active') : t('features.developer.plugins.status.inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <button
                  class="text-blue-600 hover:text-blue-900"
                  @click="testWebhook(webhook)"
                >
                  {{ t('features.developer.webhooks.actions.test') }}
                </button>
                <button
                  class="text-indigo-600 hover:text-indigo-900"
                  @click="editWebhook(webhook)"
                >
                  {{ t('features.developer.webhooks.actions.edit') }}
                </button>
                <button
                  class="text-red-600 hover:text-red-900"
                  @click="deleteWebhook(webhook)"
                >
                  {{ t('features.developer.webhooks.actions.delete') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <WebhookModal
      v-if="showCreateModal || showEditModal"
      :webhook="editingWebhook"
      @close="closeModal"
      @saved="handleWebhookSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import WebhookModal from '@/modules/Infra/components/webhooks/WebhookModal.vue';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import CheckCircle from 'lucide-vue-next/dist/esm/icons/circle-check.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

interface Webhook {
    id: string | number;
    name: string;
    url: string;
    events: string[];
    total_calls: number;
    failed_calls: number;
    is_active: boolean;
    secret?: string;
    created_at?: string;
}

interface WebhookStats {
    total: number;
    active: number;
    total_calls: number;
    failed_calls: number;
}

const webhooks = ref<Webhook[]>([]);
const statistics = ref<WebhookStats | null>(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWebhook = ref<Webhook | null>(null);

const filteredWebhooks = computed(() => {
    if (!search.value) return webhooks.value;
    
    const searchLower = search.value.toLowerCase();
    return webhooks.value.filter((webhook: Webhook) => 
        webhook.name.toLowerCase().includes(searchLower) ||
        webhook.url.toLowerCase().includes(searchLower)
    );
});

const fetchWebhooks = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/webhooks');
        const { data } = parseResponse(response);
        webhooks.value = ensureArray(data);
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/manage/webhooks/statistics');
            statistics.value = parseSingleResponse<WebhookStats>(statsResponse);
        } catch {
            // Calculate from webhooks if endpoint doesn't exist
            statistics.value = {
                total: webhooks.value.length,
                active: webhooks.value.filter(w => w.is_active).length,
                total_calls: webhooks.value.reduce((sum, w) => sum + (w.total_calls || 0), 0),
                failed_calls: webhooks.value.reduce((sum, w) => sum + (w.failed_calls || 0), 0),
            };
        }
        } catch (err: unknown) {
            logger.error('Failed to fetch webhooks:', err);
    } finally {
        loading.value = false;
    }
};

const editWebhook = (webhook: Webhook) => {
    editingWebhook.value = webhook;
    showEditModal.value = true;
};

const testWebhook = async (webhook: Webhook) => {
    try {
        await api.post(`/manage/webhooks/${webhook.id}/test`);
        toast.success.action(t('features.developer.webhooks.messages.test_success'));
    } catch (error: unknown) {
        logger.error('Failed to test webhook:', error);
        toast.error.fromResponse(error);
    }
};

const deleteWebhook = async (webhook: Webhook) => {
    const confirmed = await confirm({
        title: t('features.developer.webhooks.actions.delete'),
        message: t('features.developer.webhooks.confirm.delete', { name: webhook.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/webhooks/${webhook.id}`);
        toast.success.delete('Webhook');
        fetchWebhooks();
    } catch (error: unknown) {
        logger.error('Failed to delete webhook:', error);
        toast.error.delete(error, 'Webhook');
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingWebhook.value = null;
};

const handleWebhookSaved = () => {
    fetchWebhooks();
    closeModal();
};

onMounted(() => {
    fetchWebhooks();
});
</script>

