<template>
  <div class="space-y-6 animate-in fade-in duration-700">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold tracking-tight">
        Input Nilai E-Rapor
      </h1>
      <p class="text-muted-foreground">
        Pilih kelas yang Anda ajar dan masukkan nilai per siswa secara bulk.
      </p>
    </div>

    <!-- Filters & Selection -->
    <Card class="border border-border/40 bg-card shadow-sm">
      <CardContent class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-2">
            <Label>Pilih Mata Pelajaran (Jadwal)</Label>
            <Select v-model="selectedClassId">
              <SelectTrigger
                :disabled="loadingSchedules"
                class="h-12 bg-background"
              >
                <SelectValue placeholder="-- Pilih Kelas & Mapel --" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="sched in uniqueClasses"
                  :key="sched.id"
                  :value="sched.id.toString()"
                >
                  {{ sched.subject?.name }} - Kelas {{ sched.study_group?.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
           
          <div class="space-y-2">
            <Label>Tahun Akademik</Label>
            <Select v-model="selectedAcademicYear">
              <SelectTrigger
                :disabled="loadingMeta"
                class="h-12 bg-background"
              >
                <SelectValue placeholder="-- Tahun Akademik --" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="ay in academicYears"
                  :key="ay.id"
                  :value="ay.id.toString()"
                >
                  {{ ay.year }} {{ ay.is_active ? '(Aktif)' : '' }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
           
          <div class="space-y-2">
            <Label>Semester</Label>
            <Select v-model="selectedSemester">
              <SelectTrigger
                :disabled="loadingMeta"
                class="h-12 bg-background"
              >
                <SelectValue placeholder="-- Pilih Semester --" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="sem in semesters"
                  :key="sem.id"
                  :value="sem.id.toString()"
                >
                  {{ sem.type }} {{ sem.is_active ? '(Aktif)' : '' }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Grading Table -->
    <Card
      v-if="selectedClassData && students.length > 0"
      class="border-none"
    >
      <CardContent class="p-0 border rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="text-[11px] font-semibold text-muted-foreground/70 bg-muted/20">
              <tr>
                <th
                  class="px-4 py-3 border-b"
                  rowspan="2"
                >
                  No
                </th>
                <th
                  class="px-4 py-3 border-b border-r"
                  rowspan="2"
                >
                  Nama Siswa
                </th>
                <th
                  class="px-4 py-2 border-b border-r text-center"
                  colspan="3"
                >
                  Nilai Pengetahuan
                </th>
                <th
                  class="px-4 py-2 border-b border-r text-center"
                  colspan="3"
                >
                  Nilai Keterampilan
                </th>
                <th
                  class="px-4 py-3 border-b text-center font-semibold"
                  rowspan="2"
                >
                  Pre-Kalkulasi<br>K & S
                </th>
              </tr>
              <tr>
                <th class="px-2 py-2 border-b bg-primary/5 text-center">
                  Harian
                </th>
                <th class="px-2 py-2 border-b bg-primary/5 text-center">
                  UTS
                </th>
                <th class="px-2 py-2 border-b border-r bg-primary/5 text-center">
                  UAS
                </th>
                <!-- Keterampilan -->
                <th class="px-2 py-2 border-b bg-success/5 text-center">
                  Praktik
                </th>
                <th class="px-2 py-2 border-b bg-success/5 text-center">
                  Projek
                </th>
                <th class="px-2 py-2 border-b border-r bg-success/5 text-center">
                  Portofolio
                </th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr
                v-for="(student, index) in students"
                :key="student.id"
                class="hover:bg-muted/10 transition-colors"
              >
                <td class="px-4 py-3 text-muted-foreground">
                  {{ index + 1 }}
                </td>
                <td class="px-4 py-3 border-r font-medium">
                  {{ student.full_name }}
                </td>
                      
                <!-- Pengetahuan -->
                <td class="px-2 py-2">
                  <Input
                    v-model.number="gradesDraft[student.id].daily_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                <td class="px-2 py-2">
                  <Input
                    v-model.number="gradesDraft[student.id].mid_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                <td class="px-2 py-2 border-r">
                  <Input
                    v-model.number="gradesDraft[student.id].final_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                      
                <!-- Keterampilan -->
                <td class="px-2 py-2">
                  <Input
                    v-model.number="gradesDraft[student.id].practice_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                <td class="px-2 py-2">
                  <Input
                    v-model.number="gradesDraft[student.id].project_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                <td class="px-2 py-2 border-r">
                  <Input
                    v-model.number="gradesDraft[student.id].portfolio_score"
                    type="number"
                    min="0"
                    max="100"
                    class="w-16 h-8 text-center"
                  />
                </td>
                      
                <!-- Preview -->
                <td class="px-4 py-3 text-center bg-muted/5 font-semibold tabular-nums">
                  <span class="text-primary">{{ calcK(gradesDraft[student.id]) }}</span>
                  <span class="mx-1 text-muted-foreground/30">|</span>
                  <span class="text-success">{{ calcS(gradesDraft[student.id]) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 bg-muted/20 border-t flex justify-end items-center gap-4">
          <span class="text-sm text-muted-foreground">Pastikan mengisi semua nilai sebelum menyimpan.</span>
          <Button
            class="px-8 shadow-lg shadow-indigo-600/20"
            :loading="submitLoading"
            @click="saveGrades"
          >
            <LucideIcon
              name="Save"
              class="w-4 h-4 mr-2"
            />
            Simpan Transkrip E-Rapor
          </Button>
        </div>
      </CardContent>
    </Card>

    <div
      v-else-if="selectedClassId && students.length === 0 && !loadingStudents"
      class="text-center py-20 text-muted-foreground border-2 border-dashed rounded-2xl"
    >
      <LucideIcon
        name="Users"
        class="w-12 h-12 mx-auto mb-2 opacity-20"
      />
      <p>Tidak ada siswa di kelas ini.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import {
  Card, CardContent, Button, LucideIcon,
  Select, SelectTrigger, SelectValue, SelectContent, SelectItem,
  Input, Label
} from '@/shared/components/ui';
import api from '@/core/api/client';
import { parseResponse } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';

const toast = useToast();

const loadingSchedules = ref(true);
const loadingMeta = ref(true);
const loadingStudents = ref(false);
const submitLoading = ref(false);

const schedules = ref<any[]>([]);
const academicYears = ref<any[]>([]);
const semesters = ref<any[]>([]);

const selectedClassId = ref('');
const selectedAcademicYear = ref('');
const selectedSemester = ref('');

const students = ref<any[]>([]);
const gradesDraft = ref<Record<string, any>>({}); // Key: student.id

const fetchMeta = async () => {
    try {
        const [yearsRes, semRes] = await Promise.all([
            api.get('/school/admin/academic/years'),
            api.get('/school/admin/academic/semesters') // Usually requires academic_year_id but we fetch all or it defaults
        ]);
        academicYears.value = parseResponse(yearsRes).data || [];
        semesters.value = parseResponse(semRes).data || [];
        
        // Auto select active ones
        const activeY = academicYears.value.find(y => y.is_active);
        if (activeY) selectedAcademicYear.value = activeY.id.toString();
        
        const activeS = semesters.value.find(s => s.is_active);
        if (activeS) selectedSemester.value = activeS.id.toString();
    } catch(e) {
        toast.error.fromResponse(e);
    } finally {
        loadingMeta.value = false;
    }
};

const fetchSchedules = async () => {
  try {
    const response = await api.get('/school/admin/teacher/schedules');
    schedules.value = parseResponse(response).data || [];
  } catch (e: any) {
    toast.error.fromResponse(e);
  } finally {
    loadingSchedules.value = false;
  }
};

// Map schedules into unique classes/subjects (teacher might have 2 schedules per week for same class, but E-Rapor is per group/subject)
const uniqueClasses = computed(() => {
   const map = new Map();
   for (const s of schedules.value) {
      const key = `${s.study_group_id}-${s.subject_id}`;
      if (!map.has(key)) map.set(key, s);
   }
   return Array.from(map.values());
});

const selectedClassData = computed(() => {
   return schedules.value.find(s => s.id.toString() === selectedClassId.value);
});

// Calculate Average (Preview before saving)
const calcK = (grade: any) => {
   const k = [grade.daily_score, grade.mid_score, grade.final_score].filter(x => typeof x === 'number' && !isNaN(x));
   if (!k.length) return '-';
   return (k.reduce((a, b) => a + b, 0) / k.length).toFixed(1);
};
const calcS = (grade: any) => {
   const s = [grade.practice_score, grade.project_score, grade.portfolio_score].filter(x => typeof x === 'number' && !isNaN(x));
   if (!s.length) return '-';
   return (s.reduce((a, b) => a + b, 0) / s.length).toFixed(1);
};

const fetchStudentsInClass = async () => {
   if (!selectedClassData.value) return;
   loadingStudents.value = true;
   students.value = [];
   gradesDraft.value = {};
   try {
      const response = await api.get(`/school/admin/academic/study-groups/${selectedClassData.value.study_group_id}/members`);
      students.value = parseResponse(response).data || [];
      
      // Initialize draft
      students.value.forEach(st => {
         gradesDraft.value[st.id] = {
             student_id: st.id,
             daily_score: null, mid_score: null, final_score: null,
             practice_score: null, project_score: null, portfolio_score: null
         };
      });
      // Optionally we could try fetching existing grades if there was an endpoint, but bulk UPSERT works for new inputs
   } catch(e) {
      toast.error.fromResponse(e);
   } finally {
      loadingStudents.value = false;
   }
};

watch(selectedClassId, fetchStudentsInClass);

const saveGrades = async () => {
    if (!selectedClassData.value || !selectedAcademicYear.value || !selectedSemester.value) {
        toast.error.action('Harap pilih Mata Pelajaran, Tahun Akademik dan Semester terlebih dahulu!');
        return;
    }

    submitLoading.value = true;
    const records = Object.values(gradesDraft.value).map(g => ({
       ...g,
       subject_id: selectedClassData.value.subject_id,
       academic_year_id: parseInt(selectedAcademicYear.value),
       semester_id: parseInt(selectedSemester.value)
    }));

    try {
        await api.post('/school/admin/academic/grades/bulk', { grades: records });
        toast.success.action('Nilai E-Rapor berhasil di-publish dan bisa dilihat siswa.');
    } catch(e) {
        toast.error.fromResponse(e);
    } finally {
        submitLoading.value = false;
    }
};

onMounted(() => {
  fetchMeta();
  fetchSchedules();
});
</script>
