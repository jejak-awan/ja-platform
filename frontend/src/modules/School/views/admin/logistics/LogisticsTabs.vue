<template>
  <div class="space-y-8 p-6 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-2">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
            <LucideIcon
              name="Truck"
              class="w-5 h-5 text-primary"
            />
          </div>
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase">
            {{ $t('features.school.logistics.labels.logisticsEcosystem') }}
          </h1>
        </div>
        <p class="text-muted-foreground text-sm font-medium italic">
          {{ $t('features.school.logistics.subtitle') }}
        </p>
      </div>
    </div>

    <Tabs
      v-model="activeTab"
      class="w-full"
    >
      <TabsList class="p-2 bg-muted/50 rounded-2xl inline-flex h-auto gap-2 mb-6">
        <TabsTrigger 
          value="hostel" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold"
        >
          <LucideIcon
            name="Home"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.hostelManagement') }}
        </TabsTrigger>
        <TabsTrigger 
          value="transport" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold"
        >
          <LucideIcon
            name="Bus"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.transport') }}
        </TabsTrigger>
        <TabsTrigger 
          value="inventory" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold"
        >
          <LucideIcon
            name="Box"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.inventory') }}
        </TabsTrigger>
        <TabsTrigger 
          value="career" 
          class="rounded-xl px-6 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm border border-transparent data-[state=active]:border-border/40 font-bold"
        >
          <LucideIcon
            name="Briefcase"
            class="w-4 h-4 mr-2"
          />
          {{ $t('features.school.logistics.labels.careerCenter') }}
        </TabsTrigger>
      </TabsList>

      <div class="mt-4">
        <TabsContent value="hostel">
          <Hostel />
        </TabsContent>
        <TabsContent value="transport">
          <Transport />
        </TabsContent>
        <TabsContent value="inventory">
          <Inventory />
        </TabsContent>
        <TabsContent value="career">
          <JobPortal />
        </TabsContent>
      </div>
    </Tabs>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Tabs, TabsList, TabsTrigger, TabsContent, LucideIcon } from '@/components/ui';
import Hostel from './Hostel.vue';
import Transport from './Transport.vue';
import Inventory from './Inventory.vue';
import JobPortal from './JobPortal.vue';

const activeTab = ref('hostel');
</script>
