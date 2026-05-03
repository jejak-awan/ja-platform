<template>
  <Card>
    <form @submit.prevent="handleSubmit">
      <CardContent class="p-0">
        <Tabs
          v-model="activeTab"
          class="w-full"
        >
          <TabsList class="p-6 border-b bg-muted/20 flex justify-start h-auto gap-4">
            <TabsTrigger value="biography">
              {{ $t('common.labels.biography') }}
            </TabsTrigger>
            <TabsTrigger value="address">
              {{ $t('common.labels.locationContact') }}
            </TabsTrigger>
            <TabsTrigger value="parents">
              {{ $t('common.labels.parentsGuardians') }}
            </TabsTrigger>
          </TabsList>

          <!-- Tab Biografi -->
          <TabsContent
            value="biography"
            class="p-8 space-y-6"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
              <div class="md:col-span-2 space-y-2">
                <Label for="full_name">{{ $t('features.school.admission.labels.full_name') }} <span class="text-destructive">*</span></Label>
                <Input
                  id="full_name"
                  v-model="form.full_name"
                  :placeholder="$t('features.school.admission.placeholders.full_name')"
                  required
                />
              </div>
              <div class="space-y-2">
                <Label for="nisn">NISN</Label>
                <Input
                  id="nisn"
                  v-model="form.nisn"
                  maxlength="10"
                  :placeholder="$t('features.school.admission.placeholders.nisnHint')"
                />
              </div>
              <div class="space-y-2">
                <Label for="nis">NIS</Label>
                <Input
                  id="nis"
                  v-model="form.nis"
                  :placeholder="$t('features.school.admission.placeholders.nisHint')"
                />
              </div>
              <div class="space-y-2">
                <Label for="gender">{{ $t('common.labels.gender') }} <span class="text-destructive">*</span></Label>
                <Select v-model="form.gender">
                  <SelectTrigger><SelectValue :placeholder="$t('features.school.admission.placeholders.gender')" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="L">
                      {{ $t('common.genders.male') }}
                    </SelectItem>
                    <SelectItem value="P">
                      {{ $t('common.genders.female') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="religion">{{ $t('common.labels.religion') }}</Label>
                <Select v-model="form.religion">
                  <SelectTrigger><SelectValue :placeholder="$t('features.school.admission.placeholders.religion')" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Islam">
                      Islam
                    </SelectItem>
                    <SelectItem value="Kristen">
                      Kristen
                    </SelectItem>
                    <SelectItem value="Katolik">
                      Katolik
                    </SelectItem>
                    <SelectItem value="Hindu">
                      Hindu
                    </SelectItem>
                    <SelectItem value="Budha">
                      Budha
                    </SelectItem>
                    <SelectItem value="Konghucu">
                      Konghucu
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="place_of_birth">{{ $t('common.labels.placeOfBirth') }}</Label>
                <Input
                  id="place_of_birth"
                  v-model="form.place_of_birth"
                />
              </div>
              <div class="space-y-2">
                <Label for="date_of_birth">{{ $t('common.labels.dateOfBirth') }}</Label>
                <Input
                  id="date_of_birth"
                  v-model="form.date_of_birth"
                  type="date"
                />
              </div>
              <div class="space-y-2">
                <Label for="level">{{ $t('features.school.students.labels.level') }}</Label>
                <Select v-model="form.school_unit_id">
                  <SelectTrigger><SelectValue :placeholder="$t('features.school.admission.placeholders.level')" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="level in levels"
                      :key="level.id"
                      :value="level.id.toString()"
                    >
                      {{ level.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div class="space-y-2">
                <Label for="department">{{ $t('features.school.academic.labels.department') }} ({{ $t('common.labels.other') }})</Label>
                <Select v-model="form.department_id">
                  <SelectTrigger><SelectValue :placeholder="$t('features.school.academic.placeholders.department')" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">
                      {{ $t('common.labels.none') }}
                    </SelectItem>
                    <SelectItem
                      v-for="dept in departments"
                      :key="dept.id"
                      :value="dept.id.toString()"
                    >
                      {{ dept.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </TabsContent>

          <!-- Tab Alamat -->
          <TabsContent
            value="address"
            class="p-8 space-y-6"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
              <div class="md:col-span-2 space-y-2">
                <Label for="address">{{ $t('common.labels.fullAddress') }}</Label>
                <Textarea
                  id="address"
                  v-model="form.address"
                  rows="3"
                />
              </div>
              <div class="space-y-2">
                <Label for="dusun">{{ $t('common.labels.dusun') }} / Jalan</Label>
                <Input
                  id="dusun"
                  v-model="form.dusun"
                />
              </div>
              <div class="grid grid-cols-2 gap-4 text-left">
                <div class="space-y-2">
                  <Label for="rt">RT</Label>
                  <Input
                    id="rt"
                    v-model="form.rt"
                    maxlength="3"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="rw">RW</Label>
                  <Input
                    id="rw"
                    v-model="form.rw"
                    maxlength="3"
                  />
                </div>
              </div>
              <div class="space-y-2">
                <Label for="village">{{ $t('common.labels.village') }}</Label>
                <Input
                  id="village"
                  v-model="form.desa_kelurahan"
                />
              </div>
              <div class="space-y-2">
                <Label for="district">{{ $t('common.labels.subdistrict') }}</Label>
                <Input
                  id="district"
                  v-model="form.kecamatan"
                />
              </div>
              <div class="space-y-2">
                <Label for="phone">{{ $t('common.labels.phone') }}</Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                />
              </div>
              <div class="space-y-2">
                <Label for="email">{{ $t('common.labels.email') }}</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                />
              </div>
            </div>
          </TabsContent>

          <!-- Tab Orang Tua -->
          <TabsContent
            value="parents"
            class="p-8 space-y-8"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
              <!-- Ayah -->
              <div class="space-y-4">
                <h4 class="font-bold border-l-4 border-primary pl-2 uppercase text-sm">
                  {{ $t('features.school.admission.labels.father_name') }}
                </h4>
                <div class="space-y-2">
                  <Label for="father_name">{{ $t('features.school.students.labels.fatherName') }}</Label>
                  <Input
                    id="father_name"
                    v-model="form.father_name"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="father_nik">NIK {{ $t('features.school.admission.labels.father_name') }}</Label>
                  <Input
                    id="father_nik"
                    v-model="form.father_nik"
                    maxlength="16"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="father_occupation">{{ $t('features.school.students.labels.fatherOccupation') }}</Label>
                  <Input
                    id="father_occupation"
                    v-model="form.father_occupation"
                  />
                </div>
              </div>

              <!-- Ibu -->
              <div class="space-y-4">
                <h4 class="font-bold border-l-4 border-primary pl-2 uppercase text-sm">
                  {{ $t('features.school.admission.labels.mother_name') }}
                </h4>
                <div class="space-y-2">
                  <Label for="mother_name">{{ $t('features.school.students.labels.motherName') }}</Label>
                  <Input
                    id="mother_name"
                    v-model="form.mother_name"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="mother_nik">NIK {{ $t('features.school.admission.labels.mother_name') }}</Label>
                  <Input
                    id="mother_nik"
                    v-model="form.mother_nik"
                    maxlength="16"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="mother_occupation">{{ $t('features.school.students.labels.motherName') }} {{ $t('features.school.students.labels.fatherOccupation') }}</Label>
                  <Input
                    id="mother_occupation"
                    v-model="form.mother_occupation"
                  />
                </div>
              </div>
            </div>
          </TabsContent>
        </Tabs>
      </CardContent>

      <CardFooter class="flex justify-between p-8 bg-muted/20 border-t items-center">
        <p class="text-xs text-muted-foreground flex items-center gap-1">
          <LucideIcon
            name="Info"
            class="w-3 h-3"
          />
          {{ $t('common.labels.requiredFieldHint', { symbol: '*' }) }}
        </p>
        <div class="flex gap-2">
          <router-link :to="{ name: 'students.index' }">
            <Button
              variant="outline"
              type="button"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
          </router-link>
          <Button
            type="submit"
            :disabled="loading"
          >
            <LucideIcon
              v-if="loading"
              name="Loader2"
              class="w-4 h-4 mr-2 animate-spin"
            />
            {{ isEdit ? $t('common.actions.saveChanges') : $t('features.school.students.actions.add') }}
          </Button>
        </div>
      </CardFooter>
    </form>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
  Card, CardContent, CardFooter, Button, LucideIcon, Label, Input, Select, SelectTrigger, SelectValue, SelectContent, SelectItem, Textarea,
  Tabs, TabsList, TabsTrigger, TabsContent
} from '@/components/ui';
import { InstitutionService } from '@/modules/School/services/InstitutionService';
import { AcademicService } from '@/modules/School/services/AcademicService';
import { parseResponse } from '@/utils/responseParser';

const props = defineProps<{
  initialData?: any;
  isEdit?: boolean;
  loading?: boolean;
}>();

const emit = defineEmits(['submit']);

const activeTab = ref('biography');
const levels = ref<any[]>([]);
const departments = ref<any[]>([]);

const form = ref<any>({
  school_id: 1,
  school_unit_id: '',
  full_name: '',
  nisn: '',
  nis: '',
  gender: '',
  religion: '',
  place_of_birth: '',
  date_of_birth: '',
  address: '',
  rt: '',
  rw: '',
  dusun: '',
  desa_kelurahan: '',
  kecamatan: '',
  phone: '',
  email: '',
  father_name: '',
  father_nik: '',
  father_occupation: '',
  mother_name: '',
  mother_occupation: '',
  ...props.initialData,
  // override after initialData spread
  department_id: props.initialData?.department_id ? props.initialData.department_id.toString() : 'none'
});

const fetchMetadata = async () => {
    try {
        const [levelRes, deptRes] = await Promise.all([
            InstitutionService.getUnits(),
            AcademicService.getDepartments()
        ]);
        
        levels.value = parseResponse(levelRes).data;
        departments.value = parseResponse(deptRes).data;
    } catch (e) {
        console.error('Failed to fetch metadata:', e);
    }
}

const handleSubmit = () => {
  const payload = { ...form.value };
  if (payload.department_id === 'none') {
      payload.department_id = null;
  }
  emit('submit', payload);
};

onMounted(() => {
    fetchMetadata();
});
</script>
