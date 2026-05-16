<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('features.redirects.title') }}
      </h1>
      <Button
        @click="showCreateModal = true"
      >
        <Plus class="w-5 h-5 mr-2" />
        {{ $t('features.redirects.new') }}
      </Button>
    </div>

    <!-- Statistics -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"
    >
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-indigo-500/10 rounded-lg">
              <ArrowRightLeft class="h-6 w-6 text-indigo-500" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.redirects.statistics.total') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ statistics.total || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-500/10 rounded-lg">
              <CheckCircle2 class="h-6 w-6 text-green-500" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.redirects.statistics.active') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ statistics.active || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-500/10 rounded-lg">
              <BarChart3 class="h-6 w-6 text-blue-500" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-muted-foreground">
                {{ $t('features.redirects.statistics.hits') }}
              </p>
              <p class="text-2xl font-bold text-foreground">
                {{ statistics.total_hits || 0 }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <div class="px-6 py-4 border-b">
        <div class="relative max-w-sm">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="search"
            type="text"
            :placeholder="$t('features.redirects.search')"
            class="pl-10"
          />
        </div>
      </div>

      <div
        v-if="loading"
        class="p-12 text-center"
      >
        <Loader2 class="w-8 h-8 animate-spin mx-auto text-primary mb-4" />
        <p class="text-muted-foreground">
          {{ $t('features.redirects.loading') }}
        </p>
      </div>

      <div
        v-else-if="filteredRedirects.length === 0"
        class="p-12 text-center"
      >
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-muted mb-4">
          <Search class="h-6 w-6 text-muted-foreground" />
        </div>
        <p class="text-muted-foreground">
          {{ $t('features.redirects.empty') }}
        </p>
      </div>

      <Table v-else>
        <TableHeader>
          <TableRow>
            <TableHead>{{ $t('features.redirects.table.from') }}</TableHead>
            <TableHead>{{ $t('features.redirects.table.to') }}</TableHead>
            <TableHead>{{ $t('features.redirects.table.code') }}</TableHead>
            <TableHead>{{ $t('features.redirects.table.hits') }}</TableHead>
            <TableHead>{{ $t('features.redirects.table.status') }}</TableHead>
            <TableHead class="text-right">
              {{ $t('features.redirects.table.actions') }}
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="redirect in filteredRedirects"
            :key="redirect.id"
          >
            <TableCell>
              <div class="text-sm font-medium text-foreground">
                {{ redirect.from_url }}
              </div>
            </TableCell>
            <TableCell>
              <div class="text-sm text-foreground">
                {{ redirect.to_url }}
              </div>
            </TableCell>
            <TableCell>
              <Badge
                variant="secondary"
                class="font-mono bg-blue-500/10 text-blue-500 hover:bg-blue-500/20"
              >
                {{ redirect.status_code || 301 }}
              </Badge>
            </TableCell>
            <TableCell class="text-sm text-muted-foreground">
              {{ redirect.hits || 0 }}
            </TableCell>
            <TableCell>
              <Badge
                :variant="redirect.is_active ? 'default' : 'secondary'"
                :class="redirect.is_active ? 'bg-green-500/10 text-green-500 hover:bg-green-500/20' : ''"
              >
                {{ redirect.is_active ? $t('features.redirects.status.active') : $t('features.redirects.status.inactive') }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <div class="flex justify-end gap-2">
                <Button
                  variant="ghost"
                  size="icon"
                  class="text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50"
                  @click="editRedirect(redirect)"
                >
                  <Pencil class="w-4 h-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="text-red-600 hover:text-red-700 hover:bg-red-50"
                  @click="deleteRedirect(redirect)"
                >
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>

    <!-- Create/Edit Modal -->
    <RedirectModal
      v-if="showCreateModal || showEditModal"
      :redirect="editingRedirect"
      @close="closeModal"
      @saved="handleRedirectSaved"
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
import RedirectModal from '@/modules/Cms/components/redirects/RedirectModal.vue';
import { Badge, Button, Card, CardContent, Input, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import ArrowRightLeft from 'lucide-vue-next/dist/esm/icons/arrow-right-left.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { parseResponse, ensureArray, parseSingleResponse } from '@/shared/utils/responseParser';

interface Redirect {
    id: string | string;
    from_url: string;
    to_url: string;
    status_code: number;
    hits: number;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

interface RedirectStatistics {
    total: number;
    active: number;
    total_hits: number;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const redirects = ref<Redirect[]>([]);
const statistics = ref<RedirectStatistics | null>(null);
const loading = ref(false);
const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRedirect = ref<Redirect | null>(null);

const filteredRedirects = computed(() => {
    if (!search.value) return redirects.value;
    
    const searchLower = search.value.toLowerCase();
    return redirects.value.filter(redirect => 
        redirect.from_url.toLowerCase().includes(searchLower) ||
        redirect.to_url.toLowerCase().includes(searchLower)
    );
});

const fetchRedirects = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/cms/redirects');
        const { data } = parseResponse(response);
        redirects.value = ensureArray(data) as Redirect[];
        
        // Fetch statistics
        try {
            const statsResponse = await api.get('/manage/cms/redirects/statistics');
            statistics.value = parseSingleResponse(statsResponse) as RedirectStatistics;
        } catch (error: unknown) {
            // Calculate from redirects if endpoint doesn't exist
            statistics.value = {
                total: redirects.value.length,
                active: redirects.value.filter(r => r.is_active).length,
                total_hits: redirects.value.reduce((sum, r) => sum + (r.hits || 0), 0),
            };
            logger.error('Failed to fetch statistics:', error);
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch redirects:', error);
    } finally {
        loading.value = false;
    }
};

const editRedirect = (redirect: Redirect) => {
    editingRedirect.value = redirect;
    showEditModal.value = true;
};

const deleteRedirect = async (redirect: Redirect) => {
    const confirmed = await confirm({
        title: t('features.redirects.actions.delete'),
        message: t('features.redirects.messages.deleteConfirm', { from: redirect.from_url }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/cms/redirects/${redirect.id}`);
        toast.success.delete(t('features.redirects.title'));
        fetchRedirects();
    } catch (error: unknown) {
        logger.error('Failed to delete redirect:', error);
        toast.error.delete(error, t('features.redirects.title'));
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingRedirect.value = null;
};

const handleRedirectSaved = () => {
    fetchRedirects();
    closeModal();
};

onMounted(() => {
    fetchRedirects();
});
</script>

