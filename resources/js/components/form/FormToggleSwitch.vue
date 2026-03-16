<template>
  <label
    :for="toggleId"
    class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium dark:text-gray-400"
    :class="disabled ? 'cursor-not-allowed text-gray-400' : 'text-gray-700'"
  >
    <div class="relative">
      <input
        :id="toggleId"
        type="checkbox"
        class="sr-only"
        :checked="modelValue"
        :disabled="disabled"
        v-bind="$attrs"
        @change="$emit('update:modelValue', $event.target?.checked ?? false)"
      />
      <div
        class="block h-6 w-11 rounded-full transition-colors"
        :class="trackClass"
      />
      <div
        class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm transition-transform duration-300 ease-linear"
        :class="modelValue ? 'translate-x-full' : 'translate-x-0'"
        :style="thumbStyle"
      />
    </div>
    <slot />
  </label>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  variant: { type: String, default: 'brand' }, // 'brand' | 'gray'
  toggleId: { type: String, default: () => 'toggle-' + Math.random().toString(36).slice(2) },
});

defineEmits(['update:modelValue']);

const trackClass = computed(() => {
  if (props.disabled) return 'bg-gray-100 dark:bg-gray-800';
  if (props.variant === 'gray') {
    return props.modelValue ? 'bg-gray-700 dark:bg-white/10' : 'bg-gray-200 dark:bg-gray-800';
  }
  return props.modelValue ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10';
});

const thumbStyle = computed(() => (props.disabled ? { backgroundColor: 'rgb(249 250 251)' } : {}));
</script>
