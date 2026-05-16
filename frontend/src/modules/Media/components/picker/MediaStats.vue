<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between space-y-0 pb-1">
          <p class="text-xs font-medium text-muted-foreground">
            {{ $t('modules.system.media.stats.total') }}
          </p>
          <ImageIcon class="h-3.5 w-3.5 text-muted-foreground/50" />
        </div>
        <div>
          <div class="text-xl font-bold">
            {{ stats?.total_count || 0 }}
          </div>
          <p class="text-[10px] text-muted-foreground/70">
            {{ $t('modules.system.media.allMedia') }}
          </p>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between space-y-0 pb-1">
          <p class="text-xs font-medium text-muted-foreground">
            {{ $t('modules.system.media.stats.storage') }}
          </p>
          <HardDrive class="h-3.5 w-3.5 text-muted-foreground/50" />
        </div>
        <div>
          <div class="text-xl font-bold">
            {{ formatFileSize(stats?.total_size || 0) }}
          </div>
          <p class="text-[10px] text-muted-foreground/70">
            {{ $t('modules.system.media.stats.storage') }}
          </p>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between space-y-0 pb-1">
          <p class="text-xs font-medium text-muted-foreground">
            {{ $t('modules.system.media.stats.images') }}
          </p>
          <ImageIcon class="h-3.5 w-3.5 text-muted-foreground/50" />
        </div>
        <div>
          <div class="text-xl font-bold">
            {{ stats?.types?.find((t: any) => t.type === 'image')?.count || 0 }}
          </div>
          <p class="text-[10px] text-muted-foreground/70">
            {{ $t('modules.system.media.stats.images') }}
          </p>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between space-y-0 pb-1">
          <p class="text-xs font-medium text-muted-foreground">
            {{ $t('modules.system.media.stats.videos') }}
          </p>
          <VideoIcon class="h-3.5 w-3.5 text-muted-foreground/50" />
        </div>
        <div>
          <div class="text-xl font-bold">
            {{ stats?.types?.find((t: any) => t.type === 'video')?.count || 0 }}
          </div>
          <p class="text-[10px] text-muted-foreground/70">
            {{ $t('modules.system.media.stats.videos') }}
          </p>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import VideoIcon from 'lucide-vue-next/dist/esm/icons/video.js';
import HardDrive from 'lucide-vue-next/dist/esm/icons/hard-drive.js';
import { Card, CardContent } from '@/shared/components/ui';

import type { MediaStats } from '@/modules/Media/types/media';

defineProps<{
    stats: MediaStats | null;
}>();

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
