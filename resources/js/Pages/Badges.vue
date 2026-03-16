<template>
  <AppLayout>
    <div class="space-y-5">
      <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <p class="text-sm text-gray-500 dark:text-gray-400">Badges (Vue + Tailwind).</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from './Layouts/AppLayout.vue';
defineProps({ title: { type: String, default: 'Badges' } });
</script>
