<template>
  <div 
    v-if="totalItems > 0"
    :class="cn(
      'flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 bg-card border border-border rounded-lg',
      props.class
    )"
  >
    <!-- Left: Showing info and rows per page -->
    <div class="flex items-center gap-4 text-xs text-foreground/80">
      <p>
        {{ $t('common.pagination.showing', { 
          from: from, 
          to: to, 
          total: totalItems 
        }) }}
      </p>
      <div
        v-if="showPerPage"
        class="flex items-center gap-2"
      >
        <span>{{ $t('common.pagination.rowsPerPage') }}</span>
        <Select v-model="selectValue">
          <SelectTrigger class="h-8 w-[70px] bg-transparent border-border/50 text-xs">
            <SelectValue :placeholder="String(perPage)" />
          </SelectTrigger>
          <SelectContent
            side="top"
            :side-offset="5"
          >
            <SelectItem
              v-for="option in perPageOptions"
              :key="option"
              :value="String(option)"
            >
              {{ option }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
    
    <!-- Right: Navigation buttons -->
    <div class="flex items-center gap-2">
      <!-- First page button (optional) -->
      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        class="h-8 w-8 p-0 transition-none"
        @click="goToPage(1)"
      >
        <ChevronsLeft class="w-4 h-4" />
      </Button>
      
      <!-- Previous button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        class="transition-none"
        @click="goToPage(currentPage - 1)"
      >
        <ChevronLeft class="w-4 h-4 mr-1" />
        {{ $t('common.pagination.previous') }}
      </Button>
      
      <!-- Page numbers (optional) -->
      <div
        v-if="showPageNumbers"
        class="hidden sm:flex items-center gap-1"
      >
        <template
          v-for="page in visiblePages"
          :key="page"
        >
          <Button
            v-if="page !== '...'"
            :variant="page === currentPage ? 'default' : 'outline'"
            size="sm"
            :class="cn(
              'h-8 w-8 p-0 transition-none',
              page === currentPage ? '!text-white !bg-primary' : 'text-foreground/70'
            )"
            @click="goToPage(page)"
          >
            {{ page }}
          </Button>
          <span
            v-else
            class="px-2 text-foreground/60"
          >...</span>
        </template>
      </div>
      
      <!-- Next button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        class="transition-none"
        @click="goToPage(currentPage + 1)"
      >
        {{ $t('common.pagination.next') }}
        <ChevronRight class="w-4 h-4 ml-1" />
      </Button>
      
      <!-- Last page button (optional) -->
      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        class="h-8 w-8 p-0 transition-none"
        @click="goToPage(totalPages)"
      >
        <ChevronsRight class="w-4 h-4" />
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import ChevronsLeft from 'lucide-vue-next/dist/esm/icons/chevrons-left.js';
import ChevronsRight from 'lucide-vue-next/dist/esm/icons/chevrons-right.js';
import Button from './Button.vue';
import Select from './Select.vue';
import SelectContent from './SelectContent.vue';
import SelectItem from './SelectItem.vue';
import SelectTrigger from './SelectTrigger.vue';
import SelectValue from './SelectValue.vue';
import { cn } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

useI18n();

const props = withDefaults(defineProps<{
  currentPage: number;
  totalItems: number;
  perPage?: number;
  perPageOptions?: number[];
  showPerPage?: boolean;
  showPageNumbers?: boolean;
  showFirstLast?: boolean;
  maxVisiblePages?: number;
  class?: HTMLAttributes['class'];
}>(), {
  perPage: 10,
  perPageOptions: () => [5, 10, 15, 20, 25, 50, 100],
  showPerPage: true,
  showPageNumbers: false,
  showFirstLast: false,
  maxVisiblePages: 5,
  class: '',
});

const emit = defineEmits<{
  'update:currentPage': [page: number];
  'update:perPage': [perPage: number];
  'page-change': [page: number];
  'per-page-change': [perPage: number];
}>();

// Computed properties
const totalPages = computed(() => {
  return Math.ceil(props.totalItems / props.perPage) || 1;
});

const from = computed(() => {
  return (props.currentPage - 1) * props.perPage + 1;
});

const to = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const visiblePages = computed(() => {
  const pages: (number | string)[] = [];
  const total = totalPages.value;
  const current = props.currentPage;
  const max = props.maxVisiblePages;
  
  if (total <= max) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    const half = Math.floor(max / 2);
    let start = current - half;
    let end = current + half;
    
    if (start < 1) {
      start = 1;
      end = max;
    }
    
    if (end > total) {
      end = total;
      start = total - max + 1;
    }
    
    if (start > 1) {
      pages.push(1);
      if (start > 2) pages.push('...');
    }
    
    for (let i = start; i <= end; i++) {
      if (i > 1 && i < total) {
        pages.push(i);
      }
    }
    
    if (end < total) {
      if (end < total - 1) pages.push('...');
      pages.push(total);
    }
  }
  
  return pages;
});

// Methods
const goToPage = (page: number | string) => {
  if (typeof page === 'string' || page < 1 || page > totalPages.value || page === props.currentPage) return;
  emit('update:currentPage', page);
  emit('page-change', page);
};

// Computed properties
const selectValue = computed({
  get: () => String(props.perPage),
  set: (val: string) => handlePerPageChange(val)
});

const handlePerPageChange = (value: string) => {
  const newPerPage = parseInt(value, 10);
  emit('update:perPage', newPerPage);
  emit('per-page-change', newPerPage);
  // Reset to page 1 when changing per page
  emit('update:currentPage', 1);
  emit('page-change', 1);
};

</script>
