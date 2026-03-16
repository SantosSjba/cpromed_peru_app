<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="buttonClasses"
  >
    <span v-if="$slots.startIcon || startIcon" class="flex items-center">
      <slot name="startIcon" />
      <Icon v-if="startIcon && !$slots.startIcon" :icon="startIcon" class="h-4 w-4" />
    </span>
    <slot />
    <span v-if="$slots.endIcon || endIcon" class="flex items-center">
      <slot name="endIcon" />
      <Icon v-if="endIcon && !$slots.endIcon" :icon="endIcon" class="h-4 w-4" />
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  size: { type: String, default: 'md' },
  variant: { type: String, default: 'primary' },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
  startIcon: { type: String, default: null },
  endIcon: { type: String, default: null },
  className: { type: String, default: '' },
});

const sizeMap = {
  sm: 'px-4 py-3 text-sm',
  md: 'px-5 py-3.5 text-sm',
};

const variantMap = {
  primary: 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300 dark:disabled:opacity-50',
  outline: 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] dark:hover:text-gray-300',
  danger: 'bg-red-500 text-white shadow-theme-xs hover:bg-red-600 disabled:bg-red-300 dark:disabled:opacity-50',
  outlineDanger: 'bg-white text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:ring-red-700 dark:hover:bg-red-500/10',
};

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium gap-2 rounded-lg transition';
  const sizeClass = sizeMap[props.size] ?? sizeMap.md;
  const variantClass = variantMap[props.variant] ?? variantMap.primary;
  const disabledClass = props.disabled ? 'cursor-not-allowed opacity-50' : '';
  return [base, sizeClass, variantClass, props.className, disabledClass].filter(Boolean).join(' ');
});
</script>
