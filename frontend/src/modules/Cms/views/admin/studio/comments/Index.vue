<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-foreground">
          {{ $t('modules.cms.comments.list.title') }}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
          {{ $t('modules.cms.comments.list.subtitle') }}
        </p>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div
      v-if="statistics"
      class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6"
    >
      <Card 
        class="p-4 cursor-pointer hover:shadow-md transition-shadow" 
        :class="statusFilter === 'all' ? 'ring-2 ring-primary/50' : ''"
        @click="statusFilter = 'all'"
      >
        <p class="text-2xl font-bold">
          {{ statistics.total }}
        </p>
        <p class="text-xs text-muted-foreground">
          {{ $t('modules.cms.comments.stats.total') }}
        </p>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:shadow-md transition-shadow border-yellow-500/20 dark:border-yellow-500/10" 
        :class="statusFilter === 'pending' ? 'ring-2 ring-yellow-500/50' : ''"
        @click="statusFilter = 'pending'"
      >
        <p class="text-2xl font-bold text-yellow-500 dark:text-yellow-400">
          {{ statistics.pending }}
        </p>
        <p class="text-xs text-yellow-500/70 dark:text-yellow-400/70">
          {{ $t('modules.cms.comments.stats.pending') }}
        </p>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:shadow-md transition-shadow border-green-500/20 dark:border-green-500/10" 
        :class="statusFilter === 'approved' ? 'ring-2 ring-green-500/50' : ''"
        @click="statusFilter = 'approved'"
      >
        <p class="text-2xl font-bold text-green-500 dark:text-green-400">
          {{ statistics.approved }}
        </p>
        <p class="text-xs text-green-500/70 dark:text-green-400/70">
          {{ $t('modules.cms.comments.stats.approved') }}
        </p>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:shadow-md transition-shadow border-red-500/20 dark:border-red-500/10" 
        :class="statusFilter === 'rejected' ? 'ring-2 ring-red-500/50' : ''"
        @click="statusFilter = 'rejected'"
      >
        <p class="text-2xl font-bold text-red-500 dark:text-red-400">
          {{ statistics.rejected }}
        </p>
        <p class="text-xs text-red-500/70 dark:text-red-400/70">
          {{ $t('modules.cms.comments.stats.rejected') }}
        </p>
      </Card>
      <Card 
        class="p-4 cursor-pointer hover:shadow-md transition-shadow" 
        :class="statusFilter === 'spam' ? 'ring-2 ring-muted-foreground/50' : ''"
        @click="statusFilter = 'spam'"
      >
        <p class="text-2xl font-bold text-muted-foreground">
          {{ statistics.spam }}
        </p>
        <p class="text-xs text-muted-foreground">
          {{ $t('modules.cms.comments.stats.spam') }}
        </p>
      </Card>
    </div>

    <!-- Filters -->
    <Card class="p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <Input
            v-model="search"
            :placeholder="$t('modules.cms.comments.filter.searchPlaceholder')"
            class="pl-9"
          />
        </div>
        <Select v-model="statusFilter">
          <SelectTrigger class="w-full md:w-[200px]">
            <SelectValue :placeholder="$t('modules.cms.comments.filter.allStatus')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('modules.cms.comments.filter.allStatus') }}
            </SelectItem>
            <SelectItem value="pending">
              {{ $t('modules.cms.comments.status.pending') }}
            </SelectItem>
            <SelectItem value="approved">
              {{ $t('modules.cms.comments.status.approved') }}
            </SelectItem>
            <SelectItem value="rejected">
              {{ $t('modules.cms.comments.status.rejected') }}
            </SelectItem>
            <SelectItem value="spam">
              {{ $t('modules.cms.comments.status.spam') }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Bulk Actions -->
        <div
          v-if="selectedIds.length > 0"
          class="flex items-center gap-3 p-1.5 px-3 rounded-lg bg-primary/5 border border-primary/10 animate-in fade-in slide-in-from-top-1 ml-auto"
        >
          <span class="text-sm font-medium text-primary">
            {{ t('modules.cms.comments.list.selected', { count: selectedIds.length }) }}
          </span>
          <div class="h-4 w-px bg-primary/20" />
          <Select
            v-model="bulkActionSelection"
            @update:model-value="handleBulkAction"
          >
            <SelectTrigger class="w-[160px] h-8 border-primary/20">
              <SelectValue :placeholder="$t('modules.cms.content.list.bulkActions')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="approve">
                {{ $t('modules.cms.comments.actions.approveAll') }}
              </SelectItem>
              <SelectItem value="reject">
                {{ $t('modules.cms.comments.actions.rejectAll') }}
              </SelectItem>
              <SelectItem value="spam">
                {{ $t('modules.cms.comments.actions.markSpam') }}
              </SelectItem>
              <SelectItem
                value="delete"
                class="text-destructive focus:text-destructive"
              >
                {{ $t('common.actions.delete') }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
            
      <div class="mt-4 pt-4 border-t flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <Checkbox 
            id="select-all"
            :checked="isAllSelected"
            @update:checked="toggleSelectAll"
          />
          <label
            for="select-all"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            {{ $t('common.actions.selectAll') }}
          </label>
        </div>
      </div>
    </Card>

    <!-- Comments List -->
    <div
      v-if="loading"
      class="bg-card border border-border rounded-lg p-12 text-center"
    >
      <p class="text-muted-foreground">
        {{ $t('common.messages.loading.default') }}
      </p>
    </div>

    <Card
      v-else-if="comments.length === 0"
      class="p-12 text-center"
    >
      <MessageSquare class="mx-auto h-12 w-12 text-muted-foreground opacity-20" />
      <p class="mt-4 text-muted-foreground">
        {{ $t('modules.cms.comments.list.empty') }}
      </p>
    </Card>

    <div
      v-else
      class="space-y-4"
    >
      <Card
        v-for="comment in comments"
        :key="comment.id"
        class="p-0 overflow-hidden"
      >
        <div class="p-6">
          <!-- Comment Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-start space-x-3 flex-1">
              <!-- Checkbox for selection -->
              <div class="flex-shrink-0 pt-1">
                <Checkbox
                  :checked="selectedIds.includes(comment.id)"
                  @update:checked="toggleSelection(comment.id)"
                />
              </div>
              <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                  <span class="text-primary font-semibold text-sm">
                    {{ ((comment.user?.name || comment.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                  <p class="text-sm font-semibold text-foreground">
                    {{ comment.user?.name || comment.name || t('modules.cms.comments.detail.anonymous') }}
                  </p>
                  <Badge
                    variant="outline"
                    :class="
                      statusFilter === 'pending' ? 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' :
                      statusFilter === 'approved' ? 'bg-green-500/10 text-green-500 border-green-500/20' :
                      statusFilter === 'rejected' ? 'bg-red-500/10 text-red-500 border-red-500/20' :
                      statusFilter === 'spam' ? 'bg-muted text-muted-foreground' : ''
                    "
                  >
                    {{ $t('modules.cms.comments.status.' + comment.status) }}
                  </Badge>
                </div>
                <div class="flex items-center gap-x-3 mt-1 text-xs text-muted-foreground">
                  <span>{{ comment.user?.email || comment.email || t('modules.cms.comments.detail.no_email') }}</span>
                  <span class="flex items-center">
                    <clock class="w-3 h-3 mr-1" />
                    {{ formatDate(comment.created_at) }}
                  </span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <Button
                v-if="comment.status === 'pending' || comment.status === 'rejected'"
                variant="ghost"
                size="sm"
                class="h-8 text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 hover:bg-green-500/10"
                @click="approveComment(comment)"
              >
                <Check class="w-4 h-4 mr-1" />
                {{ $t('modules.cms.comments.actions.approve') }}
              </Button>
              <Button
                v-if="comment.status === 'pending' || comment.status === 'approved'"
                variant="ghost"
                size="sm"
                class="h-8 text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 hover:bg-yellow-500/10"
                @click="rejectComment(comment)"
              >
                <X class="w-4 h-4 mr-1" />
                {{ $t('modules.cms.comments.actions.reject') }}
              </Button>
              <Button
                v-if="comment.status !== 'spam'"
                variant="ghost"
                size="sm"
                class="h-8 text-muted-foreground"
                @click="markAsSpam(comment)"
              >
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ $t('modules.cms.comments.actions.markSpam') }}
              </Button>
              <Button
                variant="ghost"
                size="sm"
                class="h-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                @click="deleteComment(comment)"
              >
                <Trash2 class="w-4 h-4 mr-1" />
                {{ $t('common.actions.delete') }}
              </Button>
            </div>
          </div>

          <!-- Comment Body -->
          <div class="mb-4 pl-[52px]">
            <p class="text-sm text-foreground leading-relaxed">
              {{ comment.body }}
            </p>
          </div>

          <!-- Comment Meta -->
          <div class="flex items-center justify-between text-[10px] text-muted-foreground pt-4 border-t pl-[52px]">
            <div class="flex items-center space-x-4">
              <span
                v-if="comment.content"
                class="flex items-center"
              >
                <ArrowUpRight class="w-3 h-3 mr-1" />
                {{ $t('modules.cms.comments.list.on') }}: 
                <router-link
                  :to="{ name: 'contents.edit', params: { id: comment.content.id } }"
                  class="text-primary hover:underline ml-1 font-medium"
                >
                  {{ comment.content.title }}
                </router-link>
              </span>
              <span
                v-if="comment.parent"
                class="flex items-center"
              >
                <Reply class="w-3 h-3 mr-1" />
                {{ $t('modules.cms.comments.list.replyTo') }}: <b>{{ comment.parent.user?.name || comment.parent.name || t('modules.cms.comments.detail.anonymous') }}</b>
              </span>
            </div>
            <div class="font-medium">
              <span v-if="(comment.replies_count || 0) > 0">
                {{ comment.replies_count }} {{ comment.replies_count === 1 ? t('modules.cms.comments.detail.reply') : t('modules.cms.comments.detail.replies') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Replies (if any) -->
        <div
          v-if="comment.replies && comment.replies.length > 0"
          class="bg-muted/30 border-t border-border p-6 pl-16 space-y-4"
        >
          <div
            v-for="reply in comment.replies"
            :key="reply.id"
            class="relative"
          >
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-background border border-border flex items-center justify-center">
                  <span class="text-muted-foreground font-semibold text-[10px]">
                    {{ ((reply.user?.name || reply.name || 'U')?.charAt(0) || 'U').toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                  <p class="text-xs font-semibold text-foreground">
                    {{ reply.user?.name || reply.name || t('modules.cms.comments.detail.anonymous') }}
                  </p>
                  <Badge
                    variant="outline"
                    class="text-[10px] h-4 px-1"
                    :class="
                      reply.status === 'pending' ? 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' :
                      reply.status === 'approved' ? 'bg-green-500/10 text-green-500 border-green-500/20' :
                      reply.status === 'rejected' ? 'bg-red-500/10 text-red-500 border-red-500/20' : ''
                    "
                  >
                    {{ $t('modules.cms.comments.status.' + reply.status) }}
                  </Badge>
                </div>
                <p class="text-xs text-foreground/80 leading-relaxed">
                  {{ reply.body }}
                </p>
                <p class="text-[10px] text-muted-foreground mt-1">
                  {{ formatDate(reply.created_at) }}
                </p>
              </div>
              <div class="flex items-center gap-1 opacity-0 hover:opacity-100 transition-opacity">
                <Button
                  v-if="reply.status === 'pending'"
                  variant="ghost"
                  size="icon"
                  class="w-6 h-6 text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 hover:bg-green-500/10"
                  @click="approveComment(reply)"
                >
                  <Check class="w-3 h-3" />
                </Button>
                <Button
                  v-if="reply.status === 'pending'"
                  variant="ghost"
                  size="icon"
                  class="w-6 h-6 text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 hover:bg-yellow-500/10"
                  @click="rejectComment(reply)"
                >
                  <X class="w-3 h-3" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="w-6 h-6 text-destructive"
                  @click="deleteComment(reply)"
                >
                  <Trash2 class="w-3 h-3" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <!-- Pagination -->
      <Pagination
        v-if="pagination && pagination.total > 0"
        :current-page="pagination.current_page"
        :total-items="pagination.total"
        :per-page="Number(pagination.per_page || 10)"
        :show-page-numbers="true"
        class="border-none shadow-none mt-4"
        @page-change="changePage"
        @update:per-page="(val) => { if(pagination) { pagination.per_page = Number(val); pagination.current_page = 1; fetchComments(); } }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useConfirm } from '@/shared/composables/useConfirm';
import { useToast } from '@/shared/composables/useToast';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { Badge, Button, Card, Checkbox, Input, Pagination, Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui';
import type { PaginationData } from '@/shared/utils/responseParser';

import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ArrowUpRight from 'lucide-vue-next/dist/esm/icons/arrow-up-right.js';
import Reply from 'lucide-vue-next/dist/esm/icons/reply.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';

import type { Comment, CommentStatus, CommentStatistics } from '@/modules/Cms/types/comments';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const loading = ref(false);
const comments = ref<Comment[]>([]);
const search = ref('');
const statusFilter = ref<CommentStatus | 'all'>('pending');
const pagination = ref<PaginationData | null>(null);
const statistics = ref<CommentStatistics | null>(null);
const selectedIds = ref<number[]>([]);

const fetchStatistics = async () => {
    try {
        const response = await api.get('/admin/cms/comments/statistics');
        statistics.value = (response.data) as CommentStatistics;
    } catch (error: unknown) {
        logger.error('Failed to fetch statistics:', error);
    }
};

const bulkActionSelection = ref('');

const handleBulkAction = async (value: string) => {
    if (!value) return;
    await bulkAction(value);
    bulkActionSelection.value = '';
};

const bulkAction = async (action: string) => {
    if (selectedIds.value.length === 0) return;
    
    const count = selectedIds.value.length;
    let confirmMsg = '';
    
    switch (action) {
        case 'delete':
            confirmMsg = t('common.messages.confirm.bulkDelete', { count });
            break;
        case 'approve':
            confirmMsg = t('modules.cms.comments.messages.bulkApproveConfirm', { count });
            break;
        case 'reject':
            confirmMsg = t('modules.cms.comments.messages.bulkRejectConfirm', { count });
            break;
        case 'spam':
            confirmMsg = t('modules.cms.comments.messages.bulkSpamConfirm', { count });
            break;
    }
    
    const confirmed = await confirm({
        title: t('modules.cms.comments.actions.bulkAction'),
        message: confirmMsg,
        variant: action === 'delete' ? 'danger' : 'warning',
        confirmText: t('common.actions.confirm'),
    });

    if (!confirmed) {
        bulkActionSelection.value = '';
        return;
    }
    
    try {
        await api.post('/admin/cms/comments/bulk', {
            ids: selectedIds.value,
            action: action
        });
        selectedIds.value = [];
        await fetchComments();
        await fetchStatistics();
        toast.success.action(t('common.messages.success.action'));
    } catch (error: unknown) {
        logger.error('Bulk action failed:', error);
        toast.error.action(error as Record<string, unknown>);
    }
};

const fetchComments = async () => {
    loading.value = true;
    try {
        const params: Record<string, string | number> = {
            page: pagination.value?.current_page || 1,
            per_page: Number(pagination.value?.per_page || 10),
        };

        if (statusFilter.value && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
        }

        const response = await api.get('/admin/cms/comments', { params });
        const { data, pagination: paginationData } = parseResponse<Comment>(response);
        comments.value = ensureArray<Comment>(data);
        if (paginationData) {
            pagination.value = paginationData;
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch comments:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchComments();
    }
};

const approveComment = async (comment: Comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/approve`);
        await fetchComments();
        toast.success.approve(t('modules.cms.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to approve comment:', error);
        toast.error.update(error as Record<string, unknown>, t('modules.cms.comments.title_singular'));
    }
};

const rejectComment = async (comment: Comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/reject`);
        await fetchComments();
        await fetchStatistics();
        toast.success.reject(t('modules.cms.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to reject comment:', error);
        toast.error.update(error as Record<string, unknown>, t('modules.cms.comments.title_singular'));
    }
};

const markAsSpam = async (comment: Comment) => {
    try {
        await api.put(`/admin/cms/comments/${comment.id}/spam`);
        await fetchComments();
        await fetchStatistics();
        toast.success.markSpam(t('modules.cms.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to mark as spam:', error);
        toast.error.update(error as Record<string, unknown>, t('modules.cms.comments.title_singular'));
    }
};

const toggleSelection = (commentId: number) => {
    const index = selectedIds.value.indexOf(commentId);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(commentId);
    }
};

const deleteComment = async (comment: Comment) => {
    const confirmed = await confirm({
        title: t('modules.cms.comments.actions.delete'),
        message: t('modules.cms.comments.messages.deleteConfirm'),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/admin/cms/comments/${comment.id}`);
        await fetchComments();
        toast.success.delete(t('modules.cms.comments.title_singular'));
    } catch (error: unknown) {
        logger.error('Failed to delete comment:', error);
        toast.error.delete(error as Record<string, unknown>, t('modules.cms.comments.title_singular'));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

watch([statusFilter, search], () => {
    if (pagination.value) {
        pagination.value.current_page = 1;
    }
    fetchComments();
});

const isAllSelected = computed(() => {
    return comments.value.length > 0 && selectedIds.value.length === comments.value.length;
});

const toggleSelectAll = (checked: boolean) => {
    if (checked) {
        selectedIds.value = comments.value.map(c => c.id);
    } else {
        selectedIds.value = [];
    }
};

onMounted(() => {
    fetchComments();
    fetchStatistics();
});
</script>

