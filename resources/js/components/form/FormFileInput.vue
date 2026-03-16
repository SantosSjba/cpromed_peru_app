<template>
  <div>
    <label v-if="label" :for="inputId" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}
    </label>
    <input
      :id="inputId"
      type="file"
      class="h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 shadow-theme-xs transition-colors file:mr-5 file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 hover:file:bg-gray-100 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400"
      :accept="accept"
      :multiple="multiple"
      v-bind="$attrs"
      @change="$emit('change', $event); $emit('update:modelValue', $event.target?.files)"
    />
  </div>
</template>

<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
  label: { type: String, default: '' },
  accept: { type: String, default: '' },
  multiple: { type: Boolean, default: false },
  inputId: { type: String, default: () => 'file-' + Math.random().toString(36).slice(2) },
});

defineEmits(['change', 'update:modelValue']);
</script>
