<template>
  <node-view-wrapper 
    as="span" 
    class="inline-block align-middle select-none mx-0.5 leading-none relative group"
    :class="{ 'ring-1 ring-primary rounded-sm': selected }"
    :style="wrapperStyle"
  >
    <component 
      :is="iconComponent" 
      :size="sizeValue"
      :stroke-width="node.attrs.strokeWidth"
      class="transition-transform"
      :style="iconStyle"
    />

    <!-- Resize Handle (Only visible when selected) -->
    <div 
      v-if="selected"
      class="absolute -right-1 -bottom-1 w-3 h-3 bg-primary border border-white rounded-full cursor-se-resize z-10"
      @mousedown.stop.prevent="startResize"
    />
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { computed, ref, onUnmounted } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import Circle from 'lucide-vue-next/dist/esm/icons/circle.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import Heart from 'lucide-vue-next/dist/esm/icons/heart.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import Video from 'lucide-vue-next/dist/esm/icons/video.js';
import Play from 'lucide-vue-next/dist/esm/icons/play.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import Grid from 'lucide-vue-next/dist/esm/icons/grid-2x2.js';
import Trash2 from 'lucide-vue-next/dist/esm/icons/trash-2.js';
import Edit3 from 'lucide-vue-next/dist/esm/icons/pen-tool.js';
import LogOut from 'lucide-vue-next/dist/esm/icons/log-out.js';
import ExternalLink from 'lucide-vue-next/dist/esm/icons/external-link.js';
import AlertCircle from 'lucide-vue-next/dist/esm/icons/circle-alert.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import PlusCircle from 'lucide-vue-next/dist/esm/icons/circle-plus.js';
import Lock from 'lucide-vue-next/dist/esm/icons/lock.js';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import Calendar from 'lucide-vue-next/dist/esm/icons/calendar.js';
import Camera from 'lucide-vue-next/dist/esm/icons/camera.js';
import Clock from 'lucide-vue-next/dist/esm/icons/clock.js';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import Flag from 'lucide-vue-next/dist/esm/icons/flag.js';
import Key from 'lucide-vue-next/dist/esm/icons/key.js';
import Eye from 'lucide-vue-next/dist/esm/icons/eye.js';
import EyeOff from 'lucide-vue-next/dist/esm/icons/eye-off.js';
import Maximize2 from 'lucide-vue-next/dist/esm/icons/maximize.js';
import Minimize2 from 'lucide-vue-next/dist/esm/icons/minimize.js';
import Rows from 'lucide-vue-next/dist/esm/icons/rows-2.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import Download from 'lucide-vue-next/dist/esm/icons/download.js';
import Upload from 'lucide-vue-next/dist/esm/icons/upload.js';
import RefreshCw from 'lucide-vue-next/dist/esm/icons/refresh-cw.js';
import Paperclip from 'lucide-vue-next/dist/esm/icons/paperclip.js';
import Send from 'lucide-vue-next/dist/esm/icons/send.js';
import AtSign from 'lucide-vue-next/dist/esm/icons/at-sign.js';
import Languages from 'lucide-vue-next/dist/esm/icons/languages.js';
import Phone from 'lucide-vue-next/dist/esm/icons/phone.js';
import MessageCircle from 'lucide-vue-next/dist/esm/icons/message-circle.js';
import Headphones from 'lucide-vue-next/dist/esm/icons/headphones.js';
import Speaker from 'lucide-vue-next/dist/esm/icons/speaker.js';
import Volume2 from 'lucide-vue-next/dist/esm/icons/volume-2.js';
import Film from 'lucide-vue-next/dist/esm/icons/film.js';
import Music from 'lucide-vue-next/dist/esm/icons/music.js';
import Mic from 'lucide-vue-next/dist/esm/icons/mic.js';
import Sliders from 'lucide-vue-next/dist/esm/icons/sliders-horizontal.js';
import MoreHorizontal from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import GripHorizontal from 'lucide-vue-next/dist/esm/icons/grip-horizontal.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Filter from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import Save from 'lucide-vue-next/dist/esm/icons/save.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Power from 'lucide-vue-next/dist/esm/icons/power.js';
import ShieldCheck from 'lucide-vue-next/dist/esm/icons/shield-check.js';
import ShieldAlert from 'lucide-vue-next/dist/esm/icons/shield-alert.js';
import AlertTriangle from 'lucide-vue-next/dist/esm/icons/triangle-alert.js';
import CheckCircle2 from 'lucide-vue-next/dist/esm/icons/circle-check-big.js';
import XCircle from 'lucide-vue-next/dist/esm/icons/circle-x.js';
import type { Component } from 'vue';

const iconMap: Record<string, Component> = {
  Circle, Zap, Star, Heart, HelpCircle, Info, Check, X, 
  ArrowRight, ChevronRight, Mail, MessageSquare, Image, 
  Video, Play, Settings, Search, Menu, Grid, Trash2, 
  Edit3, LogOut, ExternalLink, AlertCircle, Shield, 
  PlusCircle, Lock, User, Bell, Calendar, Camera, 
  Clock, MapPin, Flag, Key, Eye, EyeOff, Maximize2, 
  Minimize2, Rows, Columns, Layout, Share2, Download, 
  Upload, RefreshCw, Paperclip, Send, AtSign, Languages, 
  Phone, MessageCircle, Headphones, Speaker, Volume2, 
  Film, Music, Mic, Sliders, MoreHorizontal, GripHorizontal, 
  Layers, Filter, Save, Copy, Power, ShieldCheck, 
  ShieldAlert, AlertTriangle, CheckCircle2, XCircle
};

const props = defineProps(nodeViewProps)

const iconComponent = computed(() => {
    const name = props.node.attrs.name;
    return iconMap[name] || iconMap.Circle;
});

// Computed Styles
const wrapperStyle = computed(() => ({
    backgroundColor: props.node.attrs.backgroundColor,
    borderRadius: props.node.attrs.borderRadius,
    padding: props.node.attrs.padding,
    transform: `rotate(${props.node.attrs.rotate}deg)`,
    opacity: props.node.attrs.opacity,
}));

const iconStyle = computed(() => ({
    color: props.node.attrs.color,
    width: props.node.attrs.size,
    height: props.node.attrs.size,
}));

const sizeValue = computed(() => {
    // If size is '1em' or string, pass it to style, but Lucide prop expects number or string.
    return props.node.attrs.size;
});

// Resize Logic
const isResizing = ref(false);
const startX = ref(0);
const startWidth = ref(0);

const startResize = (event: MouseEvent) => {
    isResizing.value = true;
    startX.value = event.clientX;
    
    // Parse current size to pixels if possible, or approximate
    // Limit resizing to pixel values for consistency during drag
    const currentSize = props.node.attrs.size;
    
    if (typeof currentSize === 'string' && currentSize.endsWith('px')) {
        startWidth.value = parseInt(currentSize);
    } else if (typeof currentSize === 'string' && currentSize.endsWith('em')) {
         // Convert em to approx px for resizing interaction (assuming 16px base)
         // This is a rough approximation to start the drag
         startWidth.value = parseFloat(currentSize) * 16;
    } else {
        startWidth.value = 24; // Default fallback
    }

    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
};

const onResize = (event: MouseEvent) => {
    if (!isResizing.value) return;
    
    const diff = event.clientX - startX.value;
    const newSize = Math.max(12, startWidth.value + diff); // Min 12px
    
    props.updateAttributes({
        size: `${Math.round(newSize)}px`
    });
};

const stopResize = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
};

onUnmounted(() => {
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
});
</script>
