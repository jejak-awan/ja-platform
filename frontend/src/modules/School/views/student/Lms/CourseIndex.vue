<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">
          Learning Center
        </h1>
        <p class="text-gray-600 mt-2">
          Explore courses and grow your skills
        </p>
      </div>
      <div class="flex gap-3">
        <div class="relative">
          <input 
            v-model="search"
            type="text" 
            placeholder="Search courses..." 
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64"
          >
          <span class="absolute left-3 top-2.5 text-gray-400">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </span>
        </div>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
            />
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-500 font-medium">
            Available Courses
          </p>
          <h3 class="text-2xl font-bold text-gray-900">
            {{ courses.length }}
          </h3>
        </div>
      </div>
      <!-- Add more stats here -->
    </div>

    <!-- Course Grid -->
    <div
      v-if="loading"
      class="grid grid-cols-1 md:grid-cols-3 gap-8"
    >
      <!-- Loading Skeleton -->
      <div
        v-for="i in 3"
        :key="i"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-pulse"
      >
        <div class="h-48 bg-gray-200" />
        <div class="p-6 space-y-4">
          <div class="h-4 bg-gray-200 rounded w-1/4" />
          <div class="h-6 bg-gray-200 rounded w-3/4" />
          <div class="h-10 bg-gray-200 rounded" />
        </div>
      </div>
    </div>

    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-3 gap-8"
    >
      <div 
        v-for="course in filteredCourses" 
        :key="course.id"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group cursor-pointer"
        @click="goToCourse(course.id)"
      >
        <div class="relative h-48 bg-indigo-600">
          <img 
            v-if="course.thumbnail" 
            :src="course.thumbnail" 
            class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-500"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
          <div class="absolute bottom-4 left-4 right-4 flex justify-between items-center text-white">
            <span class="px-2 py-1 bg-white/20 backdrop-blur-md rounded-md text-xs font-semibold uppercase tracking-wider">
              {{ course.level }}
            </span>
            <span class="flex items-center gap-1 text-sm font-medium">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              {{ calculateDuration(course) }}m
            </span>
          </div>
        </div>
        
        <div class="p-6">
          <p class="text-indigo-600 text-xs font-bold uppercase tracking-widest mb-2">
            {{ course.subject?.name || 'General' }}
          </p>
          <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-4 line-clamp-1">
            {{ course.title }}
          </h3>
          
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase">
                {{ course.author?.name?.substring(0, 2) }}
              </div>
              <span class="text-sm text-gray-600 font-medium">{{ course.author?.name }}</span>
            </div>
            <div class="flex -space-x-2">
              <!-- Mock student avatars -->
              <div
                v-for="j in 3"
                :key="j"
                class="w-7 h-7 rounded-full border-2 border-white bg-gray-200"
              />
              <div class="w-7 h-7 rounded-full border-2 border-white bg-indigo-50 flex items-center justify-center text-[10px] text-indigo-600 font-bold">
                +12
              </div>
            </div>
          </div>

          <button 
            class="w-full py-3 bg-gray-50 text-gray-700 font-bold rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-all transform active:scale-95 flex items-center justify-center gap-2"
          >
            Start Learning
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M14 5l7 7m0 0l-7 7m7-7H3"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const courses = ref<any[]>([]);
const loading = ref(true);
const search = ref('');

const fetchCourses = async () => {
  try {
    const response = await axios.get('/api/v1/school/lms/courses');
    courses.value = response.data;
  } catch (error) {
    console.error('Failed to fetch courses:', error);
  } finally {
    loading.value = false;
  }
};

const filteredCourses = computed(() => {
  if (!search.value) return courses.value;
  const s = search.value.toLowerCase();
  return courses.value.filter(c => 
    c.title.toLowerCase().includes(s) || 
    c.description?.toLowerCase().includes(s)
  );
});

const calculateDuration = (_course: any) => {
  // Mock logic or summarize from lessons
  return 45; 
};

const goToCourse = (id: number | string) => {
  router.push({ name: 'student.lms.course', params: { id } });
};

onMounted(fetchCourses);
</script>
