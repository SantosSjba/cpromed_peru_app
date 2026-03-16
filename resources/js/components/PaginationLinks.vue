<template>
  <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-white/[0.02] sm:flex-row">
    <p class="text-xs text-gray-500 dark:text-gray-400">
      Mostrando
      <span class="font-semibold text-gray-900 dark:text-white">{{ displayFrom }}</span>
      –
      <span class="font-semibold text-gray-900 dark:text-white">{{ displayTo }}</span>
      de
      <span class="font-semibold text-gray-900 dark:text-white">{{ displayTotal }}</span>
      {{ itemLabel }}
    </p>

    <!-- Modo servidor (Laravel): links con Inertia + iconos Iconify para anterior/siguiente -->
    <div v-if="links && links.length" class="flex flex-wrap items-center gap-1">
      <Link
        v-for="(link, linkIdx) in links"
        :key="'pag-' + linkIdx"
        :href="link.url || '#'"
        class="inline-flex min-w-[32px] items-center justify-center rounded-lg px-2.5 py-1.5 text-sm transition"
        :class="link.active
          ? 'bg-brand-500 text-white border border-brand-500'
          : link.url
            ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
            : 'cursor-not-allowed border border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500'"
      >
        <Icon v-if="isPrevLink(link.label)" icon="mdi:chevron-left" class="h-4 w-4" aria-hidden="true" />
        <Icon v-else-if="isNextLink(link.label)" icon="mdi:chevron-right" class="h-4 w-4" aria-hidden="true" />
        <span v-else class="leading-none">{{ link.label }}</span>
      </Link>
    </div>

    <!-- Modo cliente: botones primera / anterior / números / siguiente / última -->
    <div v-else class="flex items-center gap-1">
      <button
        type="button"
        :disabled="currentPage <= 1"
        aria-label="Primera"
        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 transition disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:hover:bg-white dark:disabled:hover:bg-gray-800"
        @click="$emit('update:page', 1)"
      >
        <Icon icon="mdi:chevron-double-left" class="h-3.5 w-3.5" />
      </button>
      <button
        type="button"
        :disabled="currentPage <= 1"
        aria-label="Anterior"
        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 transition disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:hover:bg-white dark:disabled:hover:bg-gray-800"
        @click="$emit('update:page', Math.max(1, currentPage - 1))"
      >
        <Icon icon="mdi:chevron-left" class="h-3.5 w-3.5" />
      </button>
      <template v-for="(p, pIdx) in pageNumbers" :key="'pag-' + pIdx">
        <button
          v-if="p !== '...'"
          type="button"
          :class="p === currentPage ? 'bg-brand-500 border-brand-500 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
          class="min-w-[32px] rounded-lg border px-2 py-1.5 text-xs font-medium transition"
          @click="$emit('update:page', p)"
        >
          {{ p }}
        </button>
        <span v-else class="cursor-default border-transparent bg-transparent px-2 py-1.5 text-gray-400">…</span>
      </template>
      <button
        type="button"
        :disabled="currentPage >= totalPages"
        aria-label="Siguiente"
        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 transition disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:hover:bg-white dark:disabled:hover:bg-gray-800"
        @click="$emit('update:page', Math.min(totalPages, currentPage + 1))"
      >
        <Icon icon="mdi:chevron-right" class="h-3.5 w-3.5" />
      </button>
      <button
        type="button"
        :disabled="currentPage >= totalPages"
        aria-label="Última"
        class="rounded-lg border border-gray-300 bg-white p-1.5 text-gray-500 transition disabled:opacity-40 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 disabled:hover:bg-white dark:disabled:hover:bg-gray-800"
        @click="$emit('update:page', totalPages)"
      >
        <Icon icon="mdi:chevron-double-right" class="h-3.5 w-3.5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
  /** Modo servidor Laravel: array de { url, label, active } */
  links: { type: Array, default: null },
  /** Modo servidor: from, to, total */
  from: { type: Number, default: 0 },
  to: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
  /** Modo cliente: página actual, total de páginas, y perPage para calcular from/to */
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  perPage: { type: Number, default: 10 },
  totalItems: { type: Number, default: 0 },
  /** Etiqueta: "resultados", "nota(s)", "paciente(s)", etc. */
  itemLabel: { type: String, default: 'resultados' },
});

defineEmits(['update:page']);

function isPrevLink(label) {
  if (typeof label !== 'string') return false;
  const t = label.trim();
  return t === 'pagination.previous' || t === 'Previous' || /&laquo;|anterior|previous/i.test(t);
}
function isNextLink(label) {
  if (typeof label !== 'string') return false;
  const t = label.trim();
  return t === 'pagination.next' || t === 'Next' || /&raquo;|siguiente|next/i.test(t);
}

const displayFrom = computed(() => {
  if (props.links && props.from > 0) return props.from;
  if (props.totalItems <= 0) return 0;
  return (props.currentPage - 1) * props.perPage + 1;
});
const displayTo = computed(() => {
  if (props.links && props.to > 0) return props.to;
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});
const displayTotal = computed(() => {
  if (props.links && props.total > 0) return props.total;
  return props.totalItems;
});

const pageNumbers = computed(() => {
  if (props.links && props.links.length) return [];
  const total = Math.max(1, props.totalPages);
  const cur = Math.max(1, Math.min(total, props.currentPage));
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages = [];
  if (cur <= 4) {
    pages.push(1, 2, 3, 4, 5, '...', total);
  } else if (cur >= total - 3) {
    pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total);
  } else {
    pages.push(1, '...', cur - 1, cur, cur + 1, '...', total);
  }
  return pages;
});
</script>
