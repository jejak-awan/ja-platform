<template>
  <div class="space-y-6">
    <Card class="overflow-hidden">
      <!-- Filters & Actions -->
      <div class="px-6 py-4 border-b border-border/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <!-- Left: Search / Filters -->
          <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
            <div class="relative w-full md:w-72">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                v-model="search"
                :placeholder="t('modules.cms.content_templates.search')"
                class="pl-9"
                @input="handleSearch"
              />
            </div>
            <Select
              v-model="typeFilter"
              @update:model-value="fetchTemplates"
            >
              <SelectTrigger class="w-[140px]">
                <SelectValue :placeholder="t('modules.cms.content_templates.types.all')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">
                  {{ t('modules.cms.content_templates.types.all') }}
                </SelectItem>
                <SelectItem value="post">
                  {{ t('modules.cms.content_templates.types.post') }}
                </SelectItem>
                <SelectItem value="page">
                  {{ t('modules.cms.content_templates.types.page') }}
                </SelectItem>
                <SelectItem value="custom">
                  {{ t('modules.cms.content_templates.types.custom') }}
                </SelectItem>
              </SelectContent>
            </Select>
            <Select
              v-model="trashedFilter"
              @update:model-value="fetchTemplates"
            >
              <SelectTrigger class="w-[140px]">
                <SelectValue :placeholder="t('common.labels.status')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="without">
                  {{ t('common.labels.activeOnly') }}
                </SelectItem>
                <SelectItem value="with">
                  {{ t('common.labels.includesTrashed') }}
                </SelectItem>
                <SelectItem value="only">
                  {{ t('common.labels.trashedOnly') }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <div
              v-if="selectedTemplates.length > 0"
              class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1 mr-2"
            >
              <span class="text-xs font-semibold text-primary uppercase tracking-wider">
                {{ t('modules.cms.content_templates.table.selected', { count: selectedTemplates.length }) }}
              </span>
              <div class="h-4 w-px bg-primary/20" />
              <Select
                v-model="bulkAction"
                @update:model-value="handleBulkAction"
              >
                <SelectTrigger class="w-[130px] h-7 border-primary/20 text-xs shadow-none">
                  <SelectValue :placeholder="t('common.labels.bulkAction')" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    value="delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ t('common.actions.delete') }}
                  </SelectItem>
                  <SelectItem
                    value="restore"
                    class="text-emerald-600 focus:text-emerald-600"
                  >
                    {{ t('common.actions.restore') }}
                  </SelectItem>
                  <SelectItem
                    value="force_delete"
                    class="text-destructive focus:text-destructive"
                  >
                    {{ t('common.actions.forceDelete') }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Create Button -->
            <Button
              v-if="isEmbedded && authStore.hasPermission('manage content templates')"
              as-child
              size="sm"
            >
              <router-link
                :to="{ name: 'content-templates.create' }"
                class="flex items-center"
              >
                <Plus class="w-4 h-4 mr-1" />
                {{ t('modules.cms.content_templates.create') }}
              </router-link>
            </Button>
          </div>
        </div>
      </div>
      <CardContent class="p-0">
        <div
          v-if="loading && templates.length === 0"
          class="p-12 text-center"
        >
          <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('modules.cms.content_templates.loading') }}
          </p>
        </div>

        <div
          v-else-if="templates.length === 0"
          class="p-12 text-center"
        >
          <FileText class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
          <p class="text-muted-foreground font-medium">
            {{ t('modules.cms.content_templates.empty') }}
          </p>
        </div>

        <div
          v-else
          class="relative overflow-x-auto"
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-12 px-6">
                  <Checkbox
                    :checked="allSelected"
                    @update:checked="toggleSelectAll"
                  />
                </TableHead>
                <TableHead class="text-xs text-muted-foreground/70">
                  {{ t('modules.cms.content_templates.table.name') }}
                </TableHead>
                <TableHead class="text-xs text-muted-foreground/70">
                  {{ t('modules.cms.content_templates.table.type') }}
                </TableHead>
                <TableHead class="text-xs text-muted-foreground/70">
                  {{ t('modules.cms.content_templates.table.description') }}
                </TableHead>
                <TableHead class="text-xs text-muted-foreground/70">
                  {{ t('modules.cms.content_templates.table.updated') }}
                </TableHead>
                <TableHead class="text-center text-xs text-muted-foreground/70">
                  {{ t('modules.cms.content_templates.table.actions') }}
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="template in templates"
                :key="template.id"
                class="group"
              >
                <TableCell class="px-6">
                  <Checkbox
                    :checked="selectedTemplates.includes(template.id)"
                    @update:checked="(checked) => toggleSelection(template.id, checked)"
                  />
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                    {{ template.name }}
                    <Badge
                      v-if="template.deleted_at"
                      variant="destructive"
                      class="h-4.5 text-[10px] px-1.5 uppercase font-bold tracking-wider"
                    >
                      {{ t('common.labels.deleted') }}
                    </Badge>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge
                    variant="secondary"
                    class="capitalize"
                  >
                    {{ template.type ? t(`modules.cms.content_templates.types.${template.type}`) : t('modules.cms.content_templates.types.post') }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div
                    class="text-sm truncate max-w-xs"
                    :title="template.description"
                  >
                    {{ template.description || '-' }}
                  </div>
                </TableCell>
                <TableCell class="text-sm">
                  {{ formatDate(template.updated_at) }}
                </TableCell>
                <TableCell class="text-center">
                  <div class="flex justify-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      :title="t('modules.cms.content_templates.actions.createContent')"
                      class="h-8 w-8 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:bg-emerald-500/10"
                      @click="createFromTemplate(template)"
                    >
                      <CopyPlus class="w-4 h-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      as-child
                      class="h-8 w-8 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:bg-indigo-500/10"
                    >
                      <router-link :to="{ name: 'content-templates.edit', params: { id: template.id } }">
                        <Pencil class="w-4 h-4" />
                      </router-link>
                    </Button>
                    <Button
                      v-if="template.deleted_at"
                      variant="ghost"
                      size="icon"
                      :title="t('common.actions.restore')"
                      class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10"
                      @click="handleRestore(template)"
                    >
                      <RotateCcw class="w-4 h-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      :title="template.deleted_at ? t('common.actions.forceDelete') : t('modules.cms.content_templates.actions.delete')"
                      class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                      @click="handleDelete(template)"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination -->
        <Pagination
          v-if="pagination && pagination.total > 0"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :per-page="Number(perPage)"
          class="border-none shadow-none mt-4 px-6 py-4"
          @page-change="(p: number) => fetchTemplates(p)"
          @update:per-page="(val) => { perPage = String(val); fetchTemplates(1); }"
        />
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray, parseSingleResponse, type PaginationData } from '@/shared/utils/responseParser';
import { debounce } from '@/shared/utils/debounce';
import { useAuthStore } from '@/modules/Core/stores/auth';
import { Badge, Button, Card, CardContent, Checkbox, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import CopyPlus from 'lucide-vue-next/dist/esm/icons/copy-plus.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';

interface Template {
    id: number;
    name: string;
    type: string;
    description?: string;
    updated_at: string;
    deleted_at?: string | null;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();
const router = useRouter();
const authStore = useAuthStore();

defineProps<{
    isEmbedded: boolean;
}>();
const templates = ref<Template[]>([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('all');
const trashedFilter = ref('without');
const pagination = ref<PaginationData | null>(null); 
const perPage = ref('10');
const selectedTemplates = ref<number[]>([]);
const bulkAction = ref('');

const allSelected = computed(() => {
    return templates.value.length > 0 && selectedTemplates.value.length === templates.value.length;
});

const handleSearch = debounce(() => {
    fetchTemplates(1);
}, 300);

const fetchTemplates = async (page: number | string = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: perPage.value,
            // If 'all', send specific classic types to filter on server
            type: typeFilter.value !== 'all' ? typeFilter.value : 'post,page,custom',
            search: search.value,
            trashed: trashedFilter.value !== 'without' ? trashedFilter.value : undefined
        };

        const response = await api.get('/admin/cms/content-templates', { params });
        const { data, pagination: pag } = parseResponse(response);
        
        templates.value = ensureArray(data);
        pagination.value = pag;
        selectedTemplates.value = []; // Reset selection on page change
    } catch (error: unknown) {
        logger.error('Failed to fetch templates:', error);
        templates.value = [];
    } finally {
        loading.value = false;
    }
};

const createFromTemplate = async (template: Template) => {
    try {
        const response = await api.post(`/admin/cms/content-templates/${template.id}/create-content`);
        const content = parseSingleResponse<{ id: string | number }>(response);
        if (content && content.id) {
            toast.success.createFromTemplate();
            router.push({ name: 'contents.edit', params: { id: content.id } });
        }
    } catch (error: unknown) {
        logger.error('Failed to create content from template:', error);
        toast.error.templateCreateContent(error as Record<string, unknown>);
    }
};

const handleDelete = async (template: Template) => {
    const isTrashed = !!template.deleted_at;
    const confirmed = await confirm({
        title: isTrashed ? t('common.actions.forceDelete') : t('modules.cms.content_templates.actions.delete'),
        message: isTrashed 
            ? t('modules.cms.content_templates.messages.forceDeleteConfirm', { name: template.name })
            : t('modules.cms.content_templates.messages.deleteConfirm', { name: template.name }),
        variant: 'danger',
        confirmText: isTrashed ? t('common.actions.forceDelete') : t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        if (isTrashed) {
            await api.delete(`/admin/cms/content-templates/${template.id}/force-delete`);
            toast.success.action(t('common.messages.success.deleted', { item: t('modules.cms.content_templates.title_singular') }));
        } else {
            await api.delete(`/admin/cms/content-templates/${template.id}`);
            toast.success.delete(t('modules.cms.content_templates.title_singular'));
        }
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error: unknown) {
        logger.error('Failed to delete template:', error);
        toast.error.delete(error, t('modules.cms.content_templates.title_singular'));
    }
};

const handleRestore = async (template: Template) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: t('modules.cms.content_templates.messages.restoreConfirm', { name: template.name }),
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/cms/content-templates/${template.id}/restore`);
        toast.success.restore(t('modules.cms.content_templates.title_singular'));
        await fetchTemplates(pagination.value?.current_page || 1);
    } catch (error: unknown) {
        logger.error('Failed to restore template:', error);
        toast.error.fromResponse(error);
    }
};

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedTemplates.value = templates.value.map(t => t.id);
    } else {
        selectedTemplates.value = [];
    }
};

const toggleSelection = (id: number, checked: boolean) => {
    if (checked) {
        selectedTemplates.value.push(id);
    } else {
        selectedTemplates.value = selectedTemplates.value.filter(tId => tId !== id);
    }
};

const handleBulkAction = async () => {
    if (!bulkAction.value || selectedTemplates.value.length === 0) return;

    const action = bulkAction.value;
    const count = selectedTemplates.value.length;

    if (action === 'delete' || action === 'force_delete') {
        const isForce = action === 'force_delete';
        const confirmed = await confirm({
            title: isForce ? t('common.actions.forceDelete') : t('modules.cms.content_templates.actions.bulkDelete'),
            message: isForce 
                ? t('common.messages.confirm.bulkAction', { action: t('common.actions.forceDelete'), count: count })
                : t('common.messages.confirm.bulkAction', { action: t('common.actions.delete'), count: count }),
            variant: 'danger',
            confirmText: isForce ? t('common.actions.forceDelete') : t('common.actions.delete'),
        });

        if (!confirmed) {
            bulkAction.value = '';
            return;
        }

        try {
            await api.post('/admin/cms/content-templates/bulk-action', {
                action: action,
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.delete(t('modules.cms.content_templates.title', { count: count }));
        } catch (error: unknown) {
            logger.error('Bulk action failed:', error);
            toast.error.action(error as Record<string, unknown>);
        }
    } else if (action === 'restore') {
        try {
            await api.post('/admin/cms/content-templates/bulk-action', {
                action: 'restore',
                ids: selectedTemplates.value
            });
            await fetchTemplates(pagination.value?.current_page || 1);
            bulkAction.value = '';
            toast.success.restore(t('modules.cms.content_templates.title', { count: count }));
        } catch (error: unknown) {
            logger.error('Bulk action failed:', error);
            toast.error.action(error as Record<string, unknown>);
        }
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    fetchTemplates();
});
</script>

