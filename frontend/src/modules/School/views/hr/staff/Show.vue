<template>
  <div class="p-6 space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
    <!-- Header with Breadcrumbs & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div class="space-y-1">
        <BreadcrumbTrail class="mb-2" />
        <h1 class="text-3xl font-extrabold tracking-tight text-foreground">{{ staff.full_name }}</h1>
      </div>

      <div class="flex gap-2">
        <Button
          variant="outline"
          size="lg"
          class="border-border/50 hover:bg-accent/50 transition-colors rounded-xl h-11"
          @click="$router.push({ name: 'staff.edit', params: { id: staff.id } })"
        >
          <LucideIcon name="Pencil" class="w-4 h-4 mr-2" />
          Edit Profil
        </Button>
        <Button
          variant="secondary"
          size="lg"
          class="bg-muted/50 hover:bg-muted/80 transition-colors rounded-xl h-11"
          @click="$router.push({ name: 'staff.index' })"
        >
          <LucideIcon name="ArrowLeft" class="w-4 h-4 mr-2" />
          Kembali
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Profile Summary Card -->
      <div class="lg:col-span-1 space-y-6">
        <Card class="overflow-hidden border-border/50 bg-background/50 backdrop-blur-sm shadow-sm rounded-2xl sticky top-24">
          <div class="h-32 bg-gradient-to-br from-primary/20 via-accent/10 to-background border-b border-border/50 relative">
             <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                <div class="w-24 h-24 rounded-2xl bg-background p-1 shadow-md border border-border/50">
                  <div class="w-full h-full rounded-xl bg-muted flex items-center justify-center overflow-hidden">
                    <LucideIcon v-if="!staff.photo" name="User" class="w-12 h-12 text-muted-foreground/30" />
                    <img v-else :src="staff.photo" class="w-full h-full object-cover" />
                  </div>
                </div>
             </div>
          </div>
          
          <CardContent class="pt-16 pb-8 text-center space-y-4">
            <div>
              <h2 class="text-xl font-bold text-foreground">{{ staff.full_name }}</h2>
              <Badge variant="outline" class="mt-2 bg-primary/5 text-primary border-primary/20">
                {{ staff.ptk_type || 'Staff Member' }}
              </Badge>
            </div>

            <div class="grid grid-cols-1 gap-3 pt-4 border-t border-border/30">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground flex items-center"><LucideIcon name="Fingerprint" class="w-4 h-4 mr-2" /> NUPTK</span>
                <span class="font-mono text-xs font-bold text-foreground">{{ staff.nuptk || '-' }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground flex items-center"><LucideIcon name="Briefcase" class="w-4 h-4 mr-2" /> Status</span>
                <span class="text-xs font-bold text-foreground">{{ staff.employment_status || '-' }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Right Column: Detailed Tabs -->
      <div class="lg:col-span-2 space-y-6">
        <Tabs v-model="activeTab" class="w-full">
          <TabsList class="bg-muted/30 p-1 rounded-xl border border-border/50 w-full justify-start overflow-x-auto h-auto gap-1">
            <TabsTrigger value="profile" class="rounded-lg px-6 py-3 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm font-bold">
              <LucideIcon name="User" class="w-4 h-4 mr-2" /> {{ $t('modules.school.hr.staff.tabs.profile') || 'Profil' }}
            </TabsTrigger>
            <TabsTrigger value="employment" class="rounded-lg px-6 py-3 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm font-bold">
              <LucideIcon name="History" class="w-4 h-4 mr-2" /> {{ $t('modules.school.hr.staff.tabs.employment') || 'Kepegawaian' }}
            </TabsTrigger>
            <TabsTrigger value="education" class="rounded-lg px-6 py-3 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm font-bold">
              <LucideIcon name="GraduationCap" class="w-4 h-4 mr-2" /> {{ $t('modules.school.hr.staff.tabs.education') || 'Pendidikan' }}
            </TabsTrigger>
          </TabsList>

          <div class="mt-6">
            <!-- Profile Tab Content -->
            <TabsContent value="profile" class="space-y-6 animate-in slide-in-from-right-2 duration-300">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info Card -->
                <Card class="border-border/50 bg-background/50 shadow-sm rounded-2xl overflow-hidden">
                  <CardHeader class="border-b border-border/30 bg-muted/20 py-4">
                    <CardTitle class="text-sm font-bold flex items-center">
                      <LucideIcon name="UserCircle" class="w-4 h-4 mr-2 text-primary" />
                      Informasi Pribadi
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="p-6 space-y-4">
                    <DetailItem label="NIK" :value="staff.nik" icon="CreditCard" />
                    <DetailItem label="Tempat Lahir" :value="staff.place_of_birth" icon="MapPin" />
                    <DetailItem label="Tanggal Lahir" :value="staff.date_of_birth" icon="Calendar" />
                    <DetailItem label="Jenis Kelamin" :value="staff.gender === 'L' ? 'Laki-laki' : 'Perempuan'" :icon="staff.gender === 'L' ? 'User' : 'User'" />
                    <DetailItem label="Agama" :value="staff.religion" icon="Globe" />
                  </CardContent>
                </Card>

                <!-- Contact Info Card -->
                <Card class="border-border/50 bg-background/50 shadow-sm rounded-2xl overflow-hidden">
                   <CardHeader class="border-b border-border/30 bg-muted/20 py-4">
                    <CardTitle class="text-sm font-bold flex items-center">
                      <LucideIcon name="Mail" class="w-4 h-4 mr-2 text-primary" />
                      Kontak & Alamat
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="p-6 space-y-4">
                    <DetailItem label="Email" :value="staff.user?.email" icon="AtSign" />
                    <DetailItem label="No. HP" :value="staff.phone" icon="Phone" />
                    <div class="space-y-1.5 pt-2">
                       <span class="text-[10px] text-muted-foreground uppercase tracking-widest font-black">Alamat Rumah</span>
                       <p class="text-sm leading-relaxed text-foreground/80">{{ staff.address || 'Belum ada data alamat.' }}</p>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>

            <!-- Employment Tab Content -->
            <TabsContent value="employment" class="space-y-6 animate-in slide-in-from-right-2 duration-300">
               <Card class="border-border/50 bg-background/50 shadow-sm rounded-2xl overflow-hidden">
                  <CardHeader class="border-b border-border/30 bg-muted/20 py-4">
                    <CardTitle class="text-sm font-bold flex items-center">
                      <LucideIcon name="Briefcase" class="w-4 h-4 mr-2 text-primary" />
                      Data Kepegawaian & Penugasan
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                      <DetailItem label="Status Kepegawaian" :value="staff.employment_status" icon="Award" />
                      <DetailItem label="Tipe PTK" :value="staff.ptk_type" icon="UserCog" />
                      <DetailItem label="SK Pengangkatan" :value="staff.sk_pengangkatan" icon="FileText" />
                      <DetailItem label="TMT Pengangkatan" :value="staff.tmt_pengangkatan" icon="CalendarClock" />
                      <DetailItem label="SK Penugasan" :value="staff.sk_penugasan" icon="FileBadge" />
                      <DetailItem label="TMT Penugasan" :value="staff.tmt_penugasan" icon="CalendarSearch" />
                      <DetailItem label="Status Sertifikasi" :value="staff.certification_status ? 'Sudah Sertifikasi' : 'Belum Sertifikasi'" icon="CircleCheck2" />
                    </div>
                  </CardContent>
               </Card>
            </TabsContent>

            <!-- Education Tab Content -->
            <TabsContent value="education" class="space-y-6 animate-in slide-in-from-right-2 duration-300">
               <Card class="border-border/50 bg-background/50 shadow-sm rounded-2xl overflow-hidden">
                  <CardHeader class="border-b border-border/30 bg-muted/20 py-4">
                    <CardTitle class="text-sm font-bold flex items-center">
                      <LucideIcon name="GraduationCap" class="w-4 h-4 mr-2 text-primary" />
                      Riwayat Pendidikan Terakhir
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="p-6 space-y-6">
                    <div class="flex items-start gap-4">
                       <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 border border-primary/20">
                          <LucideIcon name="Building2" class="w-6 h-6" />
                       </div>
                       <div class="space-y-1">
                          <h4 class="font-bold text-lg leading-none text-foreground">{{ staff.last_education || 'Tidak Terdata' }}</h4>
                          <p class="text-primary font-medium tracking-tight">{{ staff.major || 'Semua Jurusan' }}</p>
                       </div>
                    </div>
                    
                    <div class="p-4 bg-accent/30 rounded-xl border border-border/50">
                       <p class="text-xs text-muted-foreground leading-relaxed italic">
                          "Informasi ini berdasarkan data Dapodik/EMIS terakhir yang disinkronkan ke sistem."
                       </p>
                    </div>
                  </CardContent>
               </Card>
            </TabsContent>
          </div>
        </Tabs>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import { useRoute } from 'vue-router';
import { HRService } from '@/modules/School/services/HRService';
import { parseResponse } from '@/shared/utils/responseParser';
import { 
  Tabs, TabsList, TabsTrigger, TabsContent, 
  Card, CardHeader, CardTitle, CardContent, 
  Button, LucideIcon, Badge
} from '@/shared/components/ui';
import BreadcrumbTrail from '@/shared/components/BreadcrumbTrail.vue';

const route = useRoute();
const staff = ref<any>({});
const activeTab = ref('profile');
const loading = ref(false);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await HRService.getStaffDetail(String(route.params.id));
    staff.value = parseResponse(response).data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

// Internal sub-component for detail items
const DetailItem = (props: { label: string, value: any, icon: string }) => {
  return h('div', { class: 'space-y-1.5' }, [
    h('span', { class: 'text-[10px] text-muted-foreground uppercase tracking-[0.1em] font-black' }, props.label),
    h('div', { class: 'flex items-center gap-2 group' }, [
       h(LucideIcon, { name: props.icon, class: 'w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors' }),
       h('span', { class: 'font-semibold text-sm text-foreground truncate' }, props.value || '-')
    ])
  ]);
};

onMounted(fetchData);
</script>

<style scoped>
/* Removed redundant styles */
</style>
