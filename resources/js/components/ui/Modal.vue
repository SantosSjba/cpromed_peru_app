<template>
  <Teleport to="body">
    <div
      v-show="modelValue"
      class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5"
      role="dialog"
      aria-modal="true"
      :aria-hidden="!modelValue"
    >
      <!-- Backdrop -->
      <div
        class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] dark:bg-black/50"
        :class="backdropTransition"
        @click="close"
      />

      <!-- Content -->
      <div
        :class="[contentClass, contentTransition]"
        class="relative w-full rounded-3xl bg-white dark:bg-gray-900"
        @click.stop
      >
        <button
          v-if="showCloseButton"
          type="button"
          class="absolute right-3 top-3 z-[999] flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11"
          aria-label="Cerrar"
          @click="close"
        >
          <Icon icon="mdi:close" class="h-5 w-5" />
        </button>
        <div>
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { watch, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  showCloseButton: { type: Boolean, default: true },
  contentClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const close = () => emit('update:modelValue', false);

const backdropTransition = 'transition ease-out duration-300';
const contentTransition = 'transition ease-out duration-300';

watch(() => props.modelValue, (value) => {
  document.body.style.overflow = value ? 'hidden' : '';
});

function onEscape(e) {
  if (e.key === 'Escape') close();
}

onMounted(() => {
  window.addEventListener('keydown', onEscape);
});

onUnmounted(() => {
  window.removeEventListener('keydown', onEscape);
  document.body.style.overflow = '';
});
</script>

<style scoped>
[v-cloak] { display: none; }
</style>
