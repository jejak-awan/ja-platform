<template>
  <Line
    :data="chartData"
    :options="chartOptions"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import type {
    ChartOptions,
    ChartData,
    ScriptableContext
} from 'chart.js';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { useDarkMode } from '@/composables/useDarkMode';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface ChartItem {
    period: string;
    visits: number;
    [key: string]: unknown;
}

const props = withDefaults(defineProps<{
    data: ChartItem[];
    label?: string;
    compareData?: ChartItem[];
    compareLabel?: string;
}>(), {
    label: 'Visits',
    compareData: () => [],
    compareLabel: 'Previous'
});

const { isDark } = useDarkMode();

// Theme colors
const colors = computed(() => {
    return isDark.value
        ? {
            // Primary (Blue)
            borderColor: '#3b82f6', 
            details: 'rgba(59, 130, 246, 0.5)',
            gradientStart: 'rgba(59, 130, 246, 0.5)', 
            gradientStop: 'rgba(59, 130, 246, 0.0)',
            
            // Secondary (Purple)
            compareBorder: '#8b5cf6', // violet-500
            compareGradientStart: 'rgba(139, 92, 246, 0.5)',
            compareGradientStop: 'rgba(139, 92, 246, 0.0)',

            gridColor: 'rgba(255, 255, 255, 0.1)',
            tooltipBg: '#18181b', 
            tooltipText: '#fafafa', 
          }
        : {
            // Primary (Blue)
            borderColor: '#2563eb', 
            details: 'rgba(37, 99, 235, 0.5)',
            gradientStart: 'rgba(37, 99, 235, 0.25)',
            gradientStop: 'rgba(37, 99, 235, 0.0)',

            // Secondary (Purple)
            compareBorder: '#a855f7', // purple-500
            compareGradientStart: 'rgba(168, 85, 247, 0.25)',
            compareGradientStop: 'rgba(168, 85, 247, 0.0)',

            gridColor: 'rgba(0, 0, 0, 0.05)',
            tooltipBg: '#ffffff',
            tooltipText: '#09090b', 
          };
});

const chartData = computed<ChartData<'line'>>(() => {
    const datasets: ChartData<'line'>['datasets'] = [
        // Primary Dataset
        {
            label: props.label,
            borderColor: colors.value.borderColor,
            pointBackgroundColor: colors.value.borderColor,
            pointBorderColor: colors.value.borderColor,
            pointHoverBackgroundColor: colors.value.borderColor,
            pointHoverBorderColor: colors.value.borderColor,
            borderWidth: 2,
            pointRadius: 0, 
            pointHoverRadius: 4,
            fill: true,
            tension: 0.4, 
            backgroundColor: (context: ScriptableContext<'line'>) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, colors.value.gradientStart);
                gradient.addColorStop(1, colors.value.gradientStop);
                return gradient;
            },
            data: props.data.map(item => item.visits),
            order: 1,
        },
    ];

    // Add Comparison Dataset if available
    if (props.compareData && props.compareData.length > 0) {
        datasets.push({
            label: props.compareLabel,
            borderColor: colors.value.compareBorder,
            pointBackgroundColor: colors.value.compareBorder,
            pointBorderColor: colors.value.compareBorder,
            pointHoverBackgroundColor: colors.value.compareBorder,
            pointHoverBorderColor: colors.value.compareBorder,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 4,
            fill: true,
            tension: 0.4,
            backgroundColor: (context: ScriptableContext<'line'>) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, colors.value.compareGradientStart);
                gradient.addColorStop(1, colors.value.compareGradientStop);
                return gradient;
            },
            data: props.compareData.map(item => item.visits),
            order: 2,
        });
    }

    return {
        labels: props.data.map(item => item.period), // Assumes periods match
        datasets: datasets,
    };
});

const chartOptions = computed<ChartOptions<'line'>>(() => {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 400,
        },
        // Explicitly define events to avoid "includes of undefined" in minified chart.js
        events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'],
        resizeDelay: 100,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: !!(props.compareData && props.compareData.length > 0),
                position: 'top',
                labels: {
                    boxWidth: 12,
                    font: { size: 11 },
                },
            },
            tooltip: {
                enabled: true,
                mode: 'index',
                intersect: false,
                backgroundColor: colors.value.tooltipBg,
                titleColor: colors.value.tooltipText,
                bodyColor: colors.value.tooltipText,
                borderColor: colors.value.gridColor,
                borderWidth: 1,
                padding: 10,
                displayColors: true,
                boxPadding: 4,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                border: {
                    display: false, 
                    dash: [4, 4],
                },
                grid: {
                    color: colors.value.gridColor,
                    drawTicks: false,
                },
                ticks: {
                    precision: 0,
                    color: isDark.value ? '#a1a1aa' : '#71717a', 
                    font: {
                        size: 11,
                    }
                },
            },
            x: {
                border: {
                     display: false,
                },
                grid: {
                    display: false, 
                },
                ticks: {
                    color: isDark.value ? '#a1a1aa' : '#71717a',
                    font: {
                        size: 11,
                    },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 7, 
                },
            },
        },
    };
});
</script>
