<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Nota {{ nota.numero_documento }}
          </h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Cliente: {{ (nota.cliente && nota.cliente.nombre) || '—' }} · Total S/ {{ formatNum(nota.total) }}
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            :href="'/descargar-nota-pdf?id=' + nota.id"
            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600"
          >
            <Icon icon="mdi:file-pdf-box" class="h-5 w-5" />
            Descargar PDF
          </a>
          <Link href="/notas-venta" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
            Volver al listado
          </Link>
          <Button type="button" variant="outlineDanger" @click="confirmDelete">Eliminar</Button>
        </div>
      </div>

      <Card no-header>
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Datos generales</h2>
        </div>
        <dl class="grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nº Documento</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ nota.numero_documento }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ (nota.cliente && nota.cliente.nombre) || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DNI/RUC</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ (nota.cliente && nota.cliente.dni_ruc) || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección cliente</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ (nota.cliente && nota.cliente.direccion) || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Razón social</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ nota.razon_social || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha emisión</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatBoletaDate(nota.boleta && nota.boleta.fecha_emision) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Moneda / Forma de pago</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ (nota.boleta && nota.boleta.moneda) || 'Soles' }} · {{ (nota.boleta && nota.boleta.forma_pago) || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">S/ {{ formatNum(nota.total) }}</dd>
          </div>
        </dl>
        <div v-if="nota.notas" class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">
          <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</dt>
          <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ nota.notas }}</dd>
        </div>
      </Card>

      <Card v-if="nota.detalles && nota.detalles.length > 0" no-header no-padding class="overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50/80 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/80">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalles</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Cant.</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">P. unit.</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Dscto.</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Importe</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="(d, i) in nota.detalles" :key="i" class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-5 py-3 text-gray-900 dark:text-white">{{ d.descripcion || '' }}</td>
                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatNum(d.cantidad) }}</td>
                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatNum(d.precio_unitario) }}</td>
                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ formatNum(d.descuento_unitario) }}</td>
                <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">{{ formatNum(d.importe_total_unitario) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex flex-wrap justify-end gap-6 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
          <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal</span>
          <span class="text-sm font-medium text-gray-900 dark:text-white">S/ {{ formatNum(nota.subtotal) }}</span>
          <span class="text-sm text-gray-500 dark:text-gray-400">IGV</span>
          <span class="text-sm font-medium text-gray-900 dark:text-white">S/ {{ formatNum(nota.igv) }}</span>
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Total</span>
          <span class="text-lg font-semibold text-gray-900 dark:text-white">S/ {{ formatNum(nota.total) }}</span>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button } from '@/components/ui';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title: { type: String, default: 'Nota de venta' },
  nota: { type: Object, required: true },
});

const breadcrumbItems = computed(() => [
  { label: 'Notas de venta', url: '/notas-venta' },
  { label: 'Nota ' + (props.nota?.numero_documento ?? ''), url: null },
]);

function formatNum(n) {
  return Number(n ?? 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatBoletaDate(val) {
  if (!val) return '—';
  const d = new Date(val);
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function confirmDelete() {
  if (!confirm('¿Eliminar esta nota de venta?')) return;
  router.post('/eliminar-nota-venta', { id: props.nota.id });
}
</script>
