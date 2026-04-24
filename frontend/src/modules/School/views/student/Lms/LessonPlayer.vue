<template>
  <div
    v-if="loading"
    class="h-screen flex justify-center items-center bg-gray-950"
  >
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500" />
  </div>

  <div
    v-else-if="course"
    class="h-screen bg-white flex flex-col md:flex-row overflow-hidden"
  >
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
      <!-- Top Navbar -->
      <nav class="h-16 px-6 bg-white border-b border-gray-100 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
          <button
            class="p-2 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-900 transition-colors"
            @click="backToCourse"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            /></svg>
          </button>
          <div class="hidden md:block">
            <h1 class="font-extrabold text-gray-900 truncate max-w-md">
              {{ currentLesson?.title }}
            </h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">
              {{ course.title }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <button 
            v-if="!isCompleted(currentLesson?.id)" 
            :disabled="marking"
            class="px-5 py-2 bg-indigo-600 text-white text-sm font-extrabold rounded-xl hover:bg-indigo-700 disabled:opacity-50 shadow-md shadow-indigo-100"
            @click="markCompleted"
          >
            {{ marking ? 'Saving...' : 'Mark as Completed' }}
          </button>
          <div
            v-else
            class="flex items-center gap-2 px-4 py-2 bg-green-50 text-green-600 rounded-xl text-sm font-extrabold"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            ><path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            /></svg>
            Completed
          </div>
          <button
            class="md:hidden p-2 hover:bg-gray-100 rounded-lg"
            @click="toggleSidebar"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            /></svg>
          </button>
        </div>
      </nav>

      <!-- Player/Viewer Container -->
      <div class="flex-1 overflow-y-auto bg-gray-50/30 p-4 md:p-8">
        <div class="max-w-4xl mx-auto">
          <!-- Video Player -->
          <div
            v-if="currentLesson?.type === 'video' && currentLesson?.video_url"
            class="aspect-video rounded-3xl overflow-hidden bg-black shadow-2xl mb-8"
          >
            <iframe 
              class="w-full h-full" 
              :src="embedUrl(currentLesson.video_url)" 
              frameborder="0" 
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
              allowfullscreen
            />
          </div>

          <!-- Content Viewer -->
          <div class="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6">
              {{ currentLesson?.title }}
            </h2>
            <SafeHtml
              class="prose prose-indigo max-w-none text-gray-600 leading-relaxed"
              :html="currentLesson?.content || ''"
              mode="cms"
            />
          </div>

          <!-- Quiz Link -->
          <div
            v-if="currentLesson?.type === 'quiz' && currentLesson?.exam_id"
            class="mt-8 bg-indigo-600 rounded-3xl p-8 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl shadow-indigo-100"
          >
            <div class="flex items-center gap-6">
              <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-8 w-8"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                /></svg>
              </div>
              <div>
                <h3 class="text-xl font-bold">
                  Time for a Quiz!
                </h3>
                <p class="text-indigo-100">
                  Test your understanding of the current topic.
                </p>
              </div>
            </div>
            <button
              class="px-8 py-4 bg-white text-indigo-600 font-extrabold rounded-2xl hover:bg-gray-50 transition-colors shadow-lg shadow-black/10"
              @click="startExam(currentLesson.exam_id)"
            >
              Start Quiz
            </button>
          </div>
          
          <!-- Navigation Footer -->
          <div class="mt-12 flex justify-between items-center py-8 border-t border-gray-100">
            <button 
              :disabled="!hasPrev" 
              class="flex items-center gap-3 text-sm font-bold text-gray-600 hover:text-indigo-600 disabled:opacity-30 transition-colors"
              @click="prevLesson"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
              /></svg>
              Previous Lesson
            </button>
            <button 
              :disabled="!hasNext" 
              class="group flex items-center gap-3 text-sm font-bold text-indigo-600 disabled:opacity-30 transition-colors"
              @click="nextLesson"
            >
              Next Lesson
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 group-hover:translate-x-1 transition-transform"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              /></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Curriculum Sidebar -->
    <aside 
      :class="[
        'w-full md:w-80 lg:w-96 shrink-0 bg-white border-l border-gray-100 flex flex-col h-full transform transition-transform duration-300 z-20',
        sidebarVisible ? 'translate-x-0' : 'translate-x-full md:translate-x-0'
      ]"
    >
      <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
        <h2 class="font-extrabold text-gray-900">
          Course Curriculum
        </h2>
        <div class="px-2 py-1 bg-white rounded-md text-[10px] font-bold text-indigo-600 shadow-sm border border-gray-100">
          {{ Math.round(enrollment?.progress || 0) }}%
        </div>
      </div>
      <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
        <div
          v-for="(section, sIdx) in course.sections"
          :key="section.id"
        >
          <div class="px-6 py-4 bg-gray-50/30 flex items-center justify-between sticky top-0 bg-white/80 backdrop-blur-md z-10 border-b border-gray-50">
            <span class="text-xs font-extrabold text-gray-500 uppercase tracking-widest">Section {{ sIdx + 1 }}</span>
            <span class="text-[10px] font-bold text-gray-400 capitalize">{{ section.title }}</span>
          </div>
          <div 
            v-for="(lesson, lIdx) in section.lessons" 
            :key="lesson.id"
            :class="[
              'px-6 py-5 cursor-pointer border-l-4 transition-all hover:bg-indigo-50/30 group',
              currentLesson?.id === lesson.id ? 'bg-indigo-50/50 border-indigo-600' : 'border-transparent'
            ]"
            @click="switchLesson(lesson.id)"
          >
            <div class="flex items-start gap-4">
              <div :class="['mt-1 shrink-0', isCompleted(lesson.id) ? 'text-green-500' : 'text-gray-300 group-hover:text-indigo-600']">
                <svg
                  v-if="isCompleted(lesson.id)"
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                ><path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                /></svg>
                <div
                  v-else
                  class="w-5 h-5 rounded-full border-2 border-current flex items-center justify-center text-[10px] font-bold"
                >
                  {{ lIdx + 1 }}
                </div>
              </div>
              <div class="min-w-0">
                <h4 :class="['text-sm font-bold truncate leading-tight transition-colors', currentLesson?.id === lesson.id ? 'text-indigo-600' : 'text-gray-900 group-hover:text-indigo-600']">
                  {{ lesson.title }}
                </h4>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1 flex items-center gap-2">
                  {{ lesson.type }} • {{ lesson.duration || 5 }}m
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </aside>
    <!-- Overlay for mobile -->
    <div
      v-if="sidebarVisible"
      class="fixed inset-0 bg-black/50 z-10 md:hidden"
      @click="toggleSidebar"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';
import SafeHtml from '@/modules/Core/components/ui/SafeHtml.vue';

interface Lesson {
    id: number;
    title: string;
    type: string;
    content: string;
    video_url?: string;
    exam_id?: number | string;
    duration?: number;
}

interface Section {
    id: number;
    title: string;
    lessons: Lesson[];
}

interface Course {
    id: number;
    title: string;
    sections: Section[];
}

interface Enrollment {
    id: number;
    progress: number;
    completed_lessons: number[];
}

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const marking = ref(false);
const sidebarVisible = ref(false);
const course = ref<Course | null>(null);
const enrollment = ref<Enrollment | null>(null);
const currentLesson = ref<Lesson | null>(null);

const fetchCourse = async () => {
  try {
    const courseId = route.params.courseId as string;
    const [courseRes, enrollmentRes] = await Promise.all([
      api.get(`/school/lms/courses/${courseId}`),
      api.get(`/school/lms/my-enrollment/${courseId}`)
    ]);
    course.value = courseRes.data.data;
    enrollment.value = enrollmentRes.data.data;
    
    // Set initial lesson
    const lessonId = parseInt(route.params.lessonId as string);
    findAndSetLesson(lessonId);
  } catch (error) {
    console.error('Failed to fetch data:', error);
  } finally {
    loading.value = false;
  }
};

const findAndSetLesson = (id: number) => {
  if (!course.value) return;
  for (const section of course.value.sections) {
    const lesson = section.lessons.find(l => l.id === id);
    if (lesson) {
      currentLesson.value = lesson;
      return;
    }
  }
};

const isCompleted = (lessonId: number | undefined) => {
  if (!lessonId) return false;
  return enrollment.value?.completed_lessons?.includes(lessonId);
};

const markCompleted = async () => {
  if (!enrollment.value || !currentLesson.value) return;
  marking.value = true;
  try {
    const res = await api.post(`/school/lms/enrollments/${enrollment.value.id}/lessons/${currentLesson.value.id}/complete`);
    enrollment.value = res.data.enrollment; // Assuming API returns updated enrollment
  } catch (error) {
    console.error('Progress update failed:', error);
  } finally {
    marking.value = false;
  }
};

const embedUrl = (url: string) => {
  // Simple YT/Vimeo logic
  if (url.includes('youtube.com/watch?v=')) {
    return url.replace('watch?v=', 'embed/');
  }
  return url;
};

const switchLesson = (id: number) => {
  router.push({ params: { lessonId: id.toString() } });
  if (window.innerWidth < 768) sidebarVisible.value = false;
};

const toggleSidebar = () => sidebarVisible.value = !sidebarVisible.value;
const backToCourse = () => {
    if (course.value) {
        router.push({ name: 'student.lms.course', params: { id: course.value.id.toString() } });
    }
};

const allLessons = computed(() => {
  return course.value?.sections?.flatMap(s => s.lessons) || [];
});

const currentIdx = computed(() => allLessons.value.findIndex(l => l.id === currentLesson.value?.id));
const hasPrev = computed(() => currentIdx.value > 0);
const hasNext = computed(() => currentIdx.value < allLessons.value.length - 1);

const nextLesson = () => {
    const nextIdx = currentIdx.value + 1;
    if (hasNext.value && allLessons.value[nextIdx]) {
        switchLesson(allLessons.value[nextIdx].id);
    }
};
const prevLesson = () => {
    const prevIdx = currentIdx.value - 1;
    if (hasPrev.value && allLessons.value[prevIdx]) {
        switchLesson(allLessons.value[prevIdx].id);
    }
};

const startExam = (examId: number | string) => {
  router.push({ name: 'student.exam.details', params: { id: examId.toString() } });
};

watch(() => route.params.lessonId, (newId) => {
  if (newId) findAndSetLesson(parseInt(newId as string));
});

onMounted(fetchCourse);
</script>
