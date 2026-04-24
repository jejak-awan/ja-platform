import type { InjectionKey } from 'vue';
import type { FileManager } from '@/composables/useFileManager';


export const FileManagerKey: InjectionKey<FileManager> = Symbol('FileManager');

import type { MediaManager } from '@/composables/useMediaManager';
export const MediaManagerKey: InjectionKey<MediaManager> = Symbol('MediaManager');
