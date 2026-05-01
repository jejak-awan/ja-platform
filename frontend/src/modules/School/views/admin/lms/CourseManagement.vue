<template>
  <div class="pb-8">
    <div class="px-6 space-y-8 animate-in fade-in duration-700">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center border border-primary/20 shadow-sm shadow-primary/10 transition-transform hover:scale-105 duration-300">
              <LucideIcon
                name="BookOpen"
                class="w-6 h-6 text-primary"
              />
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
              {{ $t('features.school.lms.title') }}
            </h1>
          </div>
          <p class="text-sm text-muted-foreground">
            {{ $t('features.school.lms.subtitle') }}
          </p>
        </div>
        <Button 
          size="lg"
          class="rounded-xl shadow-sm px-8 h-11 font-bold transition-all active:scale-95"
          @click="showCreateModal = true" 
        >
          <LucideIcon name="Plus" class="w-4 h-4 mr-2" />
          {{ $t('features.school.lms.actions.addNew') }}
        </Button>
      </div>

      <!-- Courses Table -->
      <Card class="border-border/40 bg-card shadow-sm rounded-2xl overflow-hidden">
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-muted/30">
              <TableRow>
                <TableHead class="font-semibold py-4 px-6">{{ $t('features.school.lms.labels.courseTitle') }}</TableHead>
                <TableHead class="font-semibold">{{ $t('features.school.lms.labels.academicContext') }}</TableHead>
                <TableHead class="font-semibold text-center">{{ $t('features.school.lms.labels.level') }}</TableHead>
                <TableHead class="font-semibold text-center">{{ $t('features.school.lms.labels.status') }}</TableHead>
                <TableHead class="font-semibold text-center">{{ $t('features.school.lms.labels.modules') }}</TableHead>
                <TableHead class="font-semibold text-right px-6">{{ $t('common.labels.action') }}</TableHead>
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
                    <div class="text-[10px] text-muted-foreground font-bold mt-0.5">{{ course.slug }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="flex flex-wrap gap-1.5">
                      <Badge v-if="course.academic_year" variant="outline" class="text-[9px] font-bold border-border/50">{{ course.academic_year?.year }}</Badge>
                      <Badge v-if="course.semester" variant="secondary" class="text-[9px] font-bold">Sem {{ course.semester?.semester }}</Badge>
                      <Badge v-if="course.department" variant="default" class="text-[9px] font-bold bg-primary/20 text-primary border-primary/20 hover:bg-primary/30">{{ course.department?.name }}</Badge>
                    </div>
                  </TableCell>
                  <TableCell class="text-center">
                    <Badge variant="outline" class="uppercase text-[9px] font-black tracking-[0.15em] border-border/50">
                      {{ course.level }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center">
                    <Badge 
                      :variant="course.status === 'published' ? 'default' : 'secondary'"
                      class="uppercase text-[9px] font-black tracking-[0.15em] border"
                      :class="course.status === 'published' ? 'bg-success/10 text-success border-success/20' : 'bg-muted text-muted-foreground border-border/50'"
                    >
                      {{ course.status }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center font-bold text-muted-foreground text-sm">
                    <span class="px-2 py-1 rounded bg-muted/50 text-xs">{{ course.lessons_count || 0 }}</span>
                  </TableCell>
                  <TableCell class="text-right px-6">
                    <Button variant="ghost" size="sm" as-child class="font-bold rounded-lg h-8 px-4 hover:bg-primary/10 hover:text-primary transition-colors">
                      <router-link :to="{ name: 'admin-lms-course-detail', params: { id: course.id }}">
                        {{ $t('features.school.lms.actions.manage') }}
                        <LucideIcon name="ChevronRight" class="w-4 h-4 ml-1" />
                      </router-link>
                    </Button>
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- Create Course Modal -->
      <Dialog v-model:open="showCreateModal">
        <DialogContent class="sm:max-w-xl rounded-2xl p-0 overflow-hidden border-border/50">
          <DialogHeader class="p-8 pb-4 text-left bg-muted/20 border-b border-border/40">
            <DialogTitle class="text-2xl font-semibold tracking-tight">{{ $t('features.school.lms.actions.createTitle') }}</DialogTitle>
          </DialogHeader>
          
          <div class="p-8 space-y-6 overflow-y-auto max-h-[60vh]">
            <div class="space-y-2">
              <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.courseTitle') }}</label>
              <Input v-model="form.title" :placeholder="$t('features.school.lms.placeholders.titleHint')" class="h-12 rounded-xl bg-muted/10 border-border/50" />
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.academicYear') }}</label>
                <Select v-model="form.academic_year_id">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectYear')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="year in academicYears" :key="year.id" :value="String(year.id)">
                      {{ year.year }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.semester') }}</label>
                <Select v-model="form.semester_id">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectSemester')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="sem in semesters" :key="sem.id" :value="String(sem.id)">
                      Semester {{ sem.semester }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.department') }}</label>
                <Select v-model="form.department_id">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectDept')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="dept in departments" :key="dept.id" :value="String(dept.id)">
                      {{ dept.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.grade') }}</label>
                <Select v-model="form.grade_id">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectGrade')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="g in grades" :key="g.id" :value="String(g.id)">
                      Tingkat {{ g.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.difficulty') }}</label>
                <Select v-model="form.level">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectLevel')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="beginner">Beginner</SelectItem>
                    <SelectItem value="intermediate">Intermediate</SelectItem>
                    <SelectItem value="advanced">Advanced</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-bold text-muted-foreground">{{ $t('features.school.lms.labels.status') }}</label>
                <Select v-model="form.status">
                  <SelectTrigger class="h-12 rounded-xl bg-muted/10 border-border/50">
                    <SelectValue :placeholder="$t('features.school.lms.placeholders.selectStatus')" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>

          <DialogFooter class="p-8 pt-4 border-t border-border/40 bg-muted/20 gap-3">
            <Button variant="ghost" @click="showCreateModal = false" class="flex-1 rounded-xl font-bold h-12">{{ $t('common.actions.cancel') }}</Button>
            <Button @click="createCourse" :disabled="submitting" class="flex-1 rounded-xl font-bold h-12 shadow-lg shadow-primary/20 transition-all active:scale-95">
              <Spinner v-if="submitting" class="mr-2 h-4 w-4" />
              <LucideIcon v-else name="CircleCheck2" class="w-4 h-4 mr-2" />
              {{ $t('features.school.lms.actions.saveCourse') }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLmsStore } from '../../../stores/lms';
import LmsService from '../../../services/LmsService';
import AcademicService from '../../../services/AcademicService';
import { 
  Button, Input, Card, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, 
  Badge, Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, 
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue, 
  Spinner, SkeletonLoader, LucideIcon 
} from '@/components/ui';

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
  } catch (err) {
    console.error('Failed to load academic context', err);
  }
};

onMounted(() => {
  lmsStore.fetchAdminCourses();
  loadAcademicContext();
});
</script>
