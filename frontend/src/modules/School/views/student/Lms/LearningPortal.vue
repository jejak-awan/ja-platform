<template>
  <div class="flex h-screen bg-white dark:bg-slate-950 overflow-hidden">
    <!-- Sidebar -->
    <div class="w-80 border-r border-slate-200 dark:border-slate-800 flex flex-col bg-slate-50/50 dark:bg-slate-900/50">
      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <router-link :to="{ name: 'student-lms-my-courses' }" class="text-xs font-bold text-indigo-600 uppercase tracking-widest flex items-center mb-4">
          <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Kembali ke Kursus
        </router-link>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white line-clamp-2" v-if="course">
          {{ course.title }}
        </h2>
      </div>

      <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
        <div v-for="(lesson, lIdx) in program" :key="lesson.id" class="mb-6">
          <h3 class="px-4 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            Modul {{ lIdx + 1 }}: {{ lesson.title }}
          </h3>
          <div class="space-y-1">
            <button 
              v-for="topic in lesson.topics" 
              :key="topic.id"
              @click="selectTopic(topic)"
              class="w-full flex items-center px-4 py-3 rounded-xl transition-all text-left"
              :class="activeTopic?.id === topic.id 
                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' 
                : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400'"
            >
              <div class="mr-3">
                <svg v-if="isCompleted(topic.id)" class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div v-else class="w-5 h-5 rounded-full border-2 border-current opacity-20"></div>
              </div>
              <span class="text-sm font-medium line-clamp-1">{{ topic.title }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm z-50">
        <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <header class="h-16 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 bg-white dark:bg-slate-950">
        <div class="flex items-center gap-4" v-if="activeTopic">
          <span class="text-sm font-bold text-slate-900 dark:text-white">{{ activeTopic.title }}</span>
          <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-bold uppercase tracking-wider text-slate-500">
            {{ activeTopic.topicable_type.split('\\').pop() }}
          </span>
        </div>
        <div>
          <button 
            v-if="activeTopic && !isCompleted(activeTopic.id)"
            @click="markComplete"
            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition-all shadow-lg shadow-emerald-500/20"
          >
            Tandai Selesai
          </button>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-12 bg-slate-50 dark:bg-slate-900/20 custom-scrollbar">
        <div v-if="!activeTopic" class="h-full flex flex-col items-center justify-center text-center opacity-40">
          <svg class="w-24 h-24 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
          <h2 class="text-2xl font-bold">Pilih materi untuk memulai belajar</h2>
        </div>

        <div v-else class="max-w-4xl mx-auto">
          <!-- Render Content based on type -->
          <div v-if="activeTopic.topicable_type.includes('RichText')" class="prose prose-slate dark:prose-invert max-w-none bg-white dark:bg-slate-900 p-10 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-html="activeTopic.topicable.value"></div>
          </div>

          <div v-else-if="activeTopic.topicable_type.includes('Video')" class="aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl">
            <iframe 
              v-if="activeTopic.topicable.value.includes('youtube')"
              class="w-full h-full"
              :src="getYoutubeEmbedUrl(activeTopic.topicable.value)"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
            <video v-else controls class="w-full h-full">
              <source :src="activeTopic.topicable.value" type="video/mp4">
            </video>
          </div>

          <div v-else-if="activeTopic.topicable_type.includes('Quiz')">
            <QuizRenderer 
              :quiz="activeTopic.topicable" 
              @completed="fetchData"
            />
          </div>

          <div v-else class="p-10 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 text-center">
            <p>Konten tipe ini belum didukung penampilannya.</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LmsService from '../../../services/LmsService';
import QuizRenderer from '../../../components/lms/QuizRenderer.vue';

const route = useRoute();
const courseId = String(route.params.id);

const course = ref<any>(null);
const program = ref<any[]>([]);
const progress = ref<any[]>([]);
const activeTopic = ref<any>(null);
const loading = ref(false);

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await LmsService.getLearningData(courseId);
    program.value = res.data.program;
    progress.value = res.data.progress;
    // Set first topic as active by default if not set
    if (!activeTopic.value && program.value.length > 0 && program.value[0].topics.length > 0) {
      activeTopic.value = program.value[0].topics[0];
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const selectTopic = (topic: any) => {
  activeTopic.value = topic;
};

const isCompleted = (topicId: number) => {
  return progress.value.some(p => p.topic_id === topicId && p.is_completed);
};

const markComplete = async () => {
  if (!activeTopic.value) return;
  try {
    await LmsService.completeTopic(activeTopic.value.id);
    await fetchData(); // Refresh progress
  } catch (err) {
    console.error(err);
  }
};

const getYoutubeEmbedUrl = (url: string) => {
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  return (match && match[2] && match[2].length == 11) ? `https://www.youtube.com/embed/${match[2]}` : url;
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.1);
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.3);
}
</style>
