<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        E-Rapor & Nilai
      </h1>
      <p class="text-muted-foreground">
        Pantau hasil belajar dan perkembangan akademis Anda.
      </p>
    </div>

    <div
      v-if="loading"
      class="space-y-4"
    >
      <SkeletonLoader
        v-for="i in 5"
        :key="i"
        class="h-16 w-full"
      />
    </div>
    <div
      v-else-if="grades.length > 0"
      class="border rounded-xl overflow-hidden bg-card"
    >
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-[11px] font-semibold text-muted-foreground/70 bg-muted/20 border-b">
            <tr>
              <th class="p-4 text-left font-semibold">
                Mata Pelajaran
              </th>
              <th class="p-4 text-center font-semibold">
                Nilai Pengetahuan
              </th>
              <th class="p-4 text-center font-semibold">
                Nilai Keterampilan
              </th>
              <th class="p-4 text-center font-semibold">
                Nilai Akhir
              </th>
              <th class="p-4 text-center font-semibold">
                Predikat
              </th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr
              v-for="grade in grades"
              :key="grade.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="p-4 font-medium">
                {{ grade.subject?.name || 'Unknown Subject' }}
              </td>
              <td class="p-4 text-center">
                {{ grade.knowledge_score || '-' }}
              </td>
              <td class="p-4 text-center">
                {{ grade.skill_score || '-' }}
              </td>
              <td class="p-4 text-center font-semibold text-base">
                <span :class="{'text-destructive': grade.final_grade < 75, 'text-success': grade.final_grade >= 80}">
                  {{ grade.final_grade || '-' }}
                </span>
              </td>
              <td class="p-4 text-center">
                <Badge
                  variant="outline"
                  :class="{'bg-success/10 text-success': grade.predicate === 'A', 'bg-warning/10 text-warning': ['C', 'D'].includes(grade.predicate)}"
                >
                  {{ grade.predicate || '-' }}
                </Badge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div
      v-else
      class="text-center py-20 bg-muted/20 rounded-xl border border-dashed"
    >
      <LucideIcon
        name="GraduationCap"
        class="w-16 h-16 mx-auto mb-4 opacity-10"
      />
      <h3 class="text-lg font-semibold">
        Belum ada nilai
      </h3>
      <p class="text-muted-foreground">
        Nilai Anda akan muncul di sini setelah guru melakukan input.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { SkeletonLoader, LucideIcon, Badge } from '@/components/ui';
import api from '@/services/api';
import { parseResponse } from '@/utils/responseParser';

const loading = ref(true);
const grades = ref<any[]>([]);

const fetchGrades = async () => {
  try {
    const response = await api.get('/school/student/grades');
    grades.value = parseResponse(response).data || [];
  } catch (e) {
    console.error(e);
  }
};

onMounted(async () => {
  loading.value = true;
  await fetchGrades();
  loading.value = false;
});
</script>
