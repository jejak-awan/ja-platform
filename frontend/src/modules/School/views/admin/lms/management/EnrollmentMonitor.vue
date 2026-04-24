<template>
  <div class="p-6 min-h-screen">
    <div
      v-if="loading"
      class="flex justify-center items-center h-64"
    >
      <LucideIcon
        name="Loader2"
        class="h-8 w-8 animate-spin text-primary"
      />
    </div>

    <div
      v-else
      class="max-w-6xl mx-auto"
    >
      <!-- Breadcrumbs & Header -->
      <div class="mb-8">
        <nav class="flex items-center gap-2 text-sm font-medium text-muted-foreground mb-4">
          <router-link
            :to="{ name: 'admin.lms.manage' }"
            class="hover:text-primary transition-colors"
          >
            {{ $t('features.school.lms.management.title') }}
          </router-link>
          <LucideIcon
            name="ChevronRight"
            class="h-4 w-4"
          />
          <span class="text-foreground">{{ $t('features.school.lms.management.labels.monitorProgress') }}</span>
        </nav>
        <h1 class="text-3xl font-bold tracking-tight">
          {{ course?.title }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ $t('features.school.lms.management.subtitle') }}
        </p>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <Card>
          <CardHeader class="pb-2">
            <CardDescription class="text-xs font-medium">
              {{ $t('features.school.lms.management.labels.enrolled') }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold">
              {{ enrollments.length }}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardDescription class="text-xs font-medium">
              {{ $t('features.school.lms.management.labels.avgProgress') }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-primary">
              {{ Math.round(avgProgress) }}%
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader class="pb-2">
            <CardDescription class="text-xs font-medium">
              {{ $t('features.school.lms.management.labels.completed') }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-green-600">
              {{ completedCount }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Monitoring Table -->
      <Card class="border-none shadow-sm overflow-hidden">
        <CardHeader class="flex flex-row items-center justify-between pb-4 bg-muted/20">
          <CardTitle class="text-lg font-bold">
            {{ $t('features.school.lms.management.labels.enrollmentList') }}
          </CardTitle>
          <Button
            variant="outline"
            size="sm"
            class="gap-2 border-none bg-background shadow-sm"
            @click="exportData"
          >
            <LucideIcon
              name="Download"
              class="h-4 w-4"
            />
            PDF
          </Button>
        </CardHeader>

        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="hover:bg-transparent border-none bg-muted/30">
                <TableHead class="pl-6 h-12">
                  {{ $t('features.school.lms.management.labels.studentName') }}
                </TableHead>
                <TableHead class="h-12">
                  {{ $t('features.school.lms.management.labels.enrollmentDate') }}
                </TableHead>
                <TableHead class="h-12">
                  {{ $t('features.school.lms.management.labels.avgProgress') }}
                </TableHead>
                <TableHead class="h-12">
                  {{ $t('features.school.lms.management.labels.status') }}
                </TableHead>
                <TableHead class="text-right pr-6 h-12">
                  {{ $t('features.school.lms.management.labels.lastActivity') }}
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="enr in enrollments"
                :key="enr.id"
                class="transition-colors group"
              >
                <TableCell class="pl-6 py-5">
                  <div class="flex items-center gap-3">
                    <Avatar class="h-9 w-9 border border-primary/10">
                      <AvatarImage
                        v-if="enr.student?.profile_picture"
                        :src="enr.student.profile_picture"
                      />
                      <AvatarFallback class="bg-primary/5 text-primary text-xs font-bold">
                        {{ enr.student?.name?.substring(0, 2).toUpperCase() }}
                      </AvatarFallback>
                    </Avatar>
                    <div>
                      <div class="font-bold text-sm tracking-tight">
                        {{ enr.student?.name }}
                      </div>
                      <div class="text-[10px] text-muted-foreground font-medium">
                        {{ enr.student?.student_number }}
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="text-muted-foreground font-medium">
                  {{ formatDate(enr.enrolled_at) }}
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-4 w-32">
                    <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden">
                      <div
                        class="h-full bg-primary transition-all duration-500"
                        :style="{ width: enr.progress + '%' }"
                      />
                    </div>
                    <span class="text-xs font-bold">{{ Math.round(enr.progress) }}%</span>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge
                    :variant="enr.status === 'completed' ? 'default' : 'secondary'"
                    class="font-bold"
                  >
                    {{ enr.status }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right pr-6 text-xs font-medium text-muted-foreground">
                  {{ formatLastActivity(enr) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          
          <div
            v-if="!enrollments.length"
            class="p-16 text-center"
          >
            <div class="text-muted/30 mb-4 flex justify-center">
              <LucideIcon
                name="Users"
                class="h-16 w-16"
              />
            </div>
            <p class="text-muted-foreground font-medium text-sm">
              {{ $t('features.school.lms.management.messages.noStudents') }}
            </p>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { LmsService } from '@/modules/School/services/LmsService';
import { parseResponse } from '@/utils/responseParser';
import dayjs from 'dayjs';
import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Badge, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, Avatar, AvatarImage, AvatarFallback, LucideIcon } from '@/components/ui';

const route = useRoute();
const loading = ref(true);
const enrollments = ref<any[]>([]);
const course = ref<{ title: string } | null>(null);

const fetchData = async () => {
  try {
    const [courseRes, monitorRes] = await Promise.all([
      LmsService.getCourse(route.params.id as string),
      LmsService.getCourseMonitoring(route.params.id as string)
    ]);
    course.value = (parseResponse(courseRes).data as any) as { title: string };
    enrollments.value = ((parseResponse(monitorRes).data as any) as any[]) || [];
  } catch (error) {
    console.error('Failed to fetch monitor data:', error);
  } finally {
    loading.value = false;
  }
};

const avgProgress = computed(() => {
  if (!enrollments.value.length) return 0;
  return enrollments.value.reduce((acc, e) => acc + (e.progress || 0), 0) / enrollments.value.length;
});

const completedCount = computed(() => {
  return enrollments.value.filter(e => e.status === 'completed').length;
});

const formatDate = (date: string) => date ? dayjs(date).format('DD MMM YYYY') : '-';
const formatLastActivity = (enr: any) => enr.updated_at ? dayjs(enr.updated_at).format('DD MMM • HH:mm') : 'None';

const exportData = () => {
    // Logic for exporting data if needed
};

onMounted(fetchData);
</script>
