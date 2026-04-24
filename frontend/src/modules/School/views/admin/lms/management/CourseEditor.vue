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
      class="max-w-5xl mx-auto"
    >
      <!-- Header -->
      <div class="flex justify-between items-center mb-10">
        <div class="flex items-center gap-4">
          <Button
            variant="ghost"
            size="icon"
            class="rounded-xl"
            @click="$router.back()"
          >
            <LucideIcon
              name="ArrowLeft"
              class="h-5 w-5"
            />
          </Button>
          <div>
            <h1 class="text-2xl font-bold tracking-tight">
              {{ isNew ? $t('features.school.lms.management.createCourse') : $t('features.school.lms.management.labels.editCurriculum') }}
            </h1>
            <p class="text-sm text-muted-foreground font-medium">
              {{ course?.title || $t('features.school.lms.management.labels.courseDetails') }}
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <Button
            class="px-8 h-12 rounded-xl font-bold"
            :disabled="saving"
            @click="saveCourse"
          >
            <LucideIcon
              v-if="saving"
              name="Loader2"
              class="mr-2 h-4 w-4 animate-spin"
            />
            {{ saving ? $t('common.actions.saving') : $t('common.actions.save') }}
          </Button>
        </div>
      </div>

      <!-- Main Info -->
      <Card class="mb-10 border-none shadow-sm overflow-hidden">
        <CardHeader class="bg-muted/30 border-b">
          <CardTitle class="text-sm font-bold text-muted-foreground tracking-wider">
            {{ $t('features.school.lms.management.labels.mainInfo') }}
          </CardTitle>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-xs font-bold text-muted-foreground">{{ $t('features.school.lms.management.labels.courseTitle') }}</Label>
              <Input
                v-model="course.title"
                class="h-11 border-muted bg-muted/20"
                :placeholder="$t('features.school.lms.management.placeholders.courseTitleHint')"
              />
            </div>
            <div class="space-y-2">
              <Label class="text-xs font-bold text-muted-foreground">Slug (URL)</Label>
              <Input
                v-model="course.slug"
                class="h-11 border-muted bg-muted/20"
                placeholder="mastery-of-vuejs"
              />
            </div>
            <div class="md:col-span-2 space-y-2">
              <Label class="text-xs font-bold text-muted-foreground">{{ $t('features.school.lms.management.labels.shortDescription') }}</Label>
              <Textarea
                v-model="course.description"
                rows="3"
                class="border-muted bg-muted/20 resize-none"
                :placeholder="$t('features.school.lms.management.placeholders.descriptionHint')"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-sm font-bold text-muted-foreground tracking-wider flex items-center gap-2">
          {{ $t('features.school.lms.management.labels.curriculumSections') }}
          <Badge
            variant="secondary"
            class="rounded-full px-2"
          >
            {{ course?.sections?.length || 0 }}
          </Badge>
        </h2>
        <Button
          variant="outline"
          size="sm"
          class="gap-2 font-bold rounded-lg h-9 border-primary/20 hover:bg-primary/5 hover:text-primary"
          @click="addSection"
        >
          <LucideIcon
            name="Plus"
            class="h-4 w-4"
          />
          {{ $t('features.school.lms.management.labels.addSection') }}
        </Button>
      </div>

      <div class="space-y-6 mb-20">
        <Card
          v-for="(section, sIdx) in (course?.sections || [])"
          :key="sIdx"
          class="border shadow-none overflow-hidden group"
        >
          <!-- Section Header -->
          <div class="p-4 bg-muted/30 flex justify-between items-center border-b">
            <div class="flex items-center gap-3 flex-1">
              <div class="w-8 h-8 rounded-lg bg-background flex items-center justify-center text-xs font-bold text-muted-foreground border shadow-sm">
                {{ Number(sIdx) + 1 }}
              </div>
              <Input
                v-model="section.title"
                class="bg-transparent border-none text-base font-bold text-foreground focus-visible:ring-0 p-0 h-auto shadow-none placeholder:text-muted-foreground/50"
                placeholder="Section Title"
              />
            </div>
            <div class="flex items-center gap-2">
              <Button
                variant="secondary"
                size="sm"
                class="gap-1.5 h-8 font-bold text-[10px] px-3"
                @click="addLesson(Number(sIdx))"
              >
                <LucideIcon
                  name="Plus"
                  class="h-3.5 w-3.5"
                />
                {{ $t('features.school.lms.management.labels.addLesson') }}
              </Button>
              <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/5"
                @click="removeSection(Number(sIdx))"
              >
                <LucideIcon
                  name="Trash2"
                  class="h-4 w-4"
                />
              </Button>
            </div>
          </div>

          <!-- Lessons List -->
          <Table>
            <TableBody>
              <TableRow
                v-for="(lesson, lIdx) in section.lessons"
                :key="lIdx"
                class="hover:bg-muted/10 group/row"
              >
                <TableCell class="w-12 text-center text-[10px] font-bold text-muted-foreground">
                  {{ Number(sIdx) + 1 }}.{{ Number(lIdx) + 1 }}
                </TableCell>
                <TableCell class="py-4">
                  <div class="space-y-1">
                    <Input
                      v-model="lesson.title"
                      class="h-8 px-0 border-none font-bold text-sm focus-visible:ring-0 shadow-none bg-transparent"
                      :placeholder="$t('features.school.lms.management.labels.lessonName')"
                    />
                    <div class="text-[10px] text-muted-foreground font-medium tracking-wider">
                      {{ $t('features.school.lms.management.labels.lessonName') }}
                    </div>
                  </div>
                </TableCell>
                <TableCell class="w-48">
                  <div class="space-y-1">
                    <Select v-model="lesson.type">
                      <SelectTrigger class="h-8 text-[10px] font-bold tracking-wider bg-background border-none shadow-none focus-visible:ring-0">
                        <SelectValue :placeholder="$t('features.school.lms.management.labels.selectType')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="video">
                          Video
                        </SelectItem>
                        <SelectItem value="text">
                          Text
                        </SelectItem>
                        <SelectItem value="quiz">
                          Quiz
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <div class="text-[10px] text-muted-foreground font-medium tracking-wider">
                      {{ $t('features.school.lms.management.labels.lessonType') }}
                    </div>
                  </div>
                </TableCell>
                <TableCell
                  v-if="lesson.type === 'quiz'"
                  class="w-64"
                >
                  <div class="space-y-1">
                    <Select v-model="lesson.exam_id">
                      <SelectTrigger class="h-8 text-[10px] font-bold bg-background border-none shadow-none">
                        <SelectValue :placeholder="$t('features.school.lms.management.labels.selectExam')" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="exam in exams"
                          :key="exam.id"
                          :value="exam.id.toString()"
                        >
                          {{ exam.title }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <div class="text-[10px] text-muted-foreground font-medium tracking-wider">
                      {{ $t('features.school.lms.management.labels.examReference') }}
                    </div>
                  </div>
                </TableCell>
                <TableCell v-else>
                  <!-- Space filler -->
                </TableCell>
                <TableCell class="text-right w-12 pr-4">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-muted-foreground/30 hover:text-destructive hover:bg-destructive/5 transition-colors"
                    @click="removeLesson(Number(sIdx), Number(lIdx))"
                  >
                    <LucideIcon
                      name="X"
                      class="h-4 w-4"
                    />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          
          <div
            v-if="!section.lessons?.length"
            class="p-10 text-center bg-muted/5"
          >
            <p class="text-xs font-medium text-muted-foreground italic">
              {{ $t('features.school.lms.management.messages.noLessons') }}
            </p>
          </div>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { LmsService } from '@/modules/School/services/LmsService';
import { parseResponse } from '@/utils/responseParser';
import { Button, Card, CardContent, CardHeader, CardTitle, Badge, Input, Label, Textarea, Table, TableBody, TableCell, TableRow, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, LucideIcon } from '@/components/ui';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const saving = ref(false);
const isNew = computed(() => !route.params.id);

const course = ref<any>({
  title: '',
  slug: '',
  description: '',
  status: 'draft',
  level: 'beginner',
  sections: []
});

const exams = ref<any[]>([]);

const fetchExams = async () => {
  try {
    const res = await LmsService.getExams();
    exams.value = (parseResponse(res).data as any[]) || [];
  } catch (error) {
    console.error('Failed to fetch exams:', error);
  }
};

const fetchCourse = async () => {
  if (isNew.value) return;
  loading.value = true;
  try {
    const res = await LmsService.getCourse(route.params.id as string);
    const data = parseResponse(res).data as any;
    if (data) {
      // Map IDs to strings for Select component
      data.sections?.forEach((s: any) => {
        s.lessons?.forEach((l: any) => {
          if (l.exam_id) l.exam_id = l.exam_id.toString();
        });
      });
      course.value = {
        ...data,
        sections: (data.sections || []).map((s: any) => ({
          ...s,
          lessons: s.lessons || []
        }))
      };
    }
  } catch (error) {
    console.error('Failed to fetch course:', error);
  } finally {
    loading.value = false;
  }
};

const addSection = () => {
  course.value.sections.push({
    title: '',
    sort_order: course.value.sections.length + 1,
    lessons: []
  });
};

const removeSection = (idx: number) => {
  if (!course.value.sections) return;
  course.value.sections.splice(idx, 1);
};

const addLesson = (sIdx: number) => {
  if (!course.value.sections?.[sIdx]) return;
  if (!course.value.sections[sIdx].lessons) {
    course.value.sections[sIdx].lessons = [];
  }
  course.value.sections[sIdx].lessons.push({
    title: '',
    type: 'text',
    sort_order: course.value.sections[sIdx].lessons.length + 1
  });
};

const removeLesson = (sIdx: number, lIdx: number) => {
  if (!course.value.sections?.[sIdx]) return;
  course.value.sections[sIdx].lessons.splice(lIdx, 1);
};

const saveCourse = async () => {
  saving.value = true;
  try {
    await LmsService.saveCourse(course.value);
    router.push({ name: 'admin.lms.manage' });
  } catch (error) {
    console.error('Save failed:', error);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchCourse();
  fetchExams();
});
</script>
