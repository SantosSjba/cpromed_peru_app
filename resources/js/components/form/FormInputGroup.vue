<template>
  <div>
    <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}
    </label>
    <div class="relative flex">
      <div v-if="hasPrefix" class="flex shrink-0 items-center rounded-l-lg border-r border-gray-200 pl-3.5 pr-3 text-gray-500 dark:border-gray-800 dark:text-gray-400">
        <slot name="prefix" />
      </div>
      <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="inputClass"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target?.value)"
      />
      <div v-if="hasSuffix" class="flex shrink-0 items-center rounded-r-lg border-l border-gray-200 pl-3 pr-3.5 text-gray-500 dark:border-gray-800 dark:text-gray-400">
        <slot name="suffix" />
      </div>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const slots = useSlots();
const hasPrefix = computed(() => !!slots.prefix);
const hasSuffix = computed(() => !!slots.suffix);

const inputClass = computed(() => {
  let c =
    'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800';
  if (hasPrefix.value) c += ' rounded-l-none pl-3';
  if (hasSuffix.value) c += ' rounded-r-none pr-3';
  if (props.error) c += ' border-red-500 focus:border-red-500 focus:ring-red-500/10';
  return c;
});
</script>
