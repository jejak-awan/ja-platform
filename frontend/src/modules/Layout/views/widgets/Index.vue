<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('features.widgets.title') }}
      </h1>
      <Button @click="showCreateModal = true">
        <Plus class="w-4 h-4 mr-2" />
        {{ $t('features.widgets.new') }}
      </Button>
    </div>
    <Card>
      <div
        v-if="loading"
        class="flex flex-col items-center justify-center py-12"
      >
        <Loader2 class="w-8 h-8 animate-spin text-muted-foreground mb-2" />
        <p class="text-muted-foreground">
          {{ $t('features.widgets.loading') }}
        </p>
      </div>
      <div
        v-else-if="widgets.length === 0"
        class="flex flex-col items-center justify-center py-12"
      >
        <Layout class="w-12 h-12 text-muted-foreground/20 mb-4" />
        <p class="text-muted-foreground">
          {{ $t('features.widgets.empty') }}
        </p>
      </div>
      <Table v-else>
        <TableHeader>
          <TableRow>
            <TableHead>{{ $t('features.widgets.table.title') }}</TableHead>
            <TableHead>{{ $t('features.widgets.table.type') }}</TableHead>
            <TableHead>{{ $t('features.widgets.table.location') }}</TableHead>
            <TableHead class="text-right">
              {{ $t('features.widgets.table.actions') }}
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="widget in widgets"
            :key="widget.id"
          >
            <TableCell class="font-medium">
              <div class="flex items-center gap-2">
                {{ widget.title }}
                <Badge
                  v-if="!widget.is_active"
                  variant="destructive"
                  class="text-[10px] px-1.5 py-0 h-4"
                >
                  {{ $t('features.widgets.table.status.inactive') }}
                </Badge>
              </div>
            </TableCell>
            <TableCell>
              <Badge
                variant="outline"
                class="capitalize"
              >
                {{ widget.type }}
              </Badge>
            </TableCell>
            <TableCell>
              <code class="text-xs bg-muted px-1.5 py-0.5 rounded border border-border">
                {{ widget.location || '-' }}
              </code>
            </TableCell>
            <TableCell class="text-right">
              <div class="flex justify-end gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  @click="editWidget(widget)"
                >
                  <Pencil class="w-4 h-4 mr-2" />
                  {{ $t('features.widgets.actions.edit') }}
                </Button>
                <Button 
                  variant="ghost" 
                  size="sm" 
                  class="text-destructive hover:text-destructive hover:bg-destructive/10"
                  @click="deleteWidget(widget)"
                >
                  <Trash2 class="w-4 h-4 mr-2" />
                  {{ $t('features.widgets.actions.delete') }}
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </Card>
    <WidgetModal
      v-if="showCreateModal || showEditModal"
      :widget="editingWidget"
      @close="closeModal"
      @saved="handleWidgetSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { Badge, Button, Card, Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/shared/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import WidgetModal from '@/modules/Layout/components/widgets/WidgetModal.vue';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

interface Widget {
    id: string;
    title: string;
    type: string;
    location?: string;
    is_active?: boolean;
}

const widgets = ref<Widget[]>([]);
const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingWidget = ref<Widget | null>(null);

const fetchWidgets = async () => {
    loading.value = true;
    try {
        const response = await api.get('/manage/layout/widgets');
        const { data } = parseResponse(response);
        widgets.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch widgets:', error);
    } finally {
        loading.value = false;
    }
};

const editWidget = (widget: Widget) => {
    editingWidget.value = widget;
    showEditModal.value = true;
};

const deleteWidget = async (widget: Widget) => {
    const confirmed = await confirm({
        title: t('features.widgets.actions.delete'),
        message: t('features.widgets.messages.deleteConfirm', { title: widget.title }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/layout/widgets/${widget.id}`);
        toast.success.delete(t('features.widgets.title'));
        fetchWidgets();
    } catch (error: unknown) {
        logger.error('Failed to delete widget:', error);
        toast.error.delete(error as Error, t('features.widgets.title'));
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingWidget.value = null;
};

const handleWidgetSaved = () => {
    fetchWidgets();
    closeModal();
};

onMounted(() => {
    fetchWidgets();
});
</script>

