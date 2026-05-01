<template>
  <div class="p-6 space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground">Manajemen Kursus</h1>
        <p class="text-muted-foreground text-sm mt-1">Kelola kurikulum dan materi pembelajaran sekolah secara terpusat.</p>
      </div>
      <Button @click="showCreateModal = true" class="font-bold shadow-lg shadow-primary/20 transition-all">
        <Plus class="w-4 h-4 mr-2" />
        Tambah Kursus Baru
      </Button>
    </div>

    <!-- Courses Table -->
    <Card class="border-border/50 shadow-sm overflow-hidden">
      <Table>
        <TableHeader class="bg-muted/30">
          <TableRow>
            <TableHead class="font-bold py-4 px-6">Judul Kursus</TableHead>
            <TableHead class="font-bold">Konteks Akademik</TableHead>
            <TableHead class="font-bold text-center">Level</TableHead>
            <TableHead class="font-bold text-center">Status</TableHead>
            <TableHead class="font-bold text-center">Modul</TableHead>
            <TableHead class="font-bold text-right px-6">Aksi</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="loading">
            <TableRow v-for="i in 3" :key="i">
              <TableCell colspan="6" class="py-6 px-6">
                <SkeletonLoader class="h-12 w-full rounded-xl" />
              </TableCell>
            </TableRow>
          </template>
          <template v-else>
            <TableRow v-for="course in courses" :key="course.id" class="hover:bg-muted/10 transition-colors">
            <TableCell class="py-4 px-6">
              <div class="font-bold text-foreground">{{ course.title }}</div>
              <div class="text-xs text-muted-foreground font-mono mt-0.5">{{ course.slug }}</div>
            </TableCell>
            <TableCell>
              <div class="flex flex-wrap gap-1.5">
                <Badge v-if="course.academic_year" variant="outline" class="text-[9px] font-bold">{{ course.academic_year?.year }}</Badge>
                <Badge v-if="course.semester" variant="secondary" class="text-[9px] font-bold">Sem {{ course.semester?.semester }}</Badge>
                <Badge v-if="course.department" variant="default" class="text-[9px] font-bold bg-indigo-500 hover:bg-indigo-600">{{ course.department?.name }}</Badge>
              </div>
            </TableCell>
            <TableCell class="text-center">
              <Badge variant="outline" class="uppercase text-[9px] font-bold tracking-wider">
                {{ course.level }}
              </Badge>
            </TableCell>
            <TableCell class="text-center">
              <Badge 
                :variant="course.status === 'published' ? 'default' : 'secondary'"
                class="uppercase text-[9px] font-bold tracking-wider"
                :class="{ 'bg-emerald-500 hover:bg-emerald-600': course.status === 'published' }"
              >
                {{ course.status }}
              </Badge>
            </TableCell>
            <TableCell class="text-center font-bold text-muted-foreground text-sm">
              {{ course.lessons_count || 0 }}
            </TableCell>
            <TableCell class="text-right px-6">
              <Button variant="ghost" size="sm" as-child class="font-bold text-primary hover:text-primary hover:bg-primary/10">
                <router-link :to="{ name: 'admin-lms-course-detail', params: { id: course.id }}">
                  Kelola
                  <ChevronRight class="w-4 h-4 ml-1" />
                </router-link>
              </Button>
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
    </Table>
    </Card>

    <!-- Create Course Modal -->
    <Dialog v-model:open="showCreateModal">
      <DialogContent class="sm:max-w-xl rounded-3xl p-0 overflow-hidden">
        <DialogHeader class="p-8 pb-4 text-left">
          <DialogTitle class="text-2xl font-bold">Buat Kursus Baru</DialogTitle>
        </DialogHeader>
        
        <div class="p-8 pt-0 space-y-6 overflow-y-auto max-h-[60vh]">
          <div class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">Judul Kursus</label>
            <Input v-model="form.title" placeholder="Contoh: Web Dev Mastery" class="h-12" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Tahun Ajaran</label>
              <Select v-model="form.academic_year_id">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Tahun" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="year in academicYears" :key="year.id" :value="String(year.id)">
                    {{ year.year }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Semester</label>
              <Select v-model="form.semester_id">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Semester" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="sem in semesters" :key="sem.id" :value="String(sem.id)">
                    Semester {{ sem.semester }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Jurusan / Departemen</label>
              <Select v-model="form.department_id">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Jurusan" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="dept in departments" :key="dept.id" :value="String(dept.id)">
                    {{ dept.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Tingkatan / Grade</label>
              <Select v-model="form.grade_id">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Tingkat" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="g in grades" :key="g.id" :value="String(g.id)">
                    Tingkat {{ g.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Level Kesulitan</label>
              <Select v-model="form.level">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Level" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="beginner">Beginner</SelectItem>
                  <SelectItem value="intermediate">Intermediate</SelectItem>
                  <SelectItem value="advanced">Advanced</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Status</label>
              <Select v-model="form.status">
                <SelectTrigger class="h-12">
                  <SelectValue placeholder="Pilih Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="draft">Draft</SelectItem>
                  <SelectItem value="published">Published</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <DialogFooter class="p-8 pt-4 border-t bg-muted/20 gap-2 sm:gap-0">
          <Button variant="outline" @click="showCreateModal = false" class="flex-1 rounded-xl font-bold h-12">Batal</Button>
          <Button @click="createCourse" :disabled="submitting" class="flex-1 rounded-xl font-bold h-12 shadow-lg shadow-primary/20">
            <Spinner v-if="submitting" class="mr-2 h-4 w-4" />
            Simpan Kursus
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLmsStore } from '../../../stores/lms';
import LmsService from '../../../services/LmsService';
import AcademicService from '../../../services/AcademicService';
import { Button } from '@/components/ui';
import { Input } from '@/components/ui';
import { Card } from '@/components/ui';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui';
import { Badge } from '@/components/ui';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui';
import { Spinner, SkeletonLoader } from '@/components/ui';
import { Plus, ChevronRight } from 'lucide-vue-next';

const lmsStore = useLmsStore();
const { courses, loading } = storeToRefs(lmsStore);

const showCreateModal = ref(false);
const submitting = ref(false);

// Academic Context Data
const academicYears = ref<any[]>([]);
const semesters = ref<any[]>([]);
const departments = ref<any[]>([]);
const grades = ref<any[]>([]);

const form = ref({
  title: '',
  level: 'beginner',
  status: 'draft',
  academic_year_id: '',
  semester_id: '',
  department_id: '',
  grade_id: '',
});

const createCourse = async () => {
  submitting.value = true;
  try {
    await LmsService.createCourse(form.value);
    await lmsStore.fetchAdminCourses();
    showCreateModal.value = false;
    form.value = { 
      title: '', level: 'beginner', status: 'draft',
      academic_year_id: '', semester_id: '', department_id: '', grade_id: ''
    };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const loadAcademicContext = async () => {
  try {
    const [years, sems, depts] = await Promise.all([
      AcademicService.getAcademicYears(),
      AcademicService.getSemesters(),
      AcademicService.getDepartments()
    ]);
    academicYears.value = years.data;
    semesters.value = sems.data;
    departments.value = depts.data;
    
    // Grades usually come from institution settings or academic service
    // For now we use a default if not found
  } catch (err) {
    console.error('Failed to load academic context', err);
  }
};

onMounted(() => {
  lmsStore.fetchAdminCourses();
  loadAcademicContext();
});
</script>
