<template>
  <div class="p-6 min-h-screen">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-start">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">
          {{ $t('features.school.cbt.management.title') }}
        </h1>
        <p class="text-muted-foreground mt-1">
          {{ $t('features.school.cbt.management.subtitle') }}
        </p>
      </div>
      <Button
        class="gap-2 px-6 py-6 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:scale-[1.02]"
        @click="showCreateDialog = true"
      >
        <LucideIcon
          name="Plus"
          class="h-5 w-5"
        />
        {{ $t('features.school.cbt.management.createSession') }}
      </Button>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <Card
        v-for="(val, key) in stats"
        :key="key"
        class="border-none bg-background/50 backdrop-blur shadow-sm"
      >
        <CardHeader class="pb-2">
          <CardDescription class="text-xs font-bold text-muted-foreground tracking-wider">
            {{ $t(`features.school.cbt.management.stats.${key}`) }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="text-3xl font-bold">
            {{ val }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Sessions List -->
    <Card class="border-none shadow-xl shadow-foreground/5 bg-background/50 backdrop-blur overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="hover:bg-transparent border-none bg-muted/30">
              <TableHead class="pl-6 h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.sessionDetails') }}
              </TableHead>
              <TableHead class="h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.studyGroup') }}
              </TableHead>
              <TableHead class="h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.startTime') }}
              </TableHead>
              <TableHead class="h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.token') }}
              </TableHead>
              <TableHead class="h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.status') }}
              </TableHead>
              <TableHead class="text-right pr-6 h-12 uppercase text-[10px] font-bold tracking-widest text-muted-foreground">
                {{ $t('features.school.cbt.management.labels.actions') }}
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="session in sessions"
              :key="session.id"
              class="group transition-colors border-muted/20"
            >
              <TableCell class="pl-6 py-5">
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/5">
                    <LucideIcon
                      name="Monitor"
                      class="w-5 h-5"
                    />
                  </div>
                  <div>
                    <div class="font-bold text-base transition-colors">
                      {{ session.exam?.title }}
                    </div>
                    <div class="text-xs text-muted-foreground font-medium">
                      {{ session.exam?.subject?.name }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="font-bold text-sm">
                {{ session.study_group?.name }}
              </TableCell>
              <TableCell class="text-muted-foreground text-sm font-medium">
                {{ formatDateTime(session.start_time) }}
              </TableCell>
              <TableCell>
                <div
                  v-if="session.token"
                  class="inline-flex items-center gap-2 px-3 py-1 bg-primary/5 text-primary rounded-lg border border-primary/10 font-mono font-bold text-sm"
                >
                  {{ session.token }}
                </div>
                <Button
                  v-else
                  variant="ghost"
                  size="sm"
                  class="h-8 text-[10px] font-bold border border-dashed border-primary/30 text-primary hover:bg-primary/5 rounded-lg"
                  @click="generateToken(session.id)"
                >
                  {{ $t('features.school.cbt.management.labels.generateToken') }}
                </Button>
              </TableCell>
              <TableCell>
                <Badge
                  :variant="session.is_active ? 'default' : 'secondary'"
                  class="rounded-lg px-2.5 py-0.5 text-[10px] font-bold"
                >
                  {{ session.is_active ? $t('features.school.cbt.management.labels.active') : $t('features.school.cbt.management.labels.inactive') }}
                </Badge>
              </TableCell>
              <TableCell class="text-right pr-6">
                <div class="flex justify-end gap-1">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 text-muted-foreground hover:bg-primary/5 hover:text-primary rounded-xl"
                  >
                    <LucideIcon
                      name="ScanEye"
                      class="h-4.5 w-4.5"
                    />
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 text-muted-foreground hover:bg-destructive/5 hover:text-destructive rounded-xl"
                    @click="deleteSession(session.id)"
                  >
                    <LucideIcon
                      name="Trash2"
                      class="h-4.5 w-4.5"
                    />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
        
        <div
          v-if="!sessions.length"
          class="p-20 text-center"
        >
          <div class="bg-muted/30 w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <LucideIcon
              name="Ghost"
              class="h-8 w-8 text-muted-foreground/50"
            />
          </div>
          <h3 class="text-lg font-bold">
            {{ $t('features.school.cbt.management.messages.noSessions') }}
          </h3>
          <p class="text-sm text-muted-foreground mt-1">
            Ready to start an exam event? Create your first session.
          </p>
        </div>
      </div>
    </Card>

    <!-- Create Session Dialog -->
    <Dialog
      :open="showCreateDialog"
      @update:open="showCreateDialog = $event"
    >
      <DialogContent class="sm:max-w-[500px] border-none shadow-2xl rounded-3xl p-0 overflow-hidden">
        <DialogHeader class="p-6 bg-muted/30 border-b">
          <DialogTitle class="text-xl font-bold">
            {{ $t('features.school.cbt.management.createSession') }}
          </DialogTitle>
          <DialogDescription>
            Schedule a formal exam event for a specific study group.
          </DialogDescription>
        </DialogHeader>
        
        <div class="p-8 space-y-6">
          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $t('features.school.cbt.management.labels.selectExam') }}</Label>
            <Select v-model="newSession.exam_id">
              <SelectTrigger class="h-12 bg-muted/20 border-muted rounded-xl px-4">
                <SelectValue placeholder="Select an exam..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="exam in availableExams"
                  :key="exam.id"
                  :value="exam.id.toString()"
                >
                  {{ exam.title }} ({{ exam.category?.toUpperCase() }})
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $t('features.school.cbt.management.labels.selectGroup') }}</Label>
            <Select v-model="newSession.study_group_id">
              <SelectTrigger class="h-12 bg-muted/20 border-muted rounded-xl px-4">
                <SelectValue placeholder="Select a class..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="group in studyGroups"
                  :key="group.id"
                  :value="group.id.toString()"
                >
                  {{ group.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $t('features.school.cbt.management.labels.startTime') }}</Label>
              <Input
                v-model="newSession.start_time"
                type="datetime-local"
                class="h-12 bg-muted/20 border-muted rounded-xl"
              />
            </div>
            <div class="space-y-2">
              <Label class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $t('features.school.cbt.management.labels.endTime') }}</Label>
              <Input
                v-model="newSession.end_time"
                type="datetime-local"
                class="h-12 bg-muted/20 border-muted rounded-xl"
              />
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 bg-muted/10 border-t flex gap-2">
          <Button
            variant="ghost"
            class="rounded-xl font-bold"
            @click="showCreateDialog = false"
          >
            Cancel
          </Button>
          <Button
            :disabled="submitting"
            class="rounded-xl font-bold px-8"
            @click="createSession"
          >
            <LucideIcon
              v-if="submitting"
              name="Loader2"
              class="mr-2 h-4 w-4 animate-spin"
            />
            Create Session
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { CbtService } from '@/modules/School/services/CbtService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';
import dayjs from 'dayjs';
import { 
  Button, Card, CardContent, CardDescription, CardHeader, 
  Badge, Input, Label, Table, TableBody, TableCell, TableHead, TableHeader, TableRow, 
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
  LucideIcon 
} from '@/components/ui';

const sessions = ref<any[]>([]);
const availableExams = ref<any[]>([]);
const studyGroups = ref<any[]>([]);
const loading = ref(true);
const submitting = ref(false);
const showCreateDialog = ref(false);

const newSession = ref({
  exam_id: '',
  study_group_id: '',
  start_time: '',
  end_time: '',
  is_active: true
});

const stats = computed(() => ({
  totalExams: availableExams.value.length,
  activeSessions: sessions.value.filter(s => s.is_active).length,
  totalParticipants: sessions.value.length * 30, // Mock calculation
  completedExams: sessions.value.filter(s => dayjs().isAfter(s.end_time)).length
}));

const fetchData = async () => {
  loading.value = true;
  try {
    const [sessionsRes, examsRes, groupsRes] = await Promise.all([
      CbtService.getSessions(),
      CbtService.getExams(),
      AcademicService.getStudyGroups()
    ]);
    
    sessions.value = (parseResponse(sessionsRes).data as any[]) || [];
    availableExams.value = (parseResponse(examsRes).data as any[]) || [];
    studyGroups.value = (parseResponse(groupsRes).data as any[]) || [];
  } catch (error) {
    console.error('Failed to fetch CBT data:', error);
  } finally {
    loading.value = false;
  }
};

const createSession = async () => {
  submitting.value = true;
  try {
    const data = {
      ...newSession.value,
      exam_id: parseInt(newSession.value.exam_id),
      study_group_id: parseInt(newSession.value.study_group_id)
    };
    const res = await CbtService.storeSession(data as any);
    const responseData = parseResponse(res).data;
    if (responseData) {
      showCreateDialog.value = false;
      newSession.value = { exam_id: '', study_group_id: '', start_time: '', end_time: '', is_active: true };
      fetchData();
    }
  } catch (error) {
    console.error('Failed to create session:', error);
  } finally {
    submitting.value = false;
  }
};

const generateToken = async (id: number) => {
  try {
    await CbtService.generateToken(id);
    fetchData();
  } catch (error) {
    console.error('Failed to generate token:', error);
  }
};

const deleteSession = async (id: number) => {
  if (!confirm('Are you sure you want to delete this session?')) return;
  try {
    await CbtService.deleteSession(id);
    fetchData();
  } catch (error) {
    console.error('Failed to delete session:', error);
  }
};

const formatDateTime = (date: string) => dayjs(date).format('DD MMM, HH:mm');

onMounted(fetchData);
</script>
