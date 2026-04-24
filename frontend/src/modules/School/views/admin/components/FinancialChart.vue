<template>
  <div class="w-full h-full min-h-[300px]">
    <Bar
      v-if="loaded"
      :data="chartData"
      :options="chartOptions"
    />
    <div
      v-else
      class="flex items-center justify-center h-full"
    >
      <LucideIcon
        name="Loader2"
        class="w-8 h-8 animate-spin text-primary opacity-50"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';
import { Bar } from 'vue-chartjs';
import { LucideIcon } from '@/components/ui';
import api from '@/services/api';
import { parseSingleResponse } from '@/utils/responseParser';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps<{
    schoolId: number | string;
}>();

const loaded = ref(false);
const chartData = ref<any>({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  datasets: []
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
  },
  scales: {
      y: {
          beginAtZero: true,
          ticks: {
              callback: (value: any) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
          }
      }
  }
};

const fetchChartData = async () => {
  try {
    const response = await api.get('/admin/analytics/financial-charts', {
        params: { school_id: props.schoolId }
    });
    const data = parseSingleResponse(response) as any;
    const income = data?.income || [];
    const expenses = data?.expenses || [];

    const incomeMap = new Array(12).fill(0);
    const expenseMap = new Array(12).fill(0);

    income.forEach((item: any) => {
        if (item && item.month) {
            incomeMap[item.month - 1] = item.total || 0;
        }
    });
    expenses.forEach((item: any) => {
        if (item && item.month) {
            expenseMap[item.month - 1] = item.total || 0;
        }
    });

    chartData.value.datasets = [
      {
        label: 'Pendapatan',
        backgroundColor: '#10b981',
        data: incomeMap
      },
      {
        label: 'Pengeluaran',
        backgroundColor: '#ef4444',
        data: expenseMap
      }
    ];
    loaded.value = true;
  } catch (e) {
    console.error(e);
  }
};

onMounted(() => {
  fetchChartData();
});
</script>
