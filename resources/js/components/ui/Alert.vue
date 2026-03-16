<template>
  <div :class="containerClass" class="rounded-xl border p-4">
    <div class="flex items-start gap-3">
      <div class="-mt-0.5 shrink-0" :class="iconClass">
        <Icon :icon="iconName" class="h-6 w-6" />
      </div>
      <div class="min-w-0 flex-1">
        <h4 v-if="title" class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">
          {{ title }}
        </h4>
        <p v-if="message" class="text-sm text-gray-500 dark:text-gray-400">{{ message }}</p>
        <a
          v-if="showLink"
          :href="linkHref"
          class="mt-3 inline-block text-sm font-medium text-gray-500 underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
        >
          {{ linkText }}
        </a>
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  variant: { type: String, default: 'info' },
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  showLink: { type: Boolean, default: false },
  linkHref: { type: String, default: '#' },
  linkText: { type: String, default: 'Learn more' },
});

const variantConfig = {
  success: {
    container: 'border-green-500 bg-green-50 dark:border-green-500/30 dark:bg-green-500/15',
    icon: 'text-green-500',
    iconName: 'mdi:check-circle',
  },
  error: {
    container: 'border-red-500 bg-red-50 dark:border-red-500/30 dark:bg-red-500/15',
    icon: 'text-red-500',
    iconName: 'mdi:alert-circle',
  },
  warning: {
    container: 'border-yellow-500 bg-yellow-50 dark:border-yellow-500/30 dark:bg-yellow-500/15',
    icon: 'text-yellow-500',
    iconName: 'mdi:alert',
  },
  amber: {
    container: 'border-amber-500 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/15',
    icon: 'text-amber-500',
    iconName: 'mdi:alert-circle',
  },
  info: {
    container: 'border-blue-500 bg-blue-50 dark:border-blue-500/30 dark:bg-blue-500/15',
    icon: 'text-blue-500',
    iconName: 'mdi:information-outline',
  },
};

const config = computed(() => variantConfig[props.variant] ?? variantConfig.info);

const containerClass = computed(() => config.value.container);
const iconClass = computed(() => config.value.icon);
const iconName = computed(() => config.value.iconName);
</script>
