<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">
        {{ t('features.search.title') }}
      </h1>
      <p
        v-if="query"
        class="text-sm text-muted-foreground mt-1"
      >
        {{ t('features.search.resultsFor') }} <span class="font-medium">{{ query }}</span>
      </p>
    </div>

    <div class="mb-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('features.search.placeholder')"
          class="w-full pl-10 pr-4 py-3 border border-input rounded-lg focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          @keyup.enter="performSearch"
        >
        <Search class="absolute left-3 top-3.5 h-5 w-5 text-muted-foreground" />
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex items-center space-x-2">
      <span class="text-sm text-foreground">{{ t('features.search.filterBy') }}</span>
      <button
        v-for="type in availableTypes"
        :key="type"
        :class="[
          'px-3 py-1 text-sm rounded-md transition-colors',
          typeFilters.includes(type)
            ? 'bg-primary text-primary-foreground'
            : 'bg-secondary text-foreground hover:bg-accent'
        ]"
        @click="toggleTypeFilter(type)"
      >
        {{ t(`features.search.types.${type}`) }}
      </button>
    </div>

    <div
      v-if="loading"
      class="text-center py-12"
    >
      <p class="text-muted-foreground">
        {{ t('features.search.searching') }}
      </p>
    </div>

    <div
      v-else-if="results.length === 0 && query"
      class="text-center py-12"
    >
      <Search class="mx-auto h-12 w-12 text-muted-foreground" />
      <p class="mt-4 text-muted-foreground">
        {{ t('features.search.empty') }}
      </p>
    </div>

    <div
      v-else-if="results.length > 0"
      class="space-y-4"
    >
      <!-- Group results by type -->
      <div
        v-for="(items, type) in groupedResults"
        :key="type"
        class="bg-card border border-border rounded-lg p-6"
      >
        <h2 class="text-lg font-semibold text-foreground mb-4 capitalize">
          {{ t(`features.search.types.${type}`) }}
        </h2>
        <div class="space-y-3">
          <div
            v-for="result in items"
            :key="`${result.type}-${result.id}`"
            class="p-4 border border-border rounded-lg hover:bg-muted cursor-pointer transition-colors"
            @click="handleResultClick(result)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-sm font-medium text-foreground">
                  {{ result.title }}
                </h3>
                <p
                  v-if="result.description"
                  class="text-sm text-muted-foreground mt-1"
                >
                  {{ result.description }}
                </p>
                <div class="mt-2 flex items-center space-x-4 text-xs text-muted-foreground">
                  <span v-if="result.created_at">{{ formatDate(result.created_at) }}</span>
                  <span v-if="result.author">{{ result.author }}</span>
                </div>
              </div>
              <ChevronRight class="w-5 h-5 text-muted-foreground" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="text-center py-12"
    >
      <Search class="mx-auto h-12 w-12 text-muted-foreground" />
      <p class="mt-4 text-muted-foreground">
        {{ t('features.search.initial') }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import { SearchService } from '@/modules/Search/services/searchService';

const { t } = useI18n();
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';

const route = useRoute();
const router = useRouter();

interface SearchResult {
    id: string | string;
    type: string;
    title: string;
    description?: string;
    created_at?: string;
    author?: string;
    url?: string;
}

const query = ref(route.query.q || '');
const searchQuery = ref(route.query.q || '');
const results = ref<SearchResult[]>([]);
const loading = ref(false);
const typeFilters = ref<string[]>([]);

const availableTypes = ['content', 'category', 'user', 'media', 'page', 'tag'];

const groupedResults = computed(() => {
    const grouped: Record<string, SearchResult[]> = {};
    
    let filteredResults = results.value;
    if (typeFilters.value.length > 0) {
        filteredResults = filteredResults.filter(r => typeFilters.value.includes(r.type));
    }
    
    filteredResults.forEach(result => {
        const type = result.type;
        if (!grouped[type]) {
            grouped[type] = [];
        }
        grouped[type].push(result);
    });
    
    return grouped;
});

const performSearch = async () => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
        return;
    }
    
    query.value = searchQuery.value;
    loading.value = true;
    
    try {
        const response = await SearchService.search({ q: query.value });
        const { data } = parseResponse(response);
        results.value = ensureArray(data);
        
        // Update URL
        router.replace({ query: { q: query.value } });
    } catch (error: unknown) {
        logger.error('Failed to search:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const toggleTypeFilter = (type: string) => {
    const index = typeFilters.value.indexOf(type);
    if (index > -1) {
        typeFilters.value.splice(index, 1);
    } else {
        typeFilters.value.push(type);
    }
};

const handleResultClick = (result: SearchResult) => {
    // Navigate based on result type
    if (result.type === 'content') {
        router.push({ name: 'contents.edit', params: { id: result.id } });
    } else if (result.type === 'category') {
        router.push({ name: 'categories.edit', params: { id: result.id } });
    } else if (result.type === 'user') {
        router.push({ name: 'users.edit', params: { id: result.id } });
    } else if (result.type === 'media') {
        router.push({ name: 'media', query: { id: result.id } });
    } else if (result.url) {
        router.push(result.url);
    }
};

const formatDate = (date: string) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString();
};

onMounted(() => {
    if (query.value) {
        performSearch();
    }
});
</script>

