<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
      <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/50 p-8 text-center dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Página en construcción (Vue).</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from './Layouts/AppLayout.vue';

defineProps({
  title: { type: String, default: 'Página' },
});
</script>
