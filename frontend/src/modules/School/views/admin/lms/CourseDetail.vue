<template>
  <div class="p-6 max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="icon" as-child>
          <router-link :to="{ name: 'admin-lms-courses' }">
            <ChevronLeft class="w-5 h-5" />
          </router-link>
        </Button>
        <div v-if="course">
          <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ course.title }}</h1>
          <p class="text-muted-foreground text-sm">Editor Kurikulum & Materi</p>
        </div>
      </div>
      <div class="flex gap-3">
        <Button @click="showAddLessonModal = true" class="font-bold">
          <Plus class="w-4 h-4 mr-2" />
          Tambah Modul
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-6">
      <SkeletonLoader v-for="i in 3" :key="i" class="h-32 rounded-3xl" />
    </div>

    <!-- Course Content -->
    <div v-else-if="course" class="space-y-6">
      <Card v-for="(lesson, lIdx) in course.lessons" :key="lesson.id" class="overflow-hidden border-border/50 shadow-sm transition-all hover:shadow-md">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 bg-muted/30 py-4 px-6">
          <div class="flex items-center gap-4">
            <div class="w-8 h-8 bg-primary text-primary-foreground rounded-lg flex items-center justify-center font-bold text-sm">
              {{ Number(lIdx) + 1 }}
            </div>
            <CardTitle class="text-base font-bold">{{ lesson.title }}</CardTitle>
          </div>
          <Button variant="ghost" size="sm" @click="openAddTopicModal(lesson.id)" class="text-primary font-bold uppercase tracking-widest text-[10px]">
            <Plus class="w-3 h-3 mr-1" />
            Tambah Materi
          </Button>
        </CardHeader>
        
        <CardContent class="p-4 space-y-2">
          <div v-if="lesson.topics.length === 0" class="py-10 text-center text-muted-foreground text-sm italic">
            Belum ada materi di modul ini.
          </div>
          <div 
            v-for="topic in lesson.topics" 
            :key="topic.id"
            class="flex items-center justify-between p-4 bg-muted/20 rounded-2xl border border-transparent hover:border-primary/30 transition-all group"
          >
            <div class="flex items-center gap-4">
              <div class="w-8 h-8 bg-background rounded-lg flex items-center justify-center border border-border shadow-sm">
                <FileText v-if="topic.topicable_type.includes('RichText')" class="w-4 h-4 text-orange-500" />
                <PlayCircle v-else-if="topic.topicable_type.includes('Video')" class="w-4 h-4 text-red-500" />
                <FileCheck v-else-if="topic.topicable_type.includes('Quiz')" class="w-4 h-4 text-emerald-500" />
                <FileDown v-else class="w-4 h-4 text-blue-500" />
              </div>
              <div>
                <div class="text-sm font-bold text-foreground">{{ topic.title }}</div>
                <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">
                  {{ topic.topicable_type.split('\\').pop() }}
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-primary">
                <Pencil class="w-4 h-4" />
              </Button>
              <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-destructive">
                <Trash2 class="w-4 h-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Modals -->
    <!-- Add Lesson Modal -->
    <Dialog v-model:open="showAddLessonModal">
      <DialogContent class="sm:max-w-md rounded-3xl">
        <DialogHeader>
          <DialogTitle class="text-2xl font-bold">Tambah Modul Baru</DialogTitle>
        </DialogHeader>
        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">Judul Modul</label>
            <Input v-model="lessonForm.title" placeholder="Contoh: Pengenalan Dasar" />
          </div>
        </div>
        <DialogFooter class="gap-2 sm:gap-0">
          <Button variant="outline" @click="showAddLessonModal = false" class="flex-1 rounded-xl font-bold">Batal</Button>
          <Button @click="createLesson" :disabled="submitting" class="flex-1 rounded-xl font-bold">
            <Spinner v-if="submitting" class="mr-2 h-4 w-4" />
            Simpan Modul
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Add Topic Modal -->
    <Dialog v-model:open="showAddTopicModal">
      <DialogContent class="sm:max-w-2xl rounded-3xl overflow-hidden max-h-[90vh] flex flex-col p-0">
        <DialogHeader class="p-8 pb-4">
          <DialogTitle class="text-2xl font-bold">Tambah Materi</DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-y-auto p-8 pt-0 space-y-6">
          <div class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">Judul Materi</label>
            <Input v-model="topicForm.title" placeholder="Contoh: Apa itu HTML?" />
          </div>

          <div class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">Tipe Materi</label>
            <div class="grid grid-cols-4 gap-2">
              <Button 
                v-for="type in ['richtext', 'video', 'pdf', 'quiz']" 
                :key="type"
                type="button"
                variant="outline"
                @click="topicForm.type = type"
                class="uppercase text-[10px] tracking-widest font-bold h-12"
                :class="{ 'bg-primary text-primary-foreground border-primary hover:bg-primary/90 hover:text-primary-foreground': topicForm.type === type }"
              >
                {{ type }}
              </Button>
            </div>
          </div>
          
          <div v-if="topicForm.type === 'richtext'" class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">Konten Materi</label>
            <TiptapEditor v-model="topicForm.content.value" placeholder="Tulis materi di sini..." />
          </div>

          <div v-if="topicForm.type === 'video'" class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">URL Video (YouTube/Direct)</label>
            <Input v-model="topicForm.content.value" placeholder="https://youtube.com/..." />
          </div>

          <div v-if="topicForm.type === 'pdf'" class="space-y-2">
            <label class="text-xs font-bold text-muted-foreground uppercase">URL File PDF</label>
            <Input v-model="topicForm.content.value" placeholder="https://link-ke-file.pdf" />
          </div>

          <div v-if="topicForm.type === 'quiz'" class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Skor Kelulusan (0-100)</label>
              <Input v-model.number="topicForm.content.pass_score" type="number" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold text-muted-foreground uppercase">Batas Waktu (Menit)</label>
              <Input v-model.number="topicForm.content.time_limit" type="number" />
            </div>
          </div>
        </div>

        <DialogFooter class="p-8 pt-4 border-t bg-muted/20 gap-2 sm:gap-0">
          <Button variant="outline" @click="showAddTopicModal = false" class="flex-1 rounded-xl font-bold">Batal</Button>
          <Button @click="createTopic" :disabled="submitting" class="flex-1 rounded-xl font-bold">
            <Spinner v-if="submitting" class="mr-2 h-4 w-4" />
            Simpan Materi
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import LmsService from '../../../services/LmsService';
import { Button } from '@/shared/components/ui';
import { Input } from '@/shared/components/ui';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/shared/components/ui';
import { Card, CardHeader, CardTitle, CardContent } from '@/shared/components/ui';
import { Spinner, SkeletonLoader } from '@/shared/components/ui';
import { ChevronLeft, Plus, Pencil, Trash2, FileText, PlayCircle, FileDown, FileCheck } from 'lucide-vue-next';
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';

const route = useRoute();
const courseId = route.params.id;

const course = ref<any>(null);
const loading = ref(false);
const submitting = ref(false);

const showAddLessonModal = ref(false);
const showAddTopicModal = ref(false);
const activeLessonId = ref<number | null>(null);

const lessonForm = ref({ title: '', order: 0 });
const topicForm = ref({
  title: '',
  type: 'richtext',
  content: { 
    value: '',
    pass_score: 70,
    time_limit: 0
  },
  order: 0
});

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await LmsService.getCourseDetails(courseId);
    course.value = res.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const createLesson = async () => {
  if (!lessonForm.value.title.trim()) {
    alert('Judul modul wajib diisi');
    return;
  }
  submitting.value = true;
  try {
    await LmsService.createLesson(courseId, lessonForm.value);
    await fetchData();
    showAddLessonModal.value = false;
    lessonForm.value = { title: '', order: 0 };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const openAddTopicModal = (lessonId: number) => {
  activeLessonId.value = lessonId;
  showAddTopicModal.value = true;
};

const createTopic = async () => {
  if (!activeLessonId.value) return;
  if (!topicForm.value.title.trim()) {
    alert('Judul materi wajib diisi');
    return;
  }
  if (topicForm.value.type !== 'quiz' && !topicForm.value.content.value.trim()) {
    alert('Konten/URL materi wajib diisi');
    return;
  }
  
  submitting.value = true;
  try {
    await LmsService.createTopic(activeLessonId.value, topicForm.value);
    await fetchData();
    showAddTopicModal.value = false;
    topicForm.value = { 
      title: '', 
      type: 'richtext', 
      content: { value: '', pass_score: 70, time_limit: 0 }, 
      order: 0 
    };
  } catch (err) {
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
