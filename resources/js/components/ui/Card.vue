<template>
  <div :class="cardClass">
    <div v-if="!noHeader && (title || desc || $slots.header)" class="px-6 py-5">
      <slot name="header">
        <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
          {{ title }}
        </h3>
        <p v-if="desc" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ desc }}
        </p>
      </slot>
    </div>
    <div :class="bodyClass">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, default: '' },
  desc: { type: String, default: '' },
  class: { type: String, default: '' },
  noHeader: { type: Boolean, default: false },
  noPadding: { type: Boolean, default: false },
});

const cardClass = computed(() => {
  const base = 'rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]';
  return [base, props.class].filter(Boolean).join(' ');
});

const bodyClass = computed(() => {
  if (props.noPadding) return '';
  const hasHeader = !props.noHeader && (props.title || props.desc);
  return hasHeader
    ? 'space-y-6 border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6'
    : 'p-4 sm:p-6';
});
</script>
