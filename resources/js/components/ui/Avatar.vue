<template>
  <div class="relative shrink-0 rounded-full" :class="sizeClass">
    <img
      v-if="src"
      :src="src"
      :alt="alt"
      class="h-full w-full rounded-full object-cover"
    />
    <div
      v-else
      class="flex h-full w-full items-center justify-center rounded-full bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400"
      :class="sizeTextClass"
    >
      <slot name="placeholder">{{ fallbackText }}</slot>
    </div>
    <span
      v-if="status !== 'none'"
      class="absolute bottom-0 right-0 rounded-full border-[1.5px] border-white dark:border-gray-900"
      :class="[statusSizeClass, statusColorClass]"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: 'User Avatar' },
  size: { type: String, default: 'medium' },
  status: { type: String, default: 'none' },
  fallbackText: { type: String, default: '' },
});

const sizeClasses = {
  xsmall: 'h-6 w-6 max-w-6',
  small: 'h-8 w-8 max-w-8',
  medium: 'h-10 w-10 max-w-10',
  large: 'h-12 w-12 max-w-12',
  xlarge: 'h-14 w-14 max-w-14',
  xxlarge: 'h-16 w-16 max-w-16',
};

const statusSizeClasses = {
  xsmall: 'h-1.5 w-1.5 max-w-1.5',
  small: 'h-2 w-2 max-w-2',
  medium: 'h-2.5 w-2.5 max-w-2.5',
  large: 'h-3 w-3 max-w-3',
  xlarge: 'h-3.5 w-3.5 max-w-3.5',
  xxlarge: 'h-4 w-4 max-w-4',
};

const statusColorClasses = {
  online: 'bg-green-500',
  offline: 'bg-red-400',
  busy: 'bg-yellow-500',
};

const sizeClass = computed(() => sizeClasses[props.size] ?? sizeClasses.medium);
const sizeTextClass = computed(() => (props.size === 'xsmall' || props.size === 'small' ? 'text-xs' : 'text-sm'));
const statusSizeClass = computed(() => statusSizeClasses[props.size] ?? statusSizeClasses.medium);
const statusColorClass = computed(() => statusColorClasses[props.status] ?? '');
</script>
