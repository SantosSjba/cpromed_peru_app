<template>
  <span :class="badgeClasses">
    <Icon v-if="startIcon" :icon="startIcon" class="h-3.5 w-3.5" />
    <slot />
    <Icon v-if="endIcon" :icon="endIcon" class="h-3.5 w-3.5" />
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  variant: { type: String, default: 'light' },
  size: { type: String, default: 'md' },
  color: { type: String, default: 'primary' },
  startIcon: { type: String, default: null },
  endIcon: { type: String, default: null },
});

const baseStyles = 'inline-flex items-center px-2.5 py-0.5 justify-center gap-1 rounded-full font-medium capitalize';

const sizeStyles = {
  sm: 'text-xs',
  md: 'text-sm',
};

const variants = {
  light: {
    primary: 'bg-blue-50 text-blue-500 dark:bg-blue-500/15 dark:text-blue-400',
    success: 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500',
    error: 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500',
    warning: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-orange-400',
    info: 'bg-sky-50 text-sky-500 dark:bg-sky-500/15 dark:text-sky-500',
    light: 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-white/80',
    dark: 'bg-gray-500 text-white dark:bg-white/5 dark:text-white',
  },
  solid: {
    primary: 'bg-blue-500 text-white dark:text-white',
    success: 'bg-green-500 text-white dark:text-white',
    error: 'bg-red-500 text-white dark:text-white',
    warning: 'bg-yellow-500 text-white dark:text-white',
    info: 'bg-sky-500 text-white dark:text-white',
    light: 'bg-gray-400 dark:bg-white/5 text-white dark:text-white/80',
    dark: 'bg-gray-700 text-white dark:text-white',
  },
};

const badgeClasses = computed(() => {
  const sizeClass = sizeStyles[props.size] ?? sizeStyles.md;
  const colorMap = variants[props.variant] ?? variants.light;
  const colorClass = colorMap[props.color] ?? colorMap.primary;
  return [baseStyles, sizeClass, colorClass].join(' ');
});
</script>
