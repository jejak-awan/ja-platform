<template>
  <div class="p-6 min-h-screen">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-start">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">
          {{ $t('features.school.lms.management.title') }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ $t('features.school.lms.management.subtitle') }}
        </p>
      </div>
      <Button
        class="gap-2 px-6 py-6 rounded-xl font-bold"
        @click="createNewCourse"
      >
        <LucideIcon
          name="Plus"
          class="h-5 w-5"
        />
        {{ $t('features.school.lms.management.createCourse') }}
      </Button>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card
        v-for="(val, key) in displayStats"
        :key="key"
      >
        <CardHeader class="pb-2">
          <CardDescription class="text-xs font-medium text-muted-foreground">
            {{ $t(`features.school.lms.management.stats.${key}`) }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-bold">
            {{ val }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Course List -->
    <Card class="overflow-hidden border-none shadow-sm">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
        <CardTitle class="text-xl font-bold">
          {{ $t('features.school.lms.management.labels.yourCourses') }}
        </CardTitle>
        <div class="flex items-center gap-2">
          <div class="relative w-64">
            <LucideIcon
              name="Search"
              class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
            />
            <Input 
              type="text" 
              :placeholder="$t('features.school.lms.management.labels.searchPlaceholder')" 
              class="pl-9 bg-muted/50 border-none h-10"
            />
          </div>
        </div>
      </CardHeader>
      
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="hover:bg-transparent border-none bg-muted/30">
              <TableHead class="pl-6 h-12">
                {{ $t('features.school.lms.management.labels.courseDetails') }}
              </TableHead>
              <TableHead class="text-center h-12">
                {{ $t('features.school.lms.management.labels.enrolled') }}
              </TableHead>
              <TableHead class="text-center h-12">
                {{ $t('features.school.lms.management.labels.avgProgress') }}
              </TableHead>
              <TableHead class="h-12">
                {{ $t('features.school.lms.management.labels.status') }}
              </TableHead>
              <TableHead class="text-right pr-6 h-12">
                {{ $t('features.school.lms.management.labels.actions') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="course in courses"
              :key="course.id"
              class="group transition-colors"
            >
              <TableCell class="pl-6 py-5">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center overflow-hidden shrink-0 border border-primary/5">
                    <img
                      v-if="course.thumbnail"
                      :src="course.thumbnail"
                      class="w-full h-full object-cover"
                    >
                    <LucideIcon
                      v-else
                      name="BookOpen"
                      class="w-6 h-6 text-primary"
                    />
                  </div>
                  <div>
                    <div class="font-bold text-base group-hover:text-primary transition-colors">
                      {{ course.title }}
                    </div>
                    <div class="text-xs text-muted-foreground font-medium">
                      {{ course.subject?.name }} • {{ course.level }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-center font-bold">
                {{ course.enrollments_count || 0 }} {{ $t('features.school.students.title') }}
              </TableCell>
              <TableCell>
                <div class="flex flex-col items-center gap-2">
                  <div class="w-24 h-1.5 bg-muted rounded-full overflow-hidden">
                    <div 
                      class="h-full bg-primary transition-all duration-500" 
                      :style="{ width: (course.avg_progress || 0) + '%' }"
                    />
                  </div>
                  <span class="text-[10px] font-bold text-muted-foreground">{{ Math.round(course.avg_progress || 0) }}%</span>
                </div>
              </TableCell>
              <TableCell>
                <Badge
                  :variant="course.status === 'published' ? 'default' : 'secondary'"
                  class="font-bold"
                >
                  {{ course.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-right pr-6">
                <div class="flex justify-end gap-1">
                  <TooltipProvider :delay-duration="0">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="text-muted-foreground hover:text-primary hover:bg-primary/5 h-9 w-9"
                          @click="viewMonitoring(course.id)"
                        >
                          <LucideIcon
                            name="Monitor"
                            class="h-4.5 w-4.5"
                          />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>{{ $t('features.school.lms.management.labels.monitorProgress') }}</TooltipContent>
                    </Tooltip>
                  </TooltipProvider>

                  <TooltipProvider :delay-duration="0">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="text-muted-foreground hover:text-primary hover:bg-primary/5 h-9 w-9"
                          @click="editCourse(course.id)"
                        >
                          <LucideIcon
                            name="Edit"
                            class="h-4.5 w-4.5"
                          />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>{{ $t('features.school.lms.management.labels.editContent') }}</TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { LmsService } from '@/modules/School/services/LmsService';
import { useRouter } from 'vue-router';
import { parseResponse } from '@/utils/responseParser';
import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Badge, Input, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, LucideIcon, Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui';

const router = useRouter();
const courses = ref<any[]>([]);
const stats = ref<{
  total_courses: number;
  total_students: number;
  average_progress: number;
  active_sessions: number;
}>({
  total_courses: 0,
  total_students: 0,
  average_progress: 0,
  active_sessions: 0
});

const displayStats = computed(() => ({
  totalCourses: stats.value.total_courses,
  totalStudents: stats.value.total_students,
  avgProgress: Math.round(stats.value.average_progress || 0) + '%',
  activeSessions: stats.value.active_sessions
}));

const fetchData = async () => {
  try {
    const [coursesRes, statsRes] = await Promise.all([
      LmsService.getManagementCourses(),
      LmsService.getManagementStats()
    ]);
    
    courses.value = (parseResponse(coursesRes).data as any[]) || [];
    
    const s = parseResponse(statsRes).data as any;
    if (s) {
      stats.value = {
        total_courses: s.total_courses || 0,
        total_students: s.total_students || 0,
        average_progress: s.average_progress || 0,
        active_sessions: s.recent_enrollments?.length || 0
      };
    }
  } catch (error) {
    console.error('Failed to fetch management data:', error);
  }
};

const createNewCourse = () => {
  router.push({ name: 'admin.lms.manage.editor' });
};

const editCourse = (id: number) => {
  router.push({ name: 'admin.lms.manage.editor', params: { id: id.toString() } });
};

const viewMonitoring = (id: number) => {
  router.push({ name: 'admin.lms.manage.monitoring', params: { id: id.toString() } });
};

onMounted(fetchData);
</script>
>
