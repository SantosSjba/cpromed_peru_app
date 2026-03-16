<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Notas de venta', url: null }]" />
      <div v-if="flash.success" class="rounded-xl border border-green-200 bg-green-50/80 p-4 text-sm font-medium text-green-800 shadow-sm dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
        {{ flash.success }}
      </div>
      <div v-if="flash.error" class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm font-medium text-amber-800 shadow-sm dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
        {{ flash.error }}
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div></div>
        <Link
          href="/notas-venta/create"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600 hover:shadow-brand-500/30"
        >
          <Icon icon="mdi:plus" class="h-5 w-5" />
          Nueva nota de venta
        </Link>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <form @submit.prevent="submitSearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <label for="buscar-notas" class="sr-only">Buscar por cliente o número de documento</label>
          <input
            id="buscar-notas"
            v-model="buscar"
            type="search"
            placeholder="Buscar por cliente o número de documento..."
            @input="onBuscarInput"
            class="flex-1 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-900 shadow-sm placeholder-gray-500 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
          />
          <button
            type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600"
          >
            <Icon icon="mdi:magnify" class="h-4 w-4" />
            Buscar
          </button>
          <Link
            v-if="buscar.trim()"
            href="/notas-venta"
            class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
          >
            Limpiar
          </Link>
        </form>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">La búsqueda se ejecuta al enviar el formulario.</p>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Nº Documento</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Cliente</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">DNI/RUC</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Fecha</th>
                <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200 text-right">Total</th>
                <th class="px-5 py-4 text-right font-semibold text-gray-700 dark:text-gray-200">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="nota in notas.data" :key="nota.id" class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-5 py-4 font-medium text-gray-900 dark:text-white">{{ nota.numero_documento }}</td>
                <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ (nota.cliente && nota.cliente.nombre) || '—' }}</td>
                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ (nota.cliente && nota.cliente.dni_ruc) || '—' }}</td>
                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(nota.created_at) }}</td>
                <td class="px-5 py-4 text-right font-semibold text-gray-900 dark:text-white">S/ {{ formatNum(nota.total) }}</td>
                <td class="px-5 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="'/ver-nota-venta?id=' + nota.id" class="rounded-lg px-3 py-1.5 font-medium text-brand-600 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Ver</Link>
                    <a :href="'/descargar-nota-pdf?id=' + nota.id" class="rounded-lg px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">PDF</a>
                    <button type="button" @click="confirmDelete(nota)" class="rounded-lg px-3 py-1.5 font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Eliminar</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!notas.data || notas.data.length === 0">
                <td colspan="6" class="px-5 py-12 text-center">
                  <p class="text-gray-500 dark:text-gray-400">No hay notas de venta.</p>
                  <Link href="/notas-venta/create" class="mt-3 inline-block font-medium text-brand-500 hover:underline">Crear primera nota de venta</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <PaginationLinks
          v-if="notas.total > 0"
          :links="notas.links"
          :from="notas.from"
          :to="notas.to"
          :total="notas.total"
          item-label="nota(s)"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '../../components/PageBreadcrumb.vue';
import PaginationLinks from '../../components/PaginationLinks.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title: { type: String, default: 'Notas de venta' },
  notas: { type: Object, required: true },
  buscar: { type: String, default: '' },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const buscar = ref(props.buscar);

watch(() => props.buscar, (val) => { buscar.value = val ?? ''; });

let debounceTimer = null;
function onBuscarInput() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    router.get('/notas-venta', { buscar: (buscar.value || '').trim() }, { preserveState: true });
  }, 400);
}

function submitSearch() {
  clearTimeout(debounceTimer);
  router.get('/notas-venta', { buscar: buscar.value }, { preserveState: true });
}

function formatDate(val) {
  if (!val) return '—';
  const d = new Date(val);
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatNum(n) {
  return Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function confirmDelete(nota) {
  if (!confirm('¿Eliminar esta nota de venta?')) return;
  router.post('/eliminar-nota-venta', { id: nota.id, _token: page.props.csrf_token ?? '' });
}
</script>
