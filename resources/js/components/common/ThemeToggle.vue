<template>
  <button
    type="button"
    class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
    aria-label="Alternar tema"
    @click="toggle"
  >
    <Icon v-if="theme === 'dark'" icon="mdi:weather-sunny" class="h-5 w-5" />
    <Icon v-else icon="mdi:weather-night" class="h-5 w-5" />
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';

const theme = ref('light');

function applyTheme(val) {
  if (val === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function toggle() {
  theme.value = theme.value === 'light' ? 'dark' : 'light';
  try {
    localStorage.setItem('theme', theme.value);
  } catch (_) {}
  applyTheme(theme.value);
}

onMounted(() => {
  try {
    const stored = localStorage.getItem('theme');
    const isDark = document.documentElement.classList.contains('dark');
    theme.value = stored ?? (isDark ? 'dark' : 'light');
  } catch (_) {
    theme.value = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
  }
  applyTheme(theme.value);
});
</script>
