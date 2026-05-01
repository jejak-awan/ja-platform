<template>
  <Card class="border-border/50 bg-background/50 backdrop-blur-sm shadow-sm rounded-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-500">
    <form @submit.prevent="handleSubmit">
      <CardContent class="p-0">
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="p-6 border-b border-border/30 bg-muted/20 flex justify-start h-auto gap-4 overflow-x-auto scrollbar-hide">
            <TabsTrigger value="biography" class="rounded-lg px-6 py-2 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm">
              <LucideIcon name="User" class="w-4 h-4 mr-2" /> Biografi
            </TabsTrigger>
            <TabsTrigger value="employment" class="rounded-lg px-6 py-2 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm">
              <LucideIcon name="Briefcase" class="w-4 h-4 mr-2" /> Kepegawaian
            </TabsTrigger>
            <TabsTrigger value="education" class="rounded-lg px-6 py-2 transition-all data-[state=active]:bg-background data-[state=active]:shadow-sm">
              <LucideIcon name="GraduationCap" class="w-4 h-4 mr-2" /> Pendidikan
            </TabsTrigger>
          </TabsList>

          <!-- Tab Biografi -->
          <TabsContent
            value="biography"
            class="p-8 space-y-8 animate-in slide-in-from-right-2 duration-300"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="md:col-span-2 space-y-3">
                <Label for="full_name" class="text-sm font-bold flex items-center gap-1">
                  Nama Lengkap <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="full_name"
                  v-model="form.full_name"
                  placeholder="Nama Lengkap & Gelar (Tanpa Singkatan)"
                  class="h-12 bg-background/50 border-border/50 focus:ring-primary/20 transition-all rounded-xl"
                  required
                />
              </div>

              <div class="space-y-3">
                <Label for="nik" class="text-sm font-bold flex items-center gap-1">
                  NIK <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="nik"
                  v-model="form.nik"
                  maxlength="16"
                  placeholder="16 Digit Nomor Induk Kependudukan"
                  class="h-11 bg-background/50 border-border/50 focus:ring-primary/20 transition-all rounded-xl font-mono text-xs"
                  required
                />
              </div>

              <div class="space-y-3">
                <Label for="nuptk" class="text-sm font-bold">NUPTK</Label>
                <Input
                  id="nuptk"
                  v-model="form.nuptk"
                  maxlength="16"
                  placeholder="16 Digit NUPTK (Jika ada)"
                  class="h-11 bg-background/50 border-border/50 focus:ring-primary/20 transition-all rounded-xl font-mono text-xs"
                />
              </div>

              <div class="space-y-3">
                <Label for="gender" class="text-sm font-bold flex items-center gap-1">
                  Jenis Kelamin <span class="text-destructive">*</span>
                </Label>
                <Select v-model="form.gender">
                  <SelectTrigger class="h-11 bg-background/50 border-border/50 rounded-xl">
                    <SelectValue placeholder="Pilih Jenis Kelamin" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="L">Laki-laki</SelectItem>
                    <SelectItem value="P">Perempuan</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-3">
                <Label for="religion" class="text-sm font-bold">Agama</Label>
                <Select v-model="form.religion">
                  <SelectTrigger class="h-11 bg-background/50 border-border/50 rounded-xl">
                    <SelectValue placeholder="Pilih Agama" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Islam">Islam</SelectItem>
                    <SelectItem value="Kristen">Kristen</SelectItem>
                    <SelectItem value="Katolik">Katolik</SelectItem>
                    <SelectItem value="Hindu">Hindu</SelectItem>
                    <SelectItem value="Budha">Budha</SelectItem>
                    <SelectItem value="Konghucu">Konghucu</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-3">
                <Label for="place_of_birth" class="text-sm font-bold">Tempat Lahir</Label>
                <Input
                  id="place_of_birth"
                  v-model="form.place_of_birth"
                  placeholder="Kota / Kabupaten"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="space-y-3">
                <Label for="date_of_birth" class="text-sm font-bold">Tanggal Lahir</Label>
                <Input
                  id="date_of_birth"
                  v-model="form.date_of_birth"
                  type="date"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>
            </div>
          </TabsContent>

          <!-- Tab Kepegawaian -->
          <TabsContent
            value="employment"
            class="p-8 space-y-8 animate-in slide-in-from-right-2 duration-300"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-3">
                <Label for="ptk_type" class="text-sm font-bold">Jenis PTK</Label>
                <Select v-model="form.ptk_type">
                  <SelectTrigger class="h-11 bg-background/50 border-border/50 rounded-xl">
                    <SelectValue placeholder="Pilih Jenis PTK" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Guru Mapel">Guru Mapel</SelectItem>
                    <SelectItem value="Guru Kelas">Guru Kelas</SelectItem>
                    <SelectItem value="Guru BK">Guru BK</SelectItem>
                    <SelectItem value="Tendik">Tenaga Kependidikan</SelectItem>
                    <SelectItem value="Kepala Sekolah">Kepala Sekolah</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-3">
                <Label for="employment_status" class="text-sm font-bold">Status Kepegawaian</Label>
                <Select v-model="form.employment_status">
                  <SelectTrigger class="h-11 bg-background/50 border-border/50 rounded-xl">
                    <SelectValue placeholder="Pilih Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="PNS">PNS</SelectItem>
                    <SelectItem value="PPPK">PPPK</SelectItem>
                    <SelectItem value="GTY/PTY">GTY/PTY</SelectItem>
                    <SelectItem value="Guru Honor Sekolah">Guru Honor Sekolah</SelectItem>
                    <SelectItem value="Tenaga Honor Sekolah">Tenaga Honor Sekolah</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-3">
                <Label for="sk_pengangkatan" class="text-sm font-bold">SK Pengangkatan</Label>
                <Input
                  id="sk_pengangkatan"
                  v-model="form.sk_pengangkatan"
                  placeholder="Nomor SK Pengangkatan"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="space-y-3">
                <Label for="tmt_pengangkatan" class="text-sm font-bold">TMT Pengangkatan</Label>
                <Input
                  id="tmt_pengangkatan"
                  v-model="form.tmt_pengangkatan"
                  type="date"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="space-y-3">
                <Label for="sk_penugasan" class="text-sm font-bold">SK Penugasan</Label>
                <Input
                  id="sk_penugasan"
                  v-model="form.sk_penugasan"
                  placeholder="Nomor SK Penugasan di Sekolah"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="space-y-3">
                <Label for="tmt_penugasan" class="text-sm font-bold">TMT Penugasan</Label>
                <Input
                  id="tmt_penugasan"
                  v-model="form.tmt_penugasan"
                  type="date"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="flex items-center space-x-3 pt-6 p-4 bg-primary/5 rounded-xl border border-primary/10">
                <Switch
                  id="certification"
                  :checked="form.certification_status"
                  @update:checked="v => form.certification_status = v"
                />
                <div class="space-y-0.5">
                  <Label for="certification" class="text-sm font-bold">Sertifikasi Guru</Label>
                  <p class="text-[10px] text-muted-foreground uppercase tracking-tighter">Centang jika sudah memiliki sertifikat pendidik</p>
                </div>
              </div>
            </div>
          </TabsContent>

          <!-- Tab Pendidikan -->
          <TabsContent
            value="education"
            class="p-8 space-y-8 animate-in slide-in-from-right-2 duration-300"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-3">
                <Label for="last_education" class="text-sm font-bold">Pendidikan Terakhir</Label>
                <Select v-model="form.last_education">
                  <SelectTrigger class="h-11 bg-background/50 border-border/50 rounded-xl">
                    <SelectValue placeholder="Pilih Pendidikan" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="D3">D3</SelectItem>
                    <SelectItem value="D4">D4</SelectItem>
                    <SelectItem value="S1">S1</SelectItem>
                    <SelectItem value="S2">S2</SelectItem>
                    <SelectItem value="S3">S3</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-3">
                <Label for="major" class="text-sm font-bold">Bidang Studi / Jurusan</Label>
                <Input
                  id="major"
                  v-model="form.major"
                  placeholder="Contoh: Pendidikan Teknik Informatika"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="md:col-span-2 space-y-3">
                <Label for="address" class="text-sm font-bold">Alamat Domisili</Label>
                <Textarea
                  id="address"
                  v-model="form.address"
                  rows="4"
                  placeholder="Alamat Lengkap (Jalan, RT/RW, Kec, Kota/Kab)"
                  class="bg-background/50 border-border/50 rounded-xl focus:ring-primary/20 transition-all"
                />
              </div>

              <div class="space-y-3">
                <Label for="phone" class="text-sm font-bold">No. Telepon / HP</Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  placeholder="Contoh: 0812XXXXXXXX"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>

              <div class="space-y-3">
                <Label for="email" class="text-sm font-bold">E-mail Pribadi</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="Gmail / Yahoo / Lainnya"
                  class="h-11 bg-background/50 border-border/50 rounded-xl"
                />
              </div>
            </div>
          </TabsContent>
        </Tabs>
      </CardContent>

      <CardFooter class="flex flex-col sm:flex-row justify-between p-8 bg-muted/20 border-t border-border/30 items-center gap-4">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-full bg-accent/50 flex items-center justify-center text-accent-foreground shrink-0">
             <LucideIcon name="Info" class="w-4 h-4" />
          </div>
          <div class="space-y-0.5">
            <p class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Petunjuk Pengisian</p>
            <p class="text-[10px] text-muted-foreground leading-tight">Pastikan data yang diinput sesuai dengan data pada Kartu Keluarga / Dapodik.</p>
          </div>
        </div>

        <div class="flex gap-3 w-full sm:w-auto">
          <router-link :to="{ name: 'staff.index' }" class="flex-1 sm:flex-none">
            <Button
              variant="outline"
              type="button"
              class="w-full sm:w-auto border-border/50 hover:bg-accent rounded-xl px-8 h-12 transition-all"
            >
              Batal
            </Button>
          </router-link>
          <Button
            type="submit"
            :disabled="loading"
            class="flex-1 sm:flex-none shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all hover:-translate-y-0.5 rounded-xl px-10 h-12 font-bold"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-5 h-5 mr-2 animate-spin"
            />
            <LucideIcon v-else name="Save" class="w-5 h-5 mr-2" />
            {{ isEdit ? 'Simpan Perubahan' : 'Tambah Staff' }}
          </Button>
        </div>
      </CardFooter>
    </form>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  Card, CardContent, CardFooter, Button, LucideIcon, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea,
  Tabs, TabsList, TabsTrigger, TabsContent, Switch
} from '@/components/ui';

const props = defineProps<{
  initialData?: any;
  isEdit?: boolean;
  loading?: boolean;
}>();

const emit = defineEmits(['submit']);

const activeTab = ref('biography');

const form = ref({
  school_id: 15, // Default for SMK NEGERI 1 CIJULANG
  nuptk: '',
  nik: '',
  full_name: '',
  nip: '',
  place_of_birth: '',
  date_of_birth: '',
  gender: '',
  religion: '',
  employment_status: '',
  ptk_type: '',
  sk_pengangkatan: '',
  tmt_pengangkatan: null,
  sk_penugasan: '',
  tmt_penugasan: null,
  last_education: '',
  major: '',
  certification_status: false,
  address: '',
  phone: '',
  email: '',
  ...props.initialData
});

const handleSubmit = () => {
  emit('submit', form.value);
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
