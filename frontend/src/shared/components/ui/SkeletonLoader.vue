<template>
  <div
    class="skeleton-loader"
    :class="[type, { 'animate-pulse': animate }]"
  >
    <!-- Post Card Skeleton -->
    <div
      v-if="type === 'post-card'"
      class="skeleton-post-card"
    >
      <div class="skeleton-image" />
      <div class="skeleton-content">
        <div class="skeleton-line skeleton-title" />
        <div class="skeleton-line skeleton-line-short" />
        <div class="skeleton-line skeleton-line-short" />
        <div class="skeleton-meta">
          <div class="skeleton-avatar" />
          <div class="skeleton-line skeleton-meta-line" />
        </div>
      </div>
    </div>

    <!-- Table Row Skeleton -->
    <div
      v-else-if="type === 'table-row'"
      class="skeleton-table-row"
    >
      <div
        v-for="i in columns"
        :key="i"
        class="skeleton-line skeleton-cell"
      />
    </div>

    <!-- List Item Skeleton -->
    <div
      v-else-if="type === 'list-item'"
      class="skeleton-list-item"
    >
      <div class="skeleton-avatar" />
      <div class="skeleton-content">
        <div class="skeleton-line skeleton-line-medium" />
        <div class="skeleton-line skeleton-line-short" />
      </div>
    </div>

    <!-- Card Skeleton -->
    <div
      v-else-if="type === 'card'"
      class="skeleton-card"
    >
      <div class="skeleton-line skeleton-title" />
      <div class="skeleton-line" />
      <div class="skeleton-line skeleton-line-short" />
    </div>

    <!-- Generic Skeleton -->
    <div
      v-else
      class="skeleton-generic"
    >
      <div
        v-for="i in lines"
        :key="i"
        class="skeleton-line"
        :class="getLineClass(i)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  type?: 'post-card' | 'table-row' | 'list-item' | 'card' | 'generic';
  lines?: number;
  columns?: number;
  animate?: boolean;
}>(), {
  type: 'generic',
  lines: 3,
  columns: 4,
  animate: true,
});

const getLineClass = (index: number) => {
  const classes: string[] = [];
  if (index === "1") classes.push('skeleton-title');
  if (index === props.lines) classes.push('skeleton-line-short');
  return classes;
};
</script>

<style scoped>
.skeleton-loader {
  width: 100%;
}

/* Base skeleton styles */
.skeleton-line,
.skeleton-image,
.skeleton-avatar,
.skeleton-cell {
  background-color: #e5e7eb;
  border-radius: 0.25rem;
}

.dark .skeleton-line,
.dark .skeleton-image,
.dark .skeleton-avatar,
.dark .skeleton-cell {
  background-color: #374151;
}

.skeleton-line {
  height: 1rem;
  margin-bottom: 0.5rem;
}

.skeleton-title {
  height: 1.5rem;
  margin-bottom: 0.75rem;
}

.skeleton-line-medium {
  width: 75%;
}

.skeleton-line-short {
  width: 50%;
}

.skeleton-image {
  width: 100%;
  height: 12rem;
  margin-bottom: 1rem;
}

.skeleton-avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 9999px;
}

/* Post Card Skeleton */
.skeleton-post-card {
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.dark .skeleton-post-card {
  background-color: #1f2937;
}

.skeleton-content {
  padding: 1rem;
}

.skeleton-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
}

.skeleton-meta-line {
  height: 0.75rem;
  width: 6rem;
}

/* Table Row Skeleton */
.skeleton-table-row {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.dark .skeleton-table-row {
  border-bottom-color: #374151;
}

.skeleton-cell {
  height: 1rem;
  flex: 1;
}

/* List Item Skeleton */
.skeleton-list-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.dark .skeleton-list-item {
  border-bottom-color: #374151;
}

/* Card Skeleton */
.skeleton-card {
  background-color: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.dark .skeleton-card {
  background-color: #1f2937;
}

/* Animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

