<template>
  <div class="relative h-fit" ref="rootRef">
    <button
      type="button"
      :class="open ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
      @click="open = !open"
    >
      <Icon icon="mdi:dots-vertical" class="h-6 w-6" />
    </button>
    <div
      v-show="open"
      class="absolute right-0 top-full z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900"
    >
      <template v-if="items && items.length">
        <button
          v-for="(item, i) in items"
          :key="i"
          type="button"
          class="flex w-full rounded-lg px-3 py-2 text-left text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
          @click="onItemClick(item, $event)"
        >
          {{ typeof item === 'object' && item !== null && 'label' in item ? item.label : item }}
        </button>
      </template>
      <slot v-else />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  items: { type: Array, default: () => [] },
});

const emit = defineEmits(['select']);

const open = ref(false);
const rootRef = ref(null);

function onItemClick(item, e) {
  e.stopPropagation();
  emit('select', typeof item === 'object' && item !== null && 'value' in item ? item.value : item);
  open.value = false;
}

function onClickOutside(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>
