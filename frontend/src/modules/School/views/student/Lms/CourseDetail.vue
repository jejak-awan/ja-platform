<template>
  <div
    v-if="loading"
    class="p-8 flex justify-center items-center min-h-[60vh]"
  >
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600" />
  </div>

  <div
    v-else-if="course"
    class="max-w-7xl mx-auto"
  >
    <!-- Header/Hero Section -->
    <div class="relative bg-gray-900 text-white p-8 md:p-16 overflow-hidden">
      <!-- Background Pattern/Gradient -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600" />
      </div>

      <div class="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-12 items-center">
        <div class="lg:col-span-2 space-y-6">
          <nav class="flex gap-2 text-sm text-indigo-300 font-medium">
            <router-link
              :to="{ name: 'student.lms.index' }"
              class="hover:text-white"
            >
              Courses
            </router-link>
            <span>/</span>
            <span class="text-white">{{ course.subject?.name }}</span>
          </nav>
          
          <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
            {{ course.title }}
          </h1>
          <p class="text-xl text-gray-300 max-w-2xl leading-relaxed">
            {{ course.description }}
          </p>

          <div class="flex flex-wrap gap-6 items-center pt-4">
            <div class="flex items-center gap-2">
              <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center font-bold">
                {{ course.author?.name?.substring(0,2) }}
              </div>
              <div>
                <p class="text-xs text-indigo-300 uppercase font-bold">
                  Instructor
                </p>
                <p class="font-medium">
                  {{ course.author?.name }}
                </p>
              </div>
            </div>
            <div class="h-8 w-px bg-white/20 hidden md:block" />
            <div>
              <p class="text-xs text-indigo-300 uppercase font-bold">
                Level
              </p>
              <p class="font-medium capitalize">
                {{ course.level }}
              </p>
            </div>
            <div class="h-8 w-px bg-white/20 hidden md:block" />
            <div>
              <p class="text-xs text-indigo-300 uppercase font-bold">
                Total Lessons
              </p>
              <p class="font-medium">
                {{ totalLessons }} Lessons
              </p>
            </div>
          </div>
        </div>

        <!-- Sidebar / Action Card -->
        <div class="bg-white text-gray-900 rounded-3xl p-8 shadow-2xl border border-gray-100">
          <div class="aspect-video rounded-2xl bg-gray-100 mb-8 overflow-hidden relative group">
            <img
              v-if="course.thumbnail"
              :src="course.thumbnail"
              class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition-colors">
              <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-8 w-8 text-indigo-600 ml-1"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                ><path d="M8 5v14l11-7z" /></svg>
              </div>
            </div>
          </div>

          <button 
            v-if="!isEnrolled"
            :disabled="enrolling"
            class="w-full py-4 bg-indigo-600 text-white font-extrabold rounded-2xl hover:bg-indigo-700 transform transition-all active:scale-95 shadow-lg shadow-indigo-200 disabled:opacity-50"
            @click="enroll"
          >
            {{ enrolling ? 'Enrolling...' : 'Enroll Now for Free' }}
          </button>
          
          <div
            v-else
            class="space-y-4"
          >
            <div class="flex justify-between items-center text-sm font-bold mb-2">
              <span class="text-indigo-600">Your Progress</span>
              <span>{{ enrollment?.progress || 0 }}%</span>
            </div>
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-indigo-600 rounded-full transition-all duration-1000"
                :style="{ width: (enrollment?.progress || 0) + '%' }"
              />
            </div>
            <button 
              class="w-full py-4 bg-indigo-600 text-white font-extrabold rounded-2xl hover:bg-indigo-700 transform transition-all active:scale-95 shadow-lg shadow-indigo-200"
              @click="startLearning"
            >
              Continue Learning
            </button>
          </div>

          <ul class="mt-8 space-y-4 text-sm font-medium text-gray-600">
            <li class="flex items-center gap-3">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-green-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              /></svg>
              Lifetime access
            </li>
            <li class="flex items-center gap-3">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-green-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              /></svg>
              Standard Certifications
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Curriculum Section -->
    <div class="p-8 md:p-16 bg-gray-50 min-h-screen">
      <div class="max-w-4xl">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8">
          Course Content
        </h2>
        
        <div class="space-y-4">
          <div
            v-for="section in course.sections"
            :key="section.id"
            class="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm"
          >
            <div class="p-6 flex justify-between items-center bg-white border-b border-gray-50">
              <h3 class="text-lg font-extrabold text-gray-900">
                {{ section.title }}
              </h3>
              <span class="text-sm font-bold text-gray-400 capitalize">{{ section.lessons?.length }} lessons</span>
            </div>
            
            <div class="divide-y divide-gray-50">
              <div 
                v-for="lesson in section.lessons" 
                :key="lesson.id"
                class="p-6 flex items-center justify-between hover:bg-indigo-50/50 transition-colors group cursor-pointer"
                @click="startLesson(lesson)"
              >
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:text-indigo-600 transition-colors shadow-sm">
                    <svg
                      v-if="lesson.type === 'video'"
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    ><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                    /><path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    /></svg>
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
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
                    <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                      {{ lesson.title }}
                    </h4>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">
                      {{ lesson.type }} • {{ lesson.duration || 5 }} mins
                    </p>
                  </div>
                </div>
                
                <div
                  v-if="isEnrolled && isCompleted(lesson.id)"
                  class="text-green-500"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <div v-else-if="!isEnrolled && !lesson.is_preview">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-gray-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  ><path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  /></svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const enrolling = ref(false);
const course = ref(null);
const enrollment = ref(null);

const fetchCourse = async () => {
  try {
    const [courseRes, enrollmentRes] = await Promise.all([
      axios.get(`/api/v1/school/lms/courses/${route.params.id}`),
      axios.get(`/api/v1/school/lms/my-enrollment/${route.params.id}`).catch(() => ({ data: null }))
    ]);
    course.value = courseRes.data.data;
    enrollment.value = enrollmentRes?.data?.data || null;
  } catch (error) {
    console.error('Failed to fetch course detail:', error);
  } finally {
    loading.value = false;
  }
};

const totalLessons = computed(() => {
  return course.value?.sections?.reduce((acc, s) => acc + (s.lessons?.length || 0), 0) || 0;
});

const isEnrolled = computed(() => !!enrollment.value);

const isCompleted = (lessonId) => {
  return enrollment.value?.completed_lessons?.includes(lessonId);
};

const enroll = async () => {
  enrolling.value = true;
  try {
    const res = await axios.post(`/api/v1/school/lms/courses/${course.value.id}/enroll`);
    enrollment.value = res.data;
  } catch (error) {
    console.error('Enrollment failed:', error);
  } finally {
    enrolling.value = false;
  }
};

const startLearning = () => {
  // Find first uncompleted lesson
  startLesson();
};

const startLesson = (lesson = null) => {
  if (!isEnrolled.value && lesson && !lesson.is_preview) {
    return enroll();
  }
  
  const lessonId = lesson?.id || findNextLesson()?.id;
  if (lessonId) {
    router.push({ 
      name: 'student.lms.player', 
      params: { 
        courseId: course.value.id,
        lessonId: lessonId 
      } 
    });
  }
};

const findNextLesson = () => {
  for (const section of course.value.sections) {
    for (const lesson of section.lessons) {
      if (!isCompleted(lesson.id)) return lesson;
    }
  }
  return course.value.sections[0]?.lessons[0];
};

onMounted(fetchCourse);
</script>
