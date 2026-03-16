<template>
  <label
    :for="radioId"
    class="relative flex cursor-pointer select-none items-center gap-3 text-sm font-medium"
    :class="disabled ? 'cursor-not-allowed text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-400'"
  >
    <input
      :id="radioId"
      type="radio"
      :name="name"
      :value="value"
      :checked="isChecked"
      :disabled="disabled"
      class="sr-only"
      v-bind="$attrs"
      @change="$emit('update:modelValue', value)"
    />
    <span
      class="flex h-5 w-5 items-center justify-center rounded-full border-[1.25px] transition-colors"
      :class="circleClass"
    >
      <span
        class="h-2 w-2 rounded-full bg-white"
        :class="isChecked ? 'block' : 'hidden'"
      />
    </span>
    <slot />
  </label>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: [String, Number, Boolean], default: undefined },
  name: { type: String, default: '' },
  value: { type: [String, Number, Boolean], required: true },
  disabled: { type: Boolean, default: false },
  radioId: { type: String, default: () => 'radio-' + Math.random().toString(36).slice(2) },
});

defineEmits(['update:modelValue']);

const isChecked = computed(() => props.modelValue === props.value);

const circleClass = computed(() => {
  if (props.disabled) return 'border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-700';
  if (isChecked.value) return 'border-brand-500 bg-brand-500';
  return 'border-gray-300 bg-transparent dark:border-gray-700 hover:border-brand-500 dark:hover:border-brand-500';
});
</script>
