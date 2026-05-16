<template>
  <div class="chart-container w-full h-full">
    <Bar
      v-if="isMounted"
      :data="chartData"
      :options="chartOptions"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Bar } from 'vue-chartjs';
import type {
    ChartOptions,
    ChartData
} from 'chart.js';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const isMounted = ref(false);

onMounted(async () => {
    await nextTick();
    isMounted.value = true;
});

onBeforeUnmount(() => {
    isMounted.value = false;
});

interface ChartItem {
    [key: string]: unknown;
}

const props = defineProps<{
    data: ChartItem[];
    labelKey: string;
    valueKey?: string;
    horizontal?: boolean;
}>();

const valueKey = computed(() => props.valueKey || 'count');
const isHorizontal = computed(() => props.horizontal !== false);

const chartData = computed<ChartData<'bar'>>(() => {
    return {
        labels: props.data.map(item => item[props.labelKey] || 'Unknown').slice(0, 10),
        datasets: [
            {
                label: 'Visits',
                backgroundColor: '#3B82F6', // blue-500
                data: props.data.map(item => Number(item[valueKey.value])).slice(0, 10),
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    indexAxis: isHorizontal.value ? 'y' : 'x',
    responsive: true,
    maintainAspectRatio: false,
    // Explicitly define events
    events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'],
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            enabled: true,
            mode: isHorizontal.value ? 'y' : 'x',
            intersect: false,
        }
    },
    interaction: {
        mode: isHorizontal.value ? 'y' : 'x',
        intersect: false,
    },
    scales: {
        x: {
            beginAtZero: true,
        },
    },
}));
</script>

<style scoped>
.chart-container :deep(canvas) {
    animation: revert !important;
    transition: revert !important;
}
</style>

