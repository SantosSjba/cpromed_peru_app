<template>
  <div class="relative" ref="rootRef">
    <div class="cursor-pointer" @click="open = !open">
      <slot name="button" />
    </div>
    <div
      v-show="open"
      class="absolute right-0 top-full z-50 mt-1 w-40 overflow-hidden rounded-2xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-800 dark:bg-gray-900"
    >
      <div class="space-y-1" role="menu" aria-orientation="vertical">
        <slot name="content" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const open = ref(false);
const rootRef = ref(null);

function onClickOutside(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>
