<template>
  <div class="relative" ref="rootRef">
    <label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
      {{ label }}
    </label>
    <div
      class="flex min-h-11 cursor-pointer flex-wrap items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-theme-xs transition dark:border-gray-700 dark:bg-gray-900"
      @click="open = !open"
    >
      <template v-if="selectedIds.length">
        <div
          v-for="id in selectedIds"
          :key="id"
          class="group flex items-center justify-center rounded-full border border-transparent bg-gray-100 py-1 pl-2.5 pr-2 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800"
        >
          <span>{{ optionLabel(id) }}</span>
          <button type="button" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" @click.stop="toggle(id)">
            <Icon icon="mdi:close" class="h-3.5 w-3.5" />
          </button>
        </div>
      </template>
      <span v-else class="text-sm text-gray-500 dark:text-gray-400">{{ placeholder }}</span>
      <div class="ml-auto flex items-start pt-1.5">
        <Icon icon="mdi:chevron-down" class="h-5 w-5 shrink-0 text-gray-500 transition-transform dark:text-gray-400" :class="open ? 'rotate-180' : ''" />
      </div>
    </div>
    <div
      v-show="open"
      class="absolute top-full left-0 right-0 z-50 mt-1 max-h-64 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900"
    >
      <div class="max-h-64 overflow-y-auto">
        <div
          v-for="opt in options"
          :key="optValue(opt)"
          class="cursor-pointer border-b border-gray-200 px-4 py-3 text-sm transition last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5"
          @click="toggle(optValue(opt))"
        >
          <span class="text-gray-800 dark:text-white/90">{{ optionLabel(optValue(opt)) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, required: true }, // [{ id, name }] or [{ value, label }]
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Seleccione...' },
  valueKey: { type: String, default: 'id' },
  labelKey: { type: String, default: 'name' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const rootRef = ref(null);

const selectedIds = computed(() => props.modelValue || []);

function optValue(opt) {
  return typeof opt === 'object' && opt !== null ? (opt[props.valueKey] ?? opt.id ?? opt.value) : opt;
}

function optionLabel(val) {
  const opt = props.options.find((o) => optValue(o) === val);
  if (opt && typeof opt === 'object') return opt[props.labelKey] ?? opt.name ?? opt.label ?? val;
  return val;
}

function toggle(id) {
  const arr = [...(props.modelValue || [])];
  const idx = arr.indexOf(id);
  if (idx >= 0) arr.splice(idx, 1);
  else arr.push(id);
  emit('update:modelValue', arr);
}

function onClickOutside(e) {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>
