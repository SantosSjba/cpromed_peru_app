<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">
    <h1 class="text-6xl font-bold text-gray-300 dark:text-gray-600">404</h1>
    <p class="mt-2 text-lg font-medium text-gray-600 dark:text-gray-400">Página no encontrada</p>
    <Link href="/" class="mt-6 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-brand-600">Ir al inicio</Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ title: { type: String, default: 'Error 404' } });
</script>
