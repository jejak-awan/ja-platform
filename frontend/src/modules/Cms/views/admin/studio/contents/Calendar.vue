<template>
  <div class="max-w-6xl mx-auto pb-10">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <h1 class="text-3xl font-bold tracking-tight text-foreground">
        {{ $t('modules.cms.content.list.calendar') }}
      </h1>
      <div class="flex flex-wrap items-center gap-3">
        <Select v-model="statusFilter">
          <SelectTrigger class="w-[140px] bg-background/50">
            <SelectValue :placeholder="$t('modules.cms.comments.filter.allStatus')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('modules.cms.comments.filter.allStatus') }}
            </SelectItem>
            <SelectItem value="draft">
              {{ $t('modules.cms.content.status.draft') }}
            </SelectItem>
            <SelectItem value="published">
              {{ $t('modules.cms.content.status.published') }}
            </SelectItem>
            <SelectItem value="scheduled">
              {{ $t('modules.cms.content.status.scheduled') }}
            </SelectItem>
          </SelectContent>
        </Select>

        <Select v-model="categoryFilter">
          <SelectTrigger class="w-[180px] bg-background/50">
            <SelectValue :placeholder="$t('modules.cms.content.form.selectCategory')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">
              {{ $t('modules.cms.content.form.selectCategory') }}
            </SelectItem>
            <SelectItem
              v-for="cat in categories"
              :key="cat.id"
              :value="cat.id.toString()"
            >
              {{ cat.name }}
            </SelectItem>
          </SelectContent>
        </Select>
                
        <Button
          as-child
          class="shadow-sm"
        >
          <router-link :to="{ name: 'contents.create' }">
            <Plus class="w-4 h-4 mr-2" />
            {{ $t('modules.cms.content.list.createNew') }}
          </router-link>
        </Button>
      </div>
    </div>

    <Card class="border-none shadow-sm p-6 bg-card/50 overflow-hidden">
      <div class="calendar-modern-container">
        <FullCalendar
          ref="calendar"
          :options="calendarOptions"
        />
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import api from '@/core/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';
import toast from '@/shared/services/legacy-toast';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import type { Content, Category } from '@/modules/Cms/types/cms';
import type { EventClickArg, EventContentArg, EventDropArg } from '@fullcalendar/core';
import type { DateClickArg } from '@fullcalendar/interaction';
import {
    Card,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/shared/components/ui';

const { t } = useI18n();
const router = useRouter();

interface ExtendedProps {
    status: string;
    category?: { name?: string };
    [key: string]: unknown;
}

const calendar = ref<{ getApi: () => { refetchEvents: () => void } } | null>(null);

const contents = ref<Content[]>([]);
const categories = ref<Category[]>([]);
const statusFilter = ref('all');
const categoryFilter = ref('all');

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay',
    },
    events: calendarEvents.value,
    editable: true,
    droppable: false,
    eventDrop: handleEventDrop,
    eventClick: handleEventClick,
    dateClick: handleDateClick,
    eventClassNames: getEventClassNames,
    eventContent: renderEventContent,
}));

const calendarEvents = computed(() => {
    return contents.value
        .filter(content => {
            if (statusFilter.value && statusFilter.value !== 'all' && content.status !== statusFilter.value) return false;
            if (categoryFilter.value && categoryFilter.value !== 'all' && content.category_id != categoryFilter.value) return false;
            return true;
        })
        .map(content => ({
            id: String(content.id),
            title: content.title,
            start: content.published_at || content.created_at,
            allDay: true,
            backgroundColor: getStatusColor(content.status || ''),
            borderColor: getStatusColor(content.status || ''),
            extendedProps: {
                status: content.status,
                category: content.category,
                author: content.author,
            },
        }));
});

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: '#6b7280',
        scheduled: '#f59e0b',
        published: '#10b981',
        archived: '#ef4444',
    };
    return colors[status] || '#6b7280';
};

const getEventClassNames = (arg: EventContentArg) => {
    const status = (arg.event.extendedProps as ExtendedProps).status;
    return [`status-${status}`];
};

const renderEventContent = (arg: EventContentArg) => {
    const properties = arg.event.extendedProps as ExtendedProps;
    const categoryName = properties.category?.name || '';
    return {
        html: `
            <div class="fc-event-title-container">
                <div class="fc-event-title">${arg.event.title}</div>
                ${categoryName ? `<div class="fc-event-category">${categoryName}</div>` : ''}
            </div>
        `,
    };
};

const handleEventDrop = async (info: EventDropArg) => {
    const contentId = info.event.id;
    const newDate = info.event.start;

    if (!newDate) return;

    try {
        await api.put(`/admin/cms/contents/${contentId}`, {
            published_at: newDate.toISOString().split('T')[0],
        });
        await fetchContents();
    } catch (_error: unknown) {
        logger.error('Failed to reschedule content:', _error);
        toast.error(t('modules.cms.content.messages.rescheduleFailed'));
        info.revert();
    }
};

const handleEventClick = (info: EventClickArg) => {
    router.push({ name: 'contents.edit', params: { id: info.event.id } });
};

const handleDateClick = (info: DateClickArg) => {
    router.push({
        name: 'contents.create',
        query: { date: info.dateStr },
    });
};

const fetchContents = async () => {
    try {
        const response = await api.get('/admin/cms/contents', {
            params: {
                per_page: 1000,
            },
        });
        const { data } = parseResponse(response);
        contents.value = ensureArray(data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch contents:', _error);
        contents.value = [];
    }
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (_error: unknown) {
        logger.error('Failed to fetch categories:', _error);
        categories.value = [];
    }
};

watch([statusFilter, categoryFilter], () => {
    if (calendar.value) {
        calendar.value.getApi().refetchEvents();
    }
});

onMounted(() => {
    fetchContents();
    fetchCategories();
});
</script>

<style>
/* FullCalendar styles */
.calendar-modern-container {
    --fc-border-color: hsl(var(--border) / 0.4);
    --fc-button-bg-color: transparent;
    --fc-button-border-color: hsl(var(--border));
    --fc-button-text-color: hsl(var(--foreground));
    --fc-button-hover-bg-color: hsl(var(--muted));
    --fc-button-hover-border-color: hsl(var(--border));
    --fc-button-active-bg-color: hsl(var(--accent));
    --fc-button-active-border-color: hsl(var(--border));
    --fc-today-bg-color: hsl(var(--primary) / 0.05);
}

.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 800 !important;
    letter-spacing: -0.025em;
}

.fc .fc-button {
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    text-transform: capitalize !important;
    border-radius: 8px !important;
    padding: 8px 16px !important;
    transition: all 0.2s ease !important;
}

.fc .fc-button-primary:not(:disabled):active, 
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: hsl(var(--primary)) !important;
    border-color: hsl(var(--primary)) !important;
    color: hsl(var(--primary-foreground)) !important;
}

.fc .fc-daygrid-day-number {
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    padding: 8px !important;
    opacity: 0.6;
}

.fc .fc-col-header-cell-cushion {
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 12px !important;
    color: hsl(var(--muted-foreground)) !important;
}

.fc-event-title-container {
    padding: 4px 8px;
    border-radius: 6px;
}

.fc-event-title {
    font-weight: 700;
    font-size: 0.75rem;
    line-height: 1.1;
}

.fc-event-category {
    font-size: 10px;
    opacity: 0.8;
    margin-top: 2px;
    font-weight: 500;
}

.fc-v-event {
    border: none !important;
    background: transparent !important;
}

.fc-daygrid-event {
    border-radius: 6px !important;
    margin: 2px 4px !important;
    transition: transform 0.2s ease !important;
    border: none !important;
}

.fc-daygrid-event:hover {
    transform: translateY(-1px) !important;
    filter: brightness(1.1) !important;
}
</style>

