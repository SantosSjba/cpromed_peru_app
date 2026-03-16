<template>
  <div :class="wrapperClass">
    <label v-if="label" :for="inputId" :class="labelClasses">
      {{ label }}
    </label>
    <div class="relative" @click="onDateWrapperClick">
      <input
        ref="inputRef"
        :id="inputId"
        :type="effectiveType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :min="min"
        :max="max"
        :step="step"
        :class="inputClasses"
        v-bind="$attrs"
        @input="$emit('update:modelValue', ($event.target && $event.target.value) ?? '')"
      />
      <!-- Date/datetime: icon calendario; clic abre el selector nativo -->
      <span
        v-if="isDateType && !readonly && !disabled"
        class="pointer-events-none absolute right-4 top-1/2 z-10 -translate-y-1/2 text-gray-500 dark:text-gray-400"
        aria-hidden="true"
      >
        <Icon icon="mdi:calendar" class="h-5 w-5" />
      </span>
      <!-- Password toggle -->
      <button
        v-if="type === 'password'"
        type="button"
        class="absolute right-4 top-1/2 z-10 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
        tabindex="-1"
        @click="showPassword = !showPassword"
      >
        <Icon :icon="showPassword ? 'mdi:eye-off' : 'mdi:eye'" class="h-5 w-5" />
      </button>
    </div>
    <p v-if="error" class="mt-1 text-xs text-red-500 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  inputId: { type: String, default: () => 'input-' + Math.random().toString(36).slice(2) },
  wrapperClass: { type: String, default: '' },
  inputClass: { type: String, default: '' },
  labelClass: { type: String, default: '' },
  min: { type: String, default: '' },
  max: { type: String, default: '' },
  step: { type: [String, Number], default: undefined },
  size: { type: String, default: 'md' },
});

defineEmits(['update:modelValue']);

const showPassword = ref(false);
const inputRef = ref(null);

function onDateWrapperClick() {
  if (!isDateType.value || props.disabled || props.readonly) return;
  const el = inputRef.value;
  if (el && typeof el.showPicker === 'function') {
    el.focus();
    el.showPicker();
  }
}

const effectiveType = computed(() => (showPassword.value && props.type === 'password' ? 'text' : props.type));

const isDateType = computed(() => ['date', 'datetime-local', 'month', 'time'].includes(props.type));

const labelClasses = computed(() => {
  const base = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
  return props.labelClass ? props.labelClass : base;
});

const sizeClasses = { md: 'h-11 px-4 py-2.5 text-sm', sm: 'h-9 px-3 py-1.5 text-xs' };
const inputClasses = computed(() => {
  const size = sizeClasses[props.size] || sizeClasses.md;
  let c =
    'w-full rounded-lg border border-gray-300 bg-white shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 ' +
    size +
    ' text-gray-800';
  // Date/datetime: espacio a la derecha para icono y para que el selector nativo se vea/clique
  if (isDateType.value) c += ' pr-11 [color-scheme:light] dark:[color-scheme:dark]';
  if (props.error) c += ' border-red-500 focus:border-red-500 focus:ring-red-500/10 dark:border-red-500';
  if (props.disabled) c += ' disabled:border-gray-100 disabled:bg-gray-50 disabled:placeholder:text-gray-300 dark:disabled:border-gray-800 dark:disabled:bg-white/[0.03] dark:disabled:placeholder:text-white/15';
  if (props.readonly) c += ' bg-gray-100 dark:bg-gray-800 cursor-not-allowed';
  if (props.inputClass) c += ' ' + props.inputClass;
  return c;
});
</script>
