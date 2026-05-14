<template>
  <TooltipProvider>
    <div>
      <!-- Header -->
      <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('modules.cms.forms.title') }}
        </h1>
        <router-link :to="{ name: 'forms.create' }">
          <Button>
            <Plus class="w-5 h-5 mr-2" />
            {{ $t('modules.cms.forms.actions.create') }}
          </Button>
        </router-link>
      </div>

      <!-- Filters -->
      <Card class="p-4 mb-4">
        <div class="flex items-center gap-4">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              v-model="search"
              type="text"
              :placeholder="$t('modules.cms.forms.filters.search')"
              class="pl-9"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="w-[180px]">
              <SelectValue :placeholder="$t('modules.cms.forms.filters.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('modules.cms.forms.filters.status') }}
              </SelectItem>
              <SelectItem value="active">
                {{ $t('modules.cms.forms.filters.active') }}
              </SelectItem>
              <SelectItem value="inactive">
                {{ $t('modules.cms.forms.filters.inactive') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="trashedFilter">
            <SelectTrigger class="w-[180px]">
              <SelectValue :placeholder="$t('common.labels.status')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="without">
                {{ $t('common.labels.activeOnly') }}
              </SelectItem>
              <SelectItem value="with">
                {{ $t('common.labels.includesTrashed') }}
              </SelectItem>
              <SelectItem value="only">
                {{ $t('common.labels.trashedOnly') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <!-- View Toggle -->
          <div class="flex items-center gap-1 p-1 border border-border/40 rounded-xl bg-muted/30">
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8"
              :class="{ 'bg-background shadow-sm': viewMode === 'card' }"
              :title="$t('common.actions.view') + ' (Card)'"
              @click="viewMode = 'card'"
            >
              <LayoutGrid class="w-4 h-4" />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8"
              :class="{ 'bg-background shadow-sm': viewMode === 'list' }"
              :title="$t('common.actions.view') + ' (List)'"
              @click="viewMode = 'list'"
            >
              <List class="w-4 h-4" />
            </Button>
          </div>

          <!-- Bulk Actions -->
          <div
            v-if="selectedIds.length > 0"
            class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 transition-opacity animate-in fade-in slide-in-from-top-1 ml-auto"
          >
            <span class="text-sm font-medium text-foreground whitespace-nowrap">
              {{ selectedIds.length }} {{ $t('modules.cms.forms.bulk.selected') }}
            </span>
            <div class="h-4 w-px bg-border mx-2" />
            <Button
              variant="ghost"
              size="sm"
              class="text-destructive hover:bg-destructive/10"
              @click="handleBulkDelete"
            >
              <Trash2 class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.forms.actions.delete') }}
            </Button>
          </div>
        </div>
      </Card>

      <!-- Loading State -->
      <div
        v-if="loading"
        class="bg-card border border-border rounded-lg p-12 text-center"
      >
        <Loader2 class="w-8 h-8 mx-auto animate-spin text-muted-foreground" />
        <p class="text-muted-foreground mt-2">
          {{ $t('modules.cms.forms.messages.loading') }}
        </p>
      </div>

      <!-- Empty State -->
      <Card
        v-else-if="filteredForms.length === 0"
        class="p-12 text-center"
      >
        <FileText class="mx-auto h-12 w-12 text-muted-foreground opacity-50" />
        <p class="mt-4 text-muted-foreground">
          {{ $t('modules.cms.forms.messages.empty') }}
        </p>
        <router-link :to="{ name: 'forms.create' }">
          <Button class="mt-4">
            {{ $t('modules.cms.forms.actions.createFirst') }}
          </Button>
        </router-link>
      </Card>

      <!-- Card View -->
      <div
        v-else-if="viewMode === 'card'"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
      >
        <Card
          v-for="form in filteredForms"
          :key="form.id"
          class="overflow-hidden hover:shadow-md"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-start gap-3 flex-1">
                <Checkbox 
                  :checked="selectedIds.includes(form.id)"
                  class="mt-1"
                  @update:checked="toggleSelection(form.id)"
                />
                <div>
                  <h3 class="text-lg font-semibold text-foreground">
                    {{ form.name }}
                    <span
                      v-if="form.deleted_at"
                      class="ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive uppercase tracking-wide"
                    >
                      {{ $t('common.labels.deleted') }}
                    </span>
                  </h3>
                  <p class="text-sm text-muted-foreground mt-1">
                    {{ form.slug }}
                  </p>
                </div>
              </div>
              <Badge
                :variant="form.is_active ? 'success' : 'secondary'"
              >
                {{ form.is_active ? $t('modules.cms.forms.filters.active') : $t('modules.cms.forms.filters.inactive') }}
              </Badge>
            </div>

            <p
              v-if="form.description"
              class="text-sm text-muted-foreground mb-4 line-clamp-2"
            >
              {{ form.description }}
            </p>

            <div class="grid grid-cols-2 gap-y-2 text-sm text-muted-foreground mb-4 border-t border-border/40 pt-4">
              <div class="flex items-center">
                <Tag class="w-4 h-4 mr-2 opacity-70" />
                <span>{{ form.fields_count || 0 }} fields</span>
              </div>
              <div
                class="flex items-center"
                :title="$t('modules.cms.forms.stats.views', { count: form.view_count || 0 })"
              >
                <Eye class="w-4 h-4 mr-2 opacity-70" />
                <span>{{ $t('modules.cms.forms.stats.views', { count: form.view_count || 0 }) }}</span>
              </div>
              <div
                class="flex items-center"
                :title="$t('modules.cms.forms.stats.starts', { count: form.start_count || 0 })"
              >
                <MousePointer2 class="w-4 h-4 mr-2 opacity-70" />
                <span>{{ $t('modules.cms.forms.stats.starts', { count: form.start_count || 0 }) }}</span>
              </div>
              <div
                class="flex items-center font-medium text-foreground"
                :title="$t('modules.cms.forms.stats.submissions', { count: form.submission_count || 0 })"
              >
                <MessageSquare class="w-4 h-4 mr-2 opacity-70" />
                <span>{{ $t('modules.cms.forms.stats.submissions', { count: form.submission_count || 0 }) }}</span>
              </div>
              <div
                class="col-span-2 flex items-center pt-1"
                :title="$t('modules.cms.forms.stats.conversion', { rate: calculateConversion(form) })"
              >
                <TrendingUp class="w-4 h-4 mr-2 opacity-70 text-emerald-500" />
                <span class="text-xs font-semibold text-emerald-600">
                  {{ $t('modules.cms.forms.stats.conversion', { rate: calculateConversion(form) }) }}
                </span>
              </div>
            </div>

            <div class="flex items-center space-x-2 pt-4 border-t border-border">
              <Button
                size="sm"
                class="flex-1 transition-colors"
                @click="editForm(form)"
              >
                <Pencil class="w-4 h-4 mr-1" />
                {{ $t('modules.cms.forms.actions.edit') }}
              </Button>
              <Button
                variant="secondary"
                size="sm"
                class="flex-1"
                @click="viewSubmissions(form)"
              >
                <Inbox class="w-4 h-4 mr-1" />
                {{ $t('modules.cms.forms.actions.submissions') }}
              </Button>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50"
                    @click="openDuplicateDialog(form)"
                  >
                    <Copy class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ $t('modules.cms.forms.actions.duplicate') }}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8"
                    @click="toggleFormStatus(form)"
                  >
                    <Ban
                      v-if="form.is_active"
                      class="w-4 h-4"
                    />
                    <Check
                      v-else
                      class="w-4 h-4"
                    />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ form.is_active ? $t('common.actions.deactivate') : $t('common.actions.activate') }}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-blue-500 hover:text-blue-600 hover:bg-blue-50"
                    @click="openShareDialog(form)"
                  >
                    <Share2 class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ $t('modules.cms.forms.actions.share') }}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip v-if="!form.deleted_at">
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive"
                    @click="deleteForm(form)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ $t('common.actions.delete') }}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip v-if="form.deleted_at">
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                    @click="restoreForm(form)"
                  >
                    <RotateCcw class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ $t('common.actions.restore') }}</p>
                </TooltipContent>
              </Tooltip>

              <Tooltip v-if="form.deleted_at">
                <TooltipTrigger as-child>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                    @click="forceDeleteForm(form)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{{ $t('common.actions.forceDelete') }}</p>
                </TooltipContent>
              </Tooltip>
            </div>
          </div>
        </Card>
      </div>

      <div v-else>
        <DataTable
          :table="table"
          :loading="loading"
          :empty-message="$t('modules.cms.forms.messages.empty')"
        />
      </div>


      <!-- Share Dialog -->
      <Dialog v-model:open="showShareDialog">
        <DialogContent class="max-w-md">
          <DialogHeader>
            <DialogTitle>{{ $t('modules.cms.forms.share.title') }}</DialogTitle>
          </DialogHeader>
          <div class="space-y-6 pt-4">
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ $t('modules.cms.forms.share.publicUrl') }}</label>
              <div class="flex gap-2">
                <Input
                  :value="publicUrl"
                  readonly
                  class="bg-muted/30"
                />
                <Button
                  size="icon"
                  variant="outline"
                  @click="copyToClipboard(publicUrl)"
                >
                  <Copy class="h-4 w-4" />
                </Button>
                <Button
                  size="icon"
                  variant="outline"
                  @click="visitPublicPage"
                >
                  <ExternalLink class="h-4 w-4" />
                </Button>
              </div>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ $t('modules.cms.forms.share.embedTag') }}</label>
              <div class="flex gap-2">
                <Input
                  :value="embedCode"
                  readonly
                  class="bg-muted/30"
                />
                <Button
                  size="icon"
                  variant="outline"
                  @click="copyToClipboard(embedCode)"
                >
                  <Copy class="h-4 w-4" />
                </Button>
              </div>
              <p class="text-[11px] text-muted-foreground italic">
                {{ $t('modules.cms.forms.share.embedDescription') }}
              </p>
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Duplicate Choice Dialog -->
      <Dialog v-model:open="showDuplicateDialog">
        <DialogContent class="max-w-md">
          <DialogHeader>
            <DialogTitle>{{ $t('modules.cms.forms.duplicate.title') }}</DialogTitle>
          </DialogHeader>
          <div class="p-4 space-y-4">
            <p class="text-sm text-muted-foreground">
              {{ $t('modules.cms.forms.duplicate.description', { name: duplicatingForm?.name }) }}
            </p>
                    
            <div class="grid gap-3">
              <button 
                class="flex items-start gap-4 p-4 rounded-xl border border-border bg-card hover:bg-primary/5 hover:border-primary/30 transition-all text-left group"
                @click="handleDuplicate(false)"
              >
                <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <LayoutTemplate class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-bold text-foreground">
                    {{ $t('modules.cms.forms.duplicate.structure') }}
                  </h4>
                  <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ $t('modules.cms.forms.duplicate.structure_desc') }}
                  </p>
                </div>
              </button>

              <button 
                class="flex items-start gap-4 p-4 rounded-xl border border-border bg-success/5 hover:border-success/30 transition-all text-left group"
                @click="handleDuplicate(true)"
              >
                <div class="h-10 w-10 rounded-full bg-success/10 text-success flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                  <Database class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="font-bold text-foreground">
                    {{ $t('modules.cms.forms.duplicate.withData') }}
                  </h4>
                  <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ $t('modules.cms.forms.duplicate.withData_desc', { count: duplicatingForm?.submission_count || 0 }) }}
                  </p>
                </div>
              </button>
            </div>

            <div class="pt-2">
              <Button
                variant="ghost"
                class="w-full"
                @click="showDuplicateDialog = false"
              >
                {{ $t('modules.cms.forms.duplicate.cancel') }}
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useConfirm } from '@/shared/composables/useConfirm';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { Badge, Button, Card, Checkbox, Dialog, DialogContent, DialogHeader, DialogTitle, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Tooltip, TooltipContent, TooltipProvider, TooltipTrigger, DataTable } from '@/shared/components/ui';
import { h } from 'vue';
import { 
    useVueTable, 
    getCoreRowModel, 
    createColumnHelper,
    getSortedRowModel,
    type SortingState
} from '@tanstack/vue-table';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Inbox from 'lucide-vue-next/dist/esm/icons/inbox.js';
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import RotateCcw from 'lucide-vue-next/dist/esm/icons/rotate-ccw.js';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import ExternalLink from 'lucide-vue-next/dist/esm/icons/external-link.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import MousePointer2 from 'lucide-vue-next/dist/esm/icons/mouse-pointer-2.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import TrendingUp from 'lucide-vue-next/dist/esm/icons/trending-up.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import Database from 'lucide-vue-next/dist/esm/icons/database.js';

import type { Form } from '@/modules/Cms/types/forms';

interface FormFilters {
    trashed?: string;
    [key: string]: string | number | undefined;
}

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

// State
const router = useRouter();
const forms = ref<Form[]>([]);
const loading = ref(true);
const search = ref('');
const statusFilter = ref('all');
const trashedFilter = ref('without');
const viewMode = ref('card');
const showShareDialog = ref(false);
const sharingForm = ref<Form | null>(null);
const showDuplicateDialog = ref(false);
const duplicatingForm = ref<Form | null>(null);
const duplicating = ref(false);

const columnHelper = createColumnHelper<Form>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (val) => table.toggleAllPageRowsSelected(!!val),
        }),
        cell: ({ row }) => h(Checkbox, {
            checked: row.getIsSelected(),
            'onUpdate:checked': (val) => row.toggleSelected(!!val),
        }),
        size: 50,
    }),
    columnHelper.accessor('name', {
        header: t('modules.cms.forms.modal.formName'),
        cell: ({ row }) => h('div', [
            h('p', { class: 'font-medium text-foreground' }, [
                row.original.name,
                row.original.deleted_at ? h('span', { class: 'ml-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-destructive/10 text-destructive uppercase tracking-wide' }, t('common.labels.deleted')) : null
            ]),
            row.original.description ? h('p', { class: 'text-sm text-muted-foreground line-clamp-1' }, row.original.description) : null
        ])
    }),
    columnHelper.accessor('slug', {
        header: t('modules.cms.forms.modal.slug'),
        cell: ({ row }) => h('code', { class: 'text-sm text-muted-foreground bg-muted px-2 py-1 rounded' }, row.original.slug)
    }),
    columnHelper.display({
        id: 'fields',
        header: () => h('div', { class: 'text-right' }, t('modules.cms.forms.stats.fields', { count: '' }).replace('{count}', '').trim()),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h('span', { class: 'text-sm text-muted-foreground' }, String(row.original.fields_count || 0))
        ])
    }),
    columnHelper.accessor('view_count', {
        header: () => h('div', { class: 'text-right' }, t('modules.cms.forms.stats.views', { count: '' }).replace(/^[0-9\s]+/, '').trim()),
        cell: ({ row }) => h('div', { class: 'text-right font-mono text-xs' }, String(row.original.view_count || 0))
    }),
    columnHelper.accessor('start_count', {
        header: () => h('div', { class: 'text-right' }, t('modules.cms.forms.stats.starts', { count: '' }).replace(/^[0-9\s]+/, '').trim()),
        cell: ({ row }) => h('div', { class: 'text-right font-mono text-xs' }, String(row.original.start_count || 0))
    }),
    columnHelper.accessor('submission_count', {
        header: () => h('div', { class: 'text-right' }, t('modules.cms.forms.actions.submissions')),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h('span', { class: 'font-medium px-2 py-0.5 rounded-full bg-primary/5 text-primary tracking-tight' }, String(row.original.submission_count || 0))
        ])
    }),
    columnHelper.display({
        id: 'conversion',
        header: () => h('div', { class: 'text-right' }, t('modules.cms.forms.stats.conversion', { rate: '' }).replace(/^[0-9%/\s]+/, '').trim()),
        cell: ({ row }) => h('div', { class: 'text-right' }, [
            h('span', { class: 'text-xs font-bold text-emerald-600' }, `${calculateConversion(row.original)}%`)
        ])
    }),
    columnHelper.accessor('is_active', {
        header: () => h('div', { class: 'text-center' }, 'Status'),
        cell: ({ row }) => h('div', { class: 'text-center' }, [
            h(Badge, { variant: row.original.is_active ? 'success' : 'secondary' }, row.original.is_active ? t('modules.cms.forms.filters.active') : t('modules.cms.forms.filters.inactive'))
        ])
    }),
    columnHelper.display({
        id: 'actions',
        header: () => h('div', { class: 'text-center' }, t('common.actions.title')),
        cell: ({ row }) => h('div', { class: 'flex items-center justify-center space-x-1' }, [
            h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => editForm(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-500/10' }, [h(Pencil, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('common.actions.edit')) })
                ]
            }),
            h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => viewSubmissions(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-blue-500 hover:text-blue-600 hover:bg-blue-500/10' }, [h(Inbox, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('modules.cms.forms.actions.submissions')) })
                ]
            }),
            h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => openDuplicateDialog(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50' }, [h(Copy, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('modules.cms.forms.actions.duplicate')) })
                ]
            }),
            h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => toggleFormStatus(row.original), variant: 'ghost', size: 'icon', class: row.original.is_active ? 'h-8 w-8 text-amber-500 hover:text-amber-600 hover:bg-amber-500/10' : 'h-8 w-8 text-emerald-500 hover:text-emerald-600 hover:bg-emerald-500/10' }, [
                            row.original.is_active ? h(Ban, { class: 'w-4 h-4' }) : h(Check, { class: 'w-4 h-4' })
                        ])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', row.original.is_active ? t('common.actions.deactivate') : t('common.actions.activate')) })
                ]
            }),
            h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: (e: Event) => { e.stopPropagation(); openShareDialog(row.original); }, variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-sky-500 hover:text-sky-600 hover:bg-sky-50' }, [h(Share2, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('modules.cms.forms.actions.share')) })
                ]
            }),
            !row.original.deleted_at && h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => deleteForm(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10' }, [h(Trash2, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('common.actions.delete')) })
                ]
            }),
            row.original.deleted_at && h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => restoreForm(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' }, [h(RotateCcw, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('common.actions.restore')) })
                ]
            }),
            row.original.deleted_at && h(Tooltip, {}, {
                default: () => [
                    h(TooltipTrigger, { asChild: true }, {
                        default: () => h(Button, { onClick: () => forceDeleteForm(row.original), variant: 'ghost', size: 'icon', class: 'h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10' }, [h(Trash2, { class: 'w-4 h-4' })])
                    }),
                    h(TooltipContent, {}, { default: () => h('p', t('common.actions.forceDelete')) })
                ]
            })
        ])
    })
];

const filteredForms = computed(() => {
    let result = forms.value;

    if (search.value) {
        const query = search.value.toLowerCase();
        result = result.filter(form =>
            form.name.toLowerCase().includes(query) ||
            form.slug.toLowerCase().includes(query) ||
            (form.description && form.description.toLowerCase().includes(query))
        );
    }

    if (statusFilter.value && statusFilter.value !== 'all') {
        const isActive = statusFilter.value === 'active';
        result = result.filter((form: Form) => form.is_active === isActive);
    }

    return result;
});

const sorting = ref<SortingState>([]);

const calculateConversion = (form: Form) => {
    if (!form.view_count || form.view_count === 0) return 0
    return Math.round(((form.submission_count || 0) / form.view_count) * 100)
}


const table = useVueTable({
    get data() { return filteredForms.value },
    columns,
    state: {
        get sorting() { return sorting.value }
    },
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getRowId: row => String(row.id),
});

const openShareDialog = (form: Form) => {
    sharingForm.value = form;
    showShareDialog.value = true;
};

const publicUrl = computed(() => {
    if (!sharingForm.value) return '';
    const baseUrl = window.location.origin;
    return `${baseUrl}/f/${sharingForm.value.slug}`;
});

const embedCode = computed(() => {
    if (!sharingForm.value) return '';
    return `[form slug="${sharingForm.value.slug}"]`;
});

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.success.default(t('modules.cms.forms.share.copied'));
};

const visitPublicPage = () => {
    if (publicUrl.value) {
        window.open(publicUrl.value, '_blank');
    }
};

const fetchForms = async () => {
    try {
        loading.value = true;
        
        const params: FormFilters = {};
        if (trashedFilter.value !== 'without') {
            params.trashed = trashedFilter.value;
        }

        const response = await api.get('/admin/cms/forms', { params });
        const { data } = parseResponse<Form>(response);
        forms.value = ensureArray<Form>(data);
    } catch (error: unknown) {
        logger.error('Error fetching forms:', error);
        forms.value = [];
    } finally {
        loading.value = false;
    }
};

const editForm = (form: Form) => {
    router.push({ name: 'forms.edit', params: { id: form.id } });
};

const viewSubmissions = (form: Form) => {
    router.push({ name: 'forms.submissions', params: { id: form.id } });
};

const toggleFormStatus = async (form: Form) => {
    try {
        const response = await api.put(`/admin/cms/forms/${form.id}`, {
            is_active: !form.is_active
        });
        const updatedForm = (response.data) as Form;
        const index = forms.value.findIndex((f: Form) => f.id === form.id);
        if (index !== -1) {
            forms.value[index] = updatedForm;
        }
        toast.success.action(t('common.messages.success.updated', { item: 'Form status' }));
    } catch (error: unknown) {
        logger.error('Error toggling form status:', error);
        toast.error.fromResponse(error);
    }
};

const deleteForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('modules.cms.forms.actions.delete'),
        message: t('modules.cms.forms.messages.deleteConfirm', { name: form.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/forms/${form.id}`);
        toast.success.delete('Form');
        fetchForms();
    } catch (error: unknown) {
        logger.error('Failed to delete form:', error);
        toast.error.fromResponse(error);
    }
};



const openDuplicateDialog = (form: Form) => {
    duplicatingForm.value = form;
    showDuplicateDialog.value = true;
};

const handleDuplicate = async (withSubmissions: boolean) => {
    if (!duplicatingForm.value) return;
    
    try {
        duplicating.value = true;
        await api.post(`/admin/cms/forms/${duplicatingForm.value.id}/duplicate`, {
            with_submissions: withSubmissions
        });
        toast.success.duplicate('Form');
        showDuplicateDialog.value = false;
        fetchForms();
    } catch (error: unknown) {
        logger.error('Failed to duplicate form:', error);
        toast.error.fromResponse(error);
    } finally {
        duplicating.value = false;
    }
};

const restoreForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('common.actions.restore'),
        message: `Are you sure you want to restore ${form.name}?`,
        variant: 'info',
        confirmText: t('common.actions.restore'),
    });

    if (!confirmed) return;

    try {
        await api.post(`/admin/cms/forms/${form.id}/restore`);
        toast.success.restore('Form');
        fetchForms();
    } catch (error: unknown) {
        logger.error('Failed to restore form:', error);
        toast.error.fromResponse(error);
    }
};

const forceDeleteForm = async (form: Form) => {
    const confirmed = await confirm({
        title: t('common.actions.forceDelete'),
        message: `Are you sure you want to PERMANENTLY delete ${form.name}? This cannot be undone.`,
        variant: 'danger',
        confirmText: t('common.actions.forceDelete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/forms/${form.id}/force-delete`);
        toast.success.action(t('common.messages.success.deleted', { item: 'Form' }));
        fetchForms();
    } catch (error: unknown) {
        logger.error('Failed to force delete form:', error);
        toast.error.fromResponse(error);
    }
};

const selectedIds = ref<(number | string)[]>([]);

const toggleSelection = (id: number | string) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const handleBulkDelete = async () => {
    const confirmed = await confirm({
        title: t('modules.cms.forms.actions.delete'),
        message: t('modules.cms.forms.bulk.confirmDelete', { count: selectedIds.value.length }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });
    if (confirmed) await performBulkAction();
};

const performBulkAction = async () => {
    try {
        await api.delete('/admin/cms/forms/bulk-delete', { data: { ids: selectedIds.value } });
        toast.success.default(t('modules.cms.forms.submissions.messages.bulkDeleteSuccess', { count: selectedIds.value.length }));
        selectedIds.value = [];
        fetchForms();
    } catch (error: unknown) {
        logger.error('Failed to bulk action forms:', error);
        toast.error.fromResponse(error);
    }
};


onMounted(() => {
    fetchForms();
});

watch(trashedFilter, () => {
    fetchForms();
});
</script>
