<template>
  <label
    :for="checkboxId"
    class="flex cursor-pointer select-none items-center text-sm font-medium text-gray-700 dark:text-gray-400"
  >
    <div class="relative">
      <input
        :id="checkboxId"
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        class="sr-only peer"
        v-bind="$attrs"
        @change="$emit('update:modelValue', $event.target?.checked ?? false)"
      />
      <div
        class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-colors hover:border-brand-500 dark:hover:border-brand-500"
        :class="boxClasses"
      >
        <span :class="modelValue ? '' : 'opacity-0'">
          <Icon icon="mdi:check" class="h-3.5 w-3.5 text-white" />
        </span>
      </div>
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
  checkboxId: { type: String, default: () => 'checkbox-' + Math.random().toString(36).slice(2) },
});

defineEmits(['update:modelValue']);

const boxClasses = computed(() => {
  if (props.disabled) {
    return 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03]';
  }
  return props.modelValue
    ? 'border-brand-500 bg-brand-500'
    : 'border-gray-300 bg-transparent dark:border-gray-700';
});
</script>
