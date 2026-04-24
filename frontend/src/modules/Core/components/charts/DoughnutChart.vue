<template>
  <Doughnut
    :data="chartData"
    :options="chartOptions"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import type {
    ChartOptions,
    ChartData
} from 'chart.js';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

interface ChartItem {
    [key: string]: unknown;
}

const props = withDefaults(defineProps<{
    data: ChartItem[];
    labelKey: string;
    valueKey?: string;
}>(), {
    valueKey: 'count',
});

const chartData = computed<ChartData<'doughnut'>>(() => {
    const labels = props.data.map(item => item[props.labelKey] || 'Unknown');
    const values = props.data.map(item => Number(item[props.valueKey]));

    return {
        labels: labels,
        datasets: [
            {
                backgroundColor: [
                    '#4F46E5', // indigo
                    '#10B981', // green
                    '#F59E0B', // amber
                    '#EF4444', // red
                    '#8B5CF6', // purple
                    '#EC4899', // pink
                    '#3B82F6', // blue
                    '#6366F1', // indigo
                ],
                data: values,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    // Explicitly define events
    events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'],
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 10,
                boxWidth: 8,
                font: {
                    size: 10
                }
            }
        },
        tooltip: {
            enabled: true,
            intersect: true, // Default for doughnut
        }
    },
    interaction: {
        mode: 'point',
        intersect: true,
    },
    cutout: '60%',
}));
</script>
