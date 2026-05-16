<template>
  <div>
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
          {{ t('features.developer.custom_fields.title') }}
        </h1>
        <p class="text-sm text-muted-foreground mt-1">
          {{ t('features.developer.custom_fields.description') }}
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          @click="showCreateGroupModal = true"
        >
          <Layout class="w-4 h-4 mr-2" />
          {{ t('features.developer.custom_fields.create_group') }}
        </Button>
        <Button @click="showCreateFieldModal = true">
          <Plus class="w-4 h-4 mr-2" />
          {{ t('features.developer.custom_fields.create_field') }}
        </Button>
      </div>
    </div>

    <Tabs
      v-model="currentTab"
      class="w-full"
    >
      <div class="mb-10">
        <TabsList class="bg-transparent p-0 h-auto gap-0">
          <TabsTrigger
            value="groups"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
          >
            <Layout class="w-4 h-4 mr-2" />
            {{ t('features.developer.custom_fields.tabs.groups') }}
          </TabsTrigger>
          <TabsTrigger
            value="fields"
            class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
          >
            <FileCode class="w-4 h-4 mr-2" />
            {{ t('features.developer.custom_fields.tabs.fields') }}
          </TabsTrigger>
        </TabsList>
      </div>

      <TabsContent
        value="groups"
        class="px-6"
      >
        <Card>
          <CardContent class="p-0">
            <div
              v-if="loadingGroups"
              class="p-12 text-center"
            >
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('features.developer.custom_fields.loading') || t('features.developer.webhooks.loading') }}
              </p>
            </div>
            <div
              v-else-if="fieldGroups.length === 0"
              class="p-12 text-center"
            >
              <Layout class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('features.developer.custom_fields.groups.empty') }}
              </p>
            </div>
            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ t('features.developer.custom_fields.groups.table.name') }}</TableHead>
                  <TableHead>{{ t('features.developer.custom_fields.groups.table.fields') }}</TableHead>
                  <TableHead>{{ t('features.developer.custom_fields.groups.table.attached_to') }}</TableHead>
                  <TableHead class="text-right">
                    {{ t('features.developer.custom_fields.groups.table.actions') }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="group in fieldGroups"
                  :key="group.id"
                  class="hover:bg-muted/50 transition-colors group"
                >
                  <TableCell>
                    <div class="text-sm font-semibold text-foreground">
                      {{ group.name }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ group.description }}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge
                      variant="outline"
                      class="font-mono"
                    >
                      {{ group.fields_count || 0 }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Badge variant="secondary">
                      {{ group.attachable_type ? group.attachable_type.split('\\').pop() : '-' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        @click="editGroup(group)"
                      >
                        <Pencil class="w-4 h-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                        @click="deleteGroup(group)"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </TabsContent>

      <TabsContent
        value="fields"
        class="px-6"
      >
        <Card>
          <CardHeader class="pb-0 border-b-0 space-y-4">
            <div class="flex items-center gap-4">
              <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input
                  v-model="fieldSearch"
                  :placeholder="t('features.developer.custom_fields.fields.search')"
                  class="pl-9"
                />
              </div>
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div
              v-if="loadingFields"
              class="p-12 text-center"
            >
              <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('features.developer.custom_fields.loading') || t('features.developer.webhooks.loading') }}
              </p>
            </div>
            <div
              v-else-if="filteredFields.length === 0"
              class="p-12 text-center"
            >
              <FileCode class="w-12 h-12 mx-auto text-muted-foreground/20 mb-4" />
              <p class="text-muted-foreground font-medium">
                {{ t('features.developer.custom_fields.fields.empty') }}
              </p>
            </div>
            <Table v-else>
              <TableHeader>
                <TableRow>
                  <TableHead>{{ t('features.developer.custom_fields.fields.table.label') }}</TableHead>
                  <TableHead>{{ t('features.developer.custom_fields.fields.table.name') }}</TableHead>
                  <TableHead>{{ t('features.developer.custom_fields.fields.table.type') }}</TableHead>
                  <TableHead>{{ t('features.developer.custom_fields.fields.table.group') }}</TableHead>
                  <TableHead class="text-right">
                    {{ t('features.developer.custom_fields.fields.table.actions') }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="field in filteredFields"
                  :key="field.id"
                  class="hover:bg-muted/50 transition-colors group"
                >
                  <TableCell class="text-sm font-semibold text-foreground">
                    {{ field.label }}
                  </TableCell>
                  <TableCell>
                    <code class="text-[11px] bg-muted px-2 py-0.5 rounded border border-border group-hover:bg-background transition-colors">{{ field.name }}</code>
                  </TableCell>
                  <TableCell>
                    <Badge
                      variant="secondary"
                      class="capitalize"
                    >
                      {{ field.type }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-sm text-muted-foreground">
                    {{ field.field_group?.name || '-' }}
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        @click="editField(field)"
                      >
                        <Pencil class="w-4 h-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                        @click="deleteField(field)"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>

    <!-- Modals -->
    <FieldGroupModal
      v-if="showCreateGroupModal || showEditGroupModal"
      :field-group="(editingGroup as any)"
      @close="closeGroupModal"
      @saved="handleGroupSaved"
    />

    <FieldModal
      v-if="showCreateFieldModal || showEditFieldModal"
      :field="(editingField as any)"
      :field-groups="fieldGroups"
      @close="closeFieldModal"
      @saved="handleFieldSaved"
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
import FieldGroupModal from '@/modules/Cms/components/custom-fields/FieldGroupModal.vue';
import FieldModal from '@/modules/Cms/components/custom-fields/FieldModal.vue';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import { Badge, Button, Card, CardContent, CardHeader, Input, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui';

import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Pencil from 'lucide-vue-next/dist/esm/icons/pencil.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import FileCode from 'lucide-vue-next/dist/esm/icons/file-code.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

import type { FieldGroup, CustomField } from '@/modules/Cms/types/custom-fields';

const { t } = useI18n();
const { confirm } = useConfirm();
const toast = useToast();

const currentTab = ref('groups');

const fieldGroups = ref<FieldGroup[]>([]);
const customFields = ref<CustomField[]>([]);
const loadingGroups = ref(false);
const loadingFields = ref(false);
const fieldSearch = ref('');

// Modals state
const showCreateGroupModal = ref(false);
const showEditGroupModal = ref(false);
const editingGroup = ref<FieldGroup | null>(null);
const showCreateFieldModal = ref(false);
const showEditFieldModal = ref(false);
const editingField = ref<CustomField | null>(null);

const filteredFields = computed(() => {
    if (!fieldSearch.value) return customFields.value;
    
    const searchLower = fieldSearch.value.toLowerCase();
    return customFields.value.filter(field => 
        field.label.toLowerCase().includes(searchLower) ||
        field.name.toLowerCase().includes(searchLower)
    );
});

const fetchFieldGroups = async () => {
    loadingGroups.value = true;
    try {
        const response = await api.get('/manage/cms/field-groups');
        const { data } = parseResponse<FieldGroup>(response);
        fieldGroups.value = ensureArray<FieldGroup>(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch field groups:', error);
    } finally {
        loadingGroups.value = false;
    }
};

const fetchCustomFields = async () => {
    loadingFields.value = true;
    try {
        const response = await api.get('/manage/cms/custom-fields');
        const { data } = parseResponse<CustomField>(response);
        customFields.value = ensureArray<CustomField>(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch custom fields:', error);
    } finally {
        loadingFields.value = false;
    }
};

// Group Actions
const editGroup = (group: FieldGroup) => {
    editingGroup.value = group;
    showEditGroupModal.value = true;
};

const deleteGroup = async (group: FieldGroup) => {
    const confirmed = await confirm({
        title: t('features.developer.custom_fields.groups.actions.delete'),
        message: t('features.developer.custom_fields.groups.confirm.delete', { name: group.name }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/cms/field-groups/${group.id}`);
        toast.success.delete(t('features.developer.custom_fields.tabs.groups'));
        fetchFieldGroups();
    } catch (error: unknown) {
        logger.error('Failed to delete group:', error);
        toast.error.delete(error, t('features.developer.custom_fields.tabs.groups'));
    }
};

const closeGroupModal = () => {
    showCreateGroupModal.value = false;
    showEditGroupModal.value = false;
    editingGroup.value = null;
};

const handleGroupSaved = () => {
    fetchFieldGroups();
    closeGroupModal();
};

// Field Actions
const editField = (field: CustomField) => {
    editingField.value = field;
    showEditFieldModal.value = true;
};

const deleteField = async (field: CustomField) => {
    const confirmed = await confirm({
        title: t('features.developer.custom_fields.fields.actions.delete'),
        message: t('features.developer.custom_fields.fields.confirm.delete', { label: field.label }),
        variant: 'danger',
        confirmText: t('common.actions.delete'),
    });

    if (!confirmed) return;

    try {
        await api.delete(`/manage/cms/custom-fields/${field.id}`);
        toast.success.delete(t('features.developer.custom_fields.tabs.fields'));
        fetchCustomFields();
    } catch (error: unknown) {
        logger.error('Failed to delete field:', error);
        toast.error.delete(error, t('features.developer.custom_fields.tabs.fields'));
    }
};

const closeFieldModal = () => {
    showCreateFieldModal.value = false;
    showEditFieldModal.value = false;
    editingField.value = null;
};

const handleFieldSaved = () => {
    fetchCustomFields();
    closeFieldModal();
};

onMounted(() => {
    fetchFieldGroups();
    fetchCustomFields();
});
</script>

