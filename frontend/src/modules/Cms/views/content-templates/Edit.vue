<template>
  <div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        {{ t('modules.cms.content_templates.form.editTitle') }}
      </h1>
      <Button
        variant="ghost"
        @click="router.push({ name: 'contents.index', query: { tab: 'templates' } })"
      >
        <ChevronLeft class="w-4 h-4 mr-2" />
        {{ t('modules.cms.content_templates.form.back') }}
      </Button>
    </div>

    <div
      v-if="loading && !form.name"
      class="text-center py-12"
    >
      <Loader2 class="w-8 h-8 animate-spin mx-auto text-muted-foreground mb-4" />
      <p class="text-muted-foreground">
        {{ t('modules.cms.content_templates.loading') }}
      </p>
    </div>

    <form
      v-else
      class="pb-10"
      @submit.prevent="handleSubmit"
    >
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Left) -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle class="text-lg font-semibold">
                {{ t('modules.cms.content_templates.form.content') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label for="title">
                  {{ t('modules.cms.content_templates.form.titleLabel') }}
                </Label>
                <Input
                  id="title"
                  v-model="form.title_template"
                  :placeholder="t('modules.cms.content_templates.form.titlePlaceholder')"
                />
              </div>

              <div class="space-y-2">
                <Label>
                  {{ t('modules.cms.content_templates.form.body') }}
                </Label>
                <TiptapEditor
                  :model-value="form.body_template"
                  :placeholder="t('modules.cms.content_templates.form.bodyPlaceholder')"
                  @update:model-value="(val) => form.body_template = val"
                />
              </div>

              <div class="space-y-2">
                <Label for="excerpt">
                  {{ t('modules.cms.content_templates.form.excerpt') }}
                </Label>
                <Textarea
                  id="excerpt"
                  v-model="form.excerpt_template"
                  rows="3"
                  :placeholder="t('modules.cms.content_templates.form.excerptPlaceholder')"
                />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle class="text-lg font-semibold">
                {{ t('modules.cms.content_templates.form.details') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label for="name">
                  {{ t('modules.cms.content_templates.form.name') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="name"
                  v-model="form.name"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                  :placeholder="t('modules.cms.content_templates.form.namePlaceholder')"
                />
                <p
                  v-if="errors.name"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="slug">
                  {{ t('modules.cms.content_templates.form.slug') || 'Slug' }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="slug"
                  v-model="form.slug"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
                  placeholder="template-slug"
                />
                <p class="text-xs text-muted-foreground">
                  {{ t('modules.cms.content_templates.form.slugHelp') || 'URL-friendly version' }}
                </p>
                <p
                  v-if="errors.slug"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="description">
                  {{ t('modules.cms.content_templates.form.description') }}
                </Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  :placeholder="t('modules.cms.content_templates.form.descriptionPlaceholder')"
                />
              </div>

              <div class="space-y-2">
                <Label for="type">
                  {{ t('modules.cms.content_templates.form.type') }} <span class="text-destructive">*</span>
                </Label>
                <Select
                  v-model="form.type"
                  required
                >
                  <SelectTrigger id="type">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
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
              </div>
            </CardContent>
          </Card>

          <div class="flex items-center gap-2">
            <div class="flex-1" />
            <Button
              variant="outline"
              type="button"
              @click="router.push({ name: 'contents.index', query: { tab: 'templates' } })"
            >
              {{ t('modules.cms.content_templates.form.cancel') }}
            </Button>
            <Button
              type="submit"
              :disabled="saving || !isDirty"
            >
              <Loader2
                v-if="saving"
                class="w-4 h-4 mr-2 animate-spin"
              />
              <Save
                v-else
                class="w-4 h-4 mr-2"
              />
              {{ saving ? t('modules.cms.content_templates.form.updating') : t('modules.cms.content_templates.form.update') }}
            </Button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { contentTemplateSchema } from '@/shared/schemas';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import type { ContentTemplate } from '@/modules/Cms/types/cms';
import { Button, Card, CardContent, CardHeader, CardTitle, Input, Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Textarea } from '@/shared/components/ui';

import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(contentTemplateSchema);
const templateId = route.params.id;

const loading = ref(false);
const saving = ref(false);
const initialForm = ref<Record<string, unknown> | null>(null);

const form = ref({
    name: '',
    slug: '',
    description: '',
    type: 'post' as 'post' | 'page' | 'custom',
    title_template: '',
    body_template: '',
    excerpt_template: '',
});

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const fetchTemplate = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/manage/cms/content-templates/${templateId}`);
        const template = parseSingleResponse<ContentTemplate>(response);
        
        if (template) {
            form.value = {
                name: template.name || '',
                slug: template.slug || '',
                description: template.description || '',
                type: (template.type as 'post' | 'page' | 'custom') || 'post',
                title_template: template.title_template || '',
                body_template: template.body_template || '',
                excerpt_template: template.excerpt_template || '',
            };
        }
        
        // Save initial state for dirty checking
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } catch (error: unknown) {
        logger.error('Failed to fetch template:', error);
        toast.error.load(error);
        router.push({ name: 'contents.index', query: { tab: 'templates' } });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.put(`/manage/cms/content-templates/${templateId}`, form.value);
        toast.success.update(t('modules.cms.content_templates.title_singular'));
        
        // Update initial form after successful save
        initialForm.value = JSON.parse(JSON.stringify(form.value));
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response: { status: number, data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchTemplate();
});
</script>

