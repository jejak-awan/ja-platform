<template>
  <div class="p-6 max-w-7xl mx-auto">
    <div class="mb-8 flex justify-between items-end">
      <div>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Katalog Kursus</h1>
        <p class="text-slate-500 dark:text-slate-400 text-lg">Tingkatkan keahlianmu dengan materi pembelajaran terbaik.</p>
      </div>
      <div class="flex gap-4">
        <!-- Search & Filter placeholders -->
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="h-80 bg-slate-100 dark:bg-slate-800 animate-pulse rounded-2xl"></div>
    </div>

    <div v-else-if="catalog.length === 0" class="text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
      <div class="text-slate-300 dark:text-slate-700 mb-4">
        <svg class="w-20 h-20 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </div>
      <h3 class="text-xl font-medium text-slate-900 dark:text-white">Belum ada kursus tersedia</h3>
      <p class="text-slate-500 mt-2">Silakan cek kembali nanti untuk materi pembelajaran baru.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div 
        v-for="course in catalog" 
        :key="course.id"
        class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
      >
        <div class="relative h-48 bg-slate-200 dark:bg-slate-800 overflow-hidden">
          <img 
            v-if="course.image_path" 
            :src="course.image_path" 
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
          >
          <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
            <span class="text-4xl font-bold opacity-20">{{ course.title.charAt(0) }}</span>
          </div>
          <div class="absolute top-4 left-4">
            <span class="px-3 py-1 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-full text-xs font-bold uppercase tracking-wider text-indigo-600">
              {{ course.level }}
            </span>
          </div>
        </div>
        
        <div class="p-6">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 line-clamp-1 group-hover:text-indigo-600 transition-colors">
            {{ course.title }}
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 line-clamp-2 leading-relaxed">
            {{ course.summary }}
          </p>
          
          <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center text-slate-500 text-sm font-medium">
              <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              {{ course.lessons_count || 0 }} Modul
            </div>
            <router-link 
              :to="{ name: 'student-lms-learn', params: { id: course.id }}"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-95"
            >
              Mulai Belajar
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useLmsStore } from '../../../stores/lms';

const lmsStore = useLmsStore();
const { catalog, loading } = storeToRefs(lmsStore);

onMounted(() => {
  lmsStore.fetchCatalog();
});
</script>
