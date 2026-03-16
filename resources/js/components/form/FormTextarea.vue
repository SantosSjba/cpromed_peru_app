<template>
  <div>
    <label v-if="label" :for="textareaId" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}
    </label>
    <textarea
      :id="textareaId"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :rows="rows"
      :class="textareaClasses"
      v-bind="$attrs"
      @input="$emit('update:modelValue', ($event.target && $event.target.value) ?? '')"
    />
    <p v-if="error" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  rows: { type: [Number, String], default: 4 },
  error: { type: String, default: '' },
  textareaId: { type: String, default: () => 'textarea-' + Math.random().toString(36).slice(2) },
});

defineEmits(['update:modelValue']);

const textareaClasses =
  'w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800' +
  (props.error ? ' border-red-500 focus:border-red-500 focus:ring-red-500/10 dark:border-red-500' : '') +
  (props.disabled ? ' disabled:border-gray-100 disabled:bg-gray-50 dark:disabled:border-gray-800 dark:disabled:bg-white/[0.03]' : '');
</script>
