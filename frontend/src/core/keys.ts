import type { InjectionKey } from 'vue';
import type { FileManager } from '@/modules/Cms/composables/useFileManager';


export const FileManagerKey: InjectionKey<FileManager> = Symbol('FileManager');

import type { MediaManager } from '@/modules/Cms/composables/useMediaManager';
export const MediaManagerKey: InjectionKey<MediaManager> = Symbol('MediaManager');
