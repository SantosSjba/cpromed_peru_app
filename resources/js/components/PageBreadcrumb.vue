<template>
  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
      {{ pageTitleDisplay }}
    </h2>
    <nav aria-label="Breadcrumb">
      <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        <li>
          <Link href="/" class="text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">Home</Link>
        </li>
        <template v-for="(item, index) in items" :key="index">
          <li class="text-gray-400 dark:text-gray-500" aria-hidden="true">/</li>
          <li>
            <Link v-if="item.url" :href="item.url" class="text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">{{ item.label }}</Link>
            <span v-else class="font-medium text-gray-800 dark:text-white/90">{{ item.label }}</span>
          </li>
        </template>
      </ol>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  pageTitle: { type: String, default: 'Page' },
  items: { type: Array, default: () => [] },
});

const pageTitleDisplay = computed(() => {
  if (props.items && props.items.length) {
    const last = props.items[props.items.length - 1];
    return last?.label ?? props.pageTitle;
  }
  return props.pageTitle;
});
</script>
