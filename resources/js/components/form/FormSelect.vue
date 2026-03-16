<template>
  <div :class="wrapperClass">
    <label v-if="label" :for="selectId" :class="labelClasses">
      {{ label }}
    </label>
    <div class="relative">
      <select
        :id="selectId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="selectClasses"
        v-bind="$attrs"
        @change="$emit('update:modelValue', ($event.target && $event.target.value) ?? '')"
      >
        <option v-if="placeholder" value="" disabled>
          {{ placeholder }}
        </option>
        <slot />
      </select>
      <span class="pointer-events-none absolute right-3 top-1/2 z-10 flex h-5 w-5 -translate-y-1/2 items-center justify-center text-gray-500 dark:text-gray-400">
        <Icon icon="mdi:chevron-down" class="h-5 w-5 shrink-0" />
      </span>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  selectId: { type: String, default: () => 'select-' + Math.random().toString(36).slice(2) },
  wrapperClass: { type: String, default: '' },
  size: { type: String, default: 'md' },
  labelClass: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const labelClasses = computed(() =>
  props.labelClass ? props.labelClass : 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400'
);

const sizeClasses = { md: 'h-11 py-2.5 pl-4 pr-10 text-sm', sm: 'h-9 py-1.5 pl-3 pr-8 text-xs' };
const selectClasses = computed(() => {
  const size = sizeClasses[props.size] || sizeClasses.md;
  let c =
    'w-full min-w-0 cursor-pointer appearance-none rounded-lg border border-gray-300 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 ' +
    size +
    ' text-gray-800';
  if (props.error) c += ' border-red-500 focus:border-red-500 focus:ring-red-500/10 dark:border-red-500';
  if (props.disabled) c += ' cursor-not-allowed opacity-60';
  return c;
});
</script>
