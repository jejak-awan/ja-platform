<template>
  <div class="space-y-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ $t('modules.cms.content.title') }}
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        {{ $t('modules.cms.content.description') }}
      </p>
    </div>

    <div class="w-full">
      <Tabs
        v-model="activeTab"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto">
            <TabsTrigger
              value="contents"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <FileText class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.content.tabs.contents') }}
            </TabsTrigger>
            <TabsTrigger
              value="categories"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <Layers class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.content.tabs.categories') }}
            </TabsTrigger>
            <TabsTrigger
              value="tags"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <Tag class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.content.tabs.tags') }}
            </TabsTrigger>
            <TabsTrigger
              value="templates"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none"
            >
              <LayoutTemplate class="w-4 h-4 mr-2" />
              {{ $t('modules.cms.content.list.templates') }}
            </TabsTrigger>
          </TabsList>

          <div class="flex items-center gap-2 pb-1">
            <!-- Redundant button removed as it's now a tab -->
          </div>
        </div>

        <div class="p-0">
          <TabsContent value="contents">
            <ContentsIndex :is-embedded="true" />
          </TabsContent>
          <TabsContent value="categories">
            <CategoriesIndex :is-embedded="true" />
          </TabsContent>
          <TabsContent value="tags">
            <TagsIndex :is-embedded="true" />
          </TabsContent>
          <TabsContent value="templates">
            <TemplatesIndex :is-embedded="true" />
          </TabsContent>
        </div>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent
} from '@/shared/components/ui';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Tag from 'lucide-vue-next/dist/esm/icons/tag.js';
import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import { useSystemStore } from '@/modules/System/stores/system';
import { useHead } from '@unhead/vue';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { ref, watch, computed } from 'vue';

// Lazy load actual index components
import ContentsIndex from './contents/Index.vue';
import CategoriesIndex from './categories/Index.vue';
import TagsIndex from '@/modules/Library/views/tags/Index.vue';
import TemplatesIndex from './content-templates/Index.vue';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const systemStore = useSystemStore();

useHead({
    title: computed(() => `${systemStore.siteSettings?.site_name || 'JA CMS'} | ${t('modules.cms.content.title')}`)
});

const activeTab = ref((route.query.tab as string) || 'contents');

watch(activeTab, (newTab) => {
    router.replace({ 
        query: { ...route.query, tab: newTab } 
    });
});
</script>


