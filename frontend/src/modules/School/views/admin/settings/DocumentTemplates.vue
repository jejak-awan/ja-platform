<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-foreground">{{ $t('features.school.ops.templates.title') }}</h2>
        <p class="text-sm text-muted-foreground italic">{{ $t('features.school.ops.templates.subtitle') }}</p>
      </div>
      <Button
        variant="default"
        class="rounded-xl shadow-sm px-6"
        @click="openCreateModal"
      >
        <LucideIcon
          name="Plus"
          class="w-4 h-4 mr-2"
        />
        {{ $t('features.school.ops.templates.btnAdd') }}
      </Button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
        <LucideIcon name="RefreshCcw" class="w-8 h-8 animate-spin text-primary/40" />
    </div>

    <div v-else-if="templates.length === 0" class="text-center py-12 bg-muted/20 rounded-2xl border border-dashed border-border">
        <LucideIcon name="FileText" class="w-12 h-12 mx-auto mb-4 text-muted-foreground/40" />
        <h3 class="font-bold text-lg">{{ $t('features.school.ops.templates.emptyTitle') }}</h3>
        <p class="text-muted-foreground text-sm max-w-md mx-auto">{{ $t('features.school.ops.templates.emptySubtitle') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card 
            v-for="template in templates" 
            :key="template.id"
            class="border border-border/40 shadow-sm rounded-2xl overflow-hidden group hover:border-primary/40 transition-all"
        >
            <CardContent class="p-6 space-y-4">
                <div class="flex justify-between items-start">
                    <div :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest', typeBadgeColor(template.type)]">
                        {{ template.type }}
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-primary" @click="openEditModal(template)">
                            <LucideIcon name="Edit" class="w-3.5 h-3.5" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="handleDelete(template)">
                            <LucideIcon name="Trash2" class="w-3.5 h-3.5" />
                        </Button>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-lg leading-tight">{{ template.name }}</h3>
                    <p class="text-xs text-muted-foreground">{{ $t('features.school.ops.templates.lastUpdated') }}: {{ formatDate(template.updated_at) }}</p>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5" :class="template.is_active ? 'text-success' : 'text-muted-foreground'">
                        <div :class="['w-1.5 h-1.5 rounded-full', template.is_active ? 'bg-success' : 'bg-muted-foreground']"></div>
                        {{ template.is_active ? $t('features.school.ops.templates.labels.active') : $t('features.school.ops.templates.labels.inactive') }}
                    </div>
                    <div v-if="template.is_default" class="flex items-center gap-1.5 text-primary">
                        <LucideIcon name="Star" class="w-3 h-3 fill-primary" />
                        {{ $t('features.school.ops.templates.labels.default') }}
                    </div>
                </div>
            </CardContent>
            <div class="p-3 bg-muted/30 border-t border-border/40 flex justify-end">
                <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" @click="previewTemplate(template)">
                    {{ $t('features.school.ops.templates.previewHtml') }}
                </Button>
            </div>
        </Card>
    </div>

    <!-- Template Form Modal -->
    <Dialog v-model:open="modalOpen">
      <DialogContent class="sm:max-w-[900px] max-h-[90vh] overflow-hidden flex flex-col rounded-2xl">
        <DialogHeader>
          <DialogTitle>{{ editingTemplate?.id ? 'Edit Template' : 'Tambah Template' }}</DialogTitle>
          <DialogDescription>
            Gunakan HTML & CSS untuk mendesain template. Gunakan placeholder seperti <code v-pre>{{ $full_name }}</code> untuk data dinamis.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="editingTemplate" class="flex flex-col flex-1 overflow-hidden">
          <!-- Always Visible Fields -->
          <div class="grid grid-cols-2 gap-4 py-4 border-b border-border/50">
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase">{{ $t('features.school.ops.templates.modal.labelName') }}</label>
              <Input
                v-model="editingTemplate.name"
                :placeholder="$t('features.school.ops.templates.modal.placeholderName')"
                class="rounded-xl"
              />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-bold uppercase">{{ $t('features.school.ops.templates.modal.labelType') }}</label>
              <Select v-model="editingTemplate.type">
                <SelectTrigger class="rounded-xl">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="skl">{{ $t('features.school.ops.templates.modal.types.skl') }}</SelectItem>
                  <SelectItem value="certificate">{{ $t('features.school.ops.templates.modal.types.certificate') }}</SelectItem>
                  <SelectItem value="letter">{{ $t('features.school.ops.templates.modal.types.letter') }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <Tabs default-value="editor" class="flex-1 flex flex-col overflow-hidden mt-4">
            <TabsList class="grid w-full grid-cols-2 lg:w-[300px]">
              <TabsTrigger value="editor">Editor</TabsTrigger>
              <TabsTrigger value="preview">{{ $t('features.school.ops.templates.previewHtml') }}</TabsTrigger>
            </TabsList>

            <TabsContent value="editor" class="flex-1 overflow-y-auto space-y-4 py-4 pr-2">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-xs font-bold uppercase">{{ $t('features.school.ops.templates.modal.labelHtml') }}</label>
                  <Textarea
                    v-model="editingTemplate.content"
                    placeholder="<div class='certificate'>...</div>"
                    class="rounded-xl font-mono text-xs min-h-[300px]"
                  />
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase">{{ $t('features.school.ops.templates.modal.labelCss') }}</label>
                    <Textarea
                      v-model="editingTemplate.styles"
                      placeholder=".certificate { padding: 20px; }"
                      class="rounded-xl font-mono text-xs min-h-[300px]"
                    />
                  </div>
              </div>

              <div class="flex items-center gap-6">
                  <div class="flex items-center space-x-2">
                    <Checkbox id="is_active" v-model:checked="editingTemplate.is_active" />
                    <label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                      {{ $t('features.school.ops.templates.modal.labelActive') }}
                    </label>
                  </div>
                  <div class="flex items-center space-x-2">
                    <Checkbox id="is_default" v-model:checked="editingTemplate.is_default" />
                    <label for="is_default" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                      {{ $t('features.school.ops.templates.modal.labelDefault') }}
                    </label>
                  </div>
              </div>
              
              <div class="p-3 bg-primary/5 rounded-xl border border-primary/10">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-primary mb-2">{{ $t('features.school.ops.templates.modal.placeholdersTitle') }}</p>
                  <div class="flex flex-wrap gap-2">
                      <span v-for="p in availablePlaceholders" :key="p" class="px-2 py-0.5 bg-white border border-border rounded text-[10px] font-mono">
                          {{ p }}
                      </span>
                  </div>
              </div>
            </TabsContent>

            <TabsContent value="preview" class="flex-1 overflow-hidden py-4">
                <div class="w-full h-full border border-border rounded-2xl overflow-auto bg-white p-8">
                    <style v-if="editingTemplate.styles">{{ editingTemplate.styles }}</style>
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <div v-html="previewContent"></div>
                </div>
            </TabsContent>
          </Tabs>
        </div>

        <DialogFooter>
          <Button 
            variant="outline" 
            class="rounded-xl" 
            @click="modalOpen = false"
          >
            Batal
          </Button>
          <Button 
            variant="default" 
            class="rounded-xl px-8" 
            :loading="saving"
            @click="saveTemplate"
          >
            Simpan Template
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <ConfirmModal ref="confirmModal" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { 
    Card, CardContent, Button, LucideIcon, 
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
    Input, Textarea, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Checkbox, ConfirmModal, Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';
import { OperationsService } from '@/modules/School/services/OperationsService';
import { useToast } from '@/composables/useToast';
import { parseResponse } from '@/utils/responseParser';
import dayjs from 'dayjs';

const toast = useToast();
const loading = ref(true);
const templates = ref<any[]>([]);
const confirmModal = ref<any>(null);

const fetchTemplates = async () => {
    loading.value = true;
    try {
        const response = await OperationsService.getDocumentTemplates();
        templates.value = parseResponse(response).data;
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date: string) => dayjs(date).format('DD MMM YYYY HH:mm');

const typeBadgeColor = (type: string) => {
    if (type === 'skl') return 'bg-blue-100 text-blue-700';
    if (type === 'certificate') return 'bg-amber-100 text-amber-700';
    return 'bg-slate-100 text-slate-700';
};

// Form Logic
const modalOpen = ref(false);
const editingTemplate = ref<any>(null);
const saving = ref(false);

const previewContent = computed(() => {
    if (!editingTemplate.value?.content) return '';
    let content = editingTemplate.value.content;
    
    // Mock data for preview
    const mockData: Record<string, string> = {
        'full_name': 'Ahmad Fauzi',
        'nisn': '0012345678',
        'nis': '12345',
        'gender': 'Laki-laki',
        'birth_place': 'Jakarta',
        'birth_date': '12 Mei 2006',
        'level_name': 'XII RPL 1',
        'department_name': 'Rekayasa Perangkat Lunak',
        'graduation_year': '2024',
        'certificate_number': 'SKL/2024/001',
        'published_date': dayjs().format('DD MMMM YYYY')
    };

    Object.keys(mockData).forEach(key => {
        const regex = new RegExp(`\\{\\{\\s*\\$${key}\\s*\\}\\}`, 'g');
        content = content.replace(regex, mockData[key]);
    });

    // Handle grades mock (simplified)
    content = content.replace(/\{\{\s*\$grades\[".*?"\]\s*\}\}/g, '90');

    return content;
});

const availablePlaceholders = [
    '{{ $full_name }}', '{{ $nisn }}', '{{ $nis }}', '{{ $gender }}', '{{ $birth_place }}', '{{ $birth_date }}',
    '{{ $level_name }}', '{{ $department_name }}', '{{ $graduation_year }}', '{{ $certificate_number }}', 
    '{{ $published_date }}', '{{ $grades["Mapel Name"] }}'
];

type DocType = 'skl' | 'certificate' | 'letter';
const DEFAULT_TEMPLATES: Record<DocType, { content: string, styles: string }> = {
    skl: {
        content: `<div class="skl-container">
  <div class="header">
    <div class="school-logo">[LOGO]</div>
    <div class="school-info">
      <h1 class="school-name">NAMA SEKOLAH ANDA</h1>
      <p class="school-address">Jl. Alamat Sekolah No. 123, Kota, Provinsi</p>
      <p class="school-contact">Telp: (021) 123456 | Website: www.sekolahanda.sch.id</p>
    </div>
  </div>
  
  <hr class="header-line">
  
  <div class="document-title">
    <h2>SURAT KETERANGAN LULUS</h2>
    <p class="cert-number">Nomor: {{ $certificate_number }}</p>
  </div>
  
  <div class="body-content">
    <p>Yang bertanda tangan di bawah ini, Kepala Sekolah Menengah Kejuruan [NAMA SEKOLAH], menerangkan bahwa:</p>
    
    <table class="student-info">
      <tr><td>Nama Lengkap</td><td>:</td><td class="bold">{{ $full_name }}</td></tr>
      <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td>{{ $birth_place }}, {{ $birth_date }}</td></tr>
      <tr><td>Nomor Induk Siswa (NIS)</td><td>:</td><td>{{ $nis }}</td></tr>
      <tr><td>Nomor Induk Siswa Nasional (NISN)</td><td>:</td><td>{{ $nisn }}</td></tr>
      <tr><td>Kompetensi Keahlian</td><td>:</td><td>{{ $department_name }}</td></tr>
    </table>
    
    <p class="status-text">Berdasarkan hasil rapat pleno dewan guru pada tanggal [TANGGAL RAPAT], siswa tersebut di atas dinyatakan:</p>
    
    <div class="status-box">LULUS</div>
    
    <p>dari satuan pendidikan [NAMA SEKOLAH] tahun pelajaran {{ $graduation_year }}. Surat keterangan ini dapat digunakan untuk keperluan administrasi sementara hingga ijazah asli diterbitkan.</p>
  </div>
  
  <div class="footer">
    <div class="signature">
      <p>Kota Sekolah, {{ $published_date }}</p>
      <p>Kepala Sekolah,</p>
      <div class="sig-space"></div>
      <p class="bold">[NAMA KEPALA SEKOLAH]</p>
      <p>NIP. [NOMOR INDUK PEGAWAI]</p>
    </div>
  </div>
</div>`,
        styles: `.skl-container { 
  width: 100%; 
  max-width: 800px; 
  margin: 0 auto; 
  padding: 40px; 
  font-family: 'Times New Roman', Times, serif;
  color: #000;
  background: #fff;
  line-height: 1.6;
}
.header { display: flex; align-items: center; margin-bottom: 10px; }
.school-logo { width: 80px; height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; margin-right: 20px; }
.school-info { flex: 1; text-align: center; }
.school-name { margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
.school-address, .school-contact { margin: 0; font-size: 12px; }
.header-line { border: 2px solid #000; margin-bottom: 20px; }
.document-title { text-align: center; margin-bottom: 30px; }
.document-title h2 { margin: 0; text-decoration: underline; font-size: 20px; }
.cert-number { margin: 5px 0 0; font-size: 14px; }
.student-info { width: 90%; margin: 20px auto; border-collapse: collapse; }
.student-info td { padding: 5px; vertical-align: top; font-size: 14px; }
.student-info td:first-child { width: 200px; }
.student-info td:nth-child(2) { width: 20px; }
.bold { font-weight: bold; }
.status-text { margin-top: 20px; text-align: justify; font-size: 14px; }
.status-box { width: 200px; margin: 20px auto; border: 3px double #000; padding: 10px; text-align: center; font-size: 24px; font-weight: bold; }
.footer { margin-top: 40px; display: flex; justify-content: flex-end; }
.signature { text-align: center; width: 250px; }
.sig-space { height: 80px; }`
    },
    certificate: {
        content: `<div class="cert-border">
  <div class="cert-inner">
    <div class="cert-header">
      <div class="cert-school-logo">[LOGO]</div>
      <h1>SERTIFIKAT PENGHARGAAN</h1>
      <p class="cert-subtitle">Diberikan Kepada:</p>
    </div>
    
    <div class="cert-body">
      <h2 class="cert-student-name">{{ $full_name }}</h2>
      <p class="cert-text">Atas prestasi dan dedikasi luar biasa dalam menyelesaikan pendidikan di [NAMA SEKOLAH] Program Keahlian {{ $department_name }} dengan predikat memuaskan pada tahun pelajaran {{ $graduation_year }}.</p>
    </div>
    
    <div class="cert-footer">
      <div class="cert-sign">
        <p>{{ $published_date }}</p>
        <p>Kepala Sekolah,</p>
        <div class="sig-placeholder"></div>
        <p class="bold">[NAMA KEPALA SEKOLAH]</p>
      </div>
    </div>
  </div>
</div>`,
        styles: `.cert-border {
  width: 800px;
  height: 560px;
  padding: 20px;
  background: #fdf6e3;
  border: 10px solid #c9a55c;
  margin: 0 auto;
  position: relative;
  font-family: 'Garamond', serif;
}
.cert-inner {
  border: 2px solid #c9a55c;
  height: 100%;
  padding: 40px;
  text-align: center;
}
.cert-header h1 {
  font-size: 36px;
  color: #8c6d31;
  margin: 20px 0;
  letter-spacing: 2px;
}
.cert-student-name {
  font-size: 32px;
  border-bottom: 2px solid #333;
  display: inline-block;
  padding: 0 40px;
  margin: 20px 0;
  color: #333;
}
.cert-text { font-size: 18px; line-height: 1.5; margin: 20px auto; max-width: 600px; }
.cert-footer { margin-top: 40px; display: flex; justify-content: flex-end; }
.cert-sign { width: 200px; }
.sig-placeholder { height: 60px; }`
    },
    letter: {
        content: `<div class="letter-container">
  <div class="letter-head">
    <h3>YAYASAN PENDIDIKAN [NAMA YAYASAN]</h3>
    <h2>[NAMA SEKOLAH ANDA]</h2>
    <p>Status: Terakreditasi A | NSS: 123456789 | NPSN: 98765432</p>
    <p>Alamat: Jl. Pendidikan No. 1, Kota | Email: info@sekolah.sch.id</p>
    <hr class="double-line">
  </div>
  
  <div class="letter-meta">
    <p>Nomor: {{ $certificate_number }}</p>
    <p>Lampiran: -</p>
    <p>Perihal: Surat Keterangan Siswa</p>
  </div>
  
  <div class="letter-title">
    <h3>SURAT KETERANGAN</h3>
  </div>
  
  <div class="letter-body">
    <p>Kepala [NAMA SEKOLAH ANDA] dengan ini menerangkan bahwa:</p>
    <div class="letter-student">
      <p>Nama: <b>{{ $full_name }}</b></p>
      <p>NIS/NISN: {{ $nis }} / {{ $nisn }}</p>
      <p>Kelas: {{ $level_name }}</p>
    </div>
    <p>Adalah benar-benar siswa yang terdaftar aktif pada tahun pelajaran {{ $graduation_year }}. Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
  </div>
  
  <div class="letter-footer">
    <div class="letter-sign">
      <p>Dikeluarkan di: [KOTA]</p>
      <p>Pada tanggal: {{ $published_date }}</p>
      <p>Kepala Sekolah,</p>
      <div class="sig-gap"></div>
      <p><b>[NAMA KEPALA SEKOLAH]</b></p>
    </div>
  </div>
</div>`,
        styles: `.letter-container { padding: 50px; background: white; min-height: 800px; color: black; font-family: Arial, sans-serif; }
.letter-head { text-align: center; margin-bottom: 20px; }
.letter-head h2, .letter-head h3 { margin: 0; }
.double-line { border-top: 3px double black; margin-top: 10px; }
.letter-meta { margin: 30px 0; }
.letter-title { text-align: center; text-decoration: underline; margin-bottom: 30px; }
.letter-student { margin: 20px 50px; }
.letter-footer { margin-top: 50px; display: flex; justify-content: flex-end; }
.letter-sign { text-align: left; }
.sig-gap { height: 70px; }`
    }
};

const openCreateModal = () => {
    editingTemplate.value = {
        name: '',
        type: 'skl' as DocType,
        content: DEFAULT_TEMPLATES.skl.content,
        styles: DEFAULT_TEMPLATES.skl.styles,
        is_active: true,
        is_default: false
    };
    modalOpen.value = true;
};

// Auto-switch template content when type changes (for new templates only)
watch(() => editingTemplate.value?.type, (newType: DocType | undefined) => {
    if (newType && !editingTemplate.value?.id) {
        // Only switch if content is still the default for another type
        const currentContent = editingTemplate.value.content;
        const isDefault = Object.values(DEFAULT_TEMPLATES).some(t => t.content === currentContent);
        
        if (isDefault && DEFAULT_TEMPLATES[newType]) {
            editingTemplate.value.content = DEFAULT_TEMPLATES[newType].content;
            editingTemplate.value.styles = DEFAULT_TEMPLATES[newType].styles;
        }
    }
});

const openEditModal = (template: any) => {
    editingTemplate.value = { ...template };
    modalOpen.value = true;
};

const saveTemplate = async () => {
    saving.value = true;
    try {
        if (editingTemplate.value.id) {
            await OperationsService.updateDocumentTemplate(editingTemplate.value.id, editingTemplate.value);
        } else {
            await OperationsService.storeDocumentTemplate(editingTemplate.value);
        }
        toast.success.action('Template berhasil disimpan');
        modalOpen.value = false;
        fetchTemplates();
    } catch (e) {
        toast.error.fromResponse(e);
    } finally {
        saving.value = false;
    }
};

const handleDelete = async (template: any) => {
    const confirmed = await confirmModal.value.confirm({
        title: 'Hapus Template',
        message: `Apakah Anda yakin ingin menghapus template "${template.name}"?`,
        variant: 'destructive'
    });

    if (confirmed) {
        try {
            await OperationsService.deleteDocumentTemplate(template.id);
            toast.success.action('Template berhasil dihapus');
            fetchTemplates();
        } catch (e) {
            toast.error.fromResponse(e);
        }
    }
};

const previewTemplate = (template: any) => {
    const win = window.open('', '_blank');
    if (win) {
        win.document.write(`
            <html>
                <head>
                    <style>${template.styles}</style>
                </head>
                <body>
                    ${template.content.replace(/\{\{\s*\$full_name\s*\}\}/g, 'CONTOH NAMA SISWA')}
                </body>
            </html>
        `);
        win.document.close();
    }
};

onMounted(fetchTemplates);
</script>
