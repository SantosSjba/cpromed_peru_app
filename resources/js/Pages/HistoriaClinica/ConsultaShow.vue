<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-2xl font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">{{ iniciales }}</div>
            <div>
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ paciente.nombres }} {{ paciente.apellidos }}</h1>
              <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Consulta del {{ formatDate(consulta.fecha_consulta) }} a las {{ formatTime(consulta.fecha_consulta) }}</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <Link :href="'/editar-consulta?paciente=' + paciente.id + '&consulta=' + consulta.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Editar</Link>
            <button type="button" @click="confirmDelete" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400">Eliminar consulta</button>
            <Link :href="'/ver-historia-clinica?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Volver a historia clínica</Link>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700"><h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalle de la consulta</h2></div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Motivo de consulta</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.motivo_consulta || '—' }}</p></div>
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Enfermedad actual</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.enfermedad_actual || '—' }}</p></div>
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dx (Diagnóstico)</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.dx || '—' }}</p></div>
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tx (Tratamiento)</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.tx || '—' }}</p></div>
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Plan Dx.</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.plan_dx || '—' }}</p></div>
          <div class="px-6 py-4"><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Recomendaciones</p><p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ consulta.recomendaciones || '—' }}</p></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '../../components/PageBreadcrumb.vue';

const props = defineProps({
  title: { type: String, default: 'Consulta' },
  paciente: { type: Object, required: true },
  consulta: { type: Object, required: true },
});

const page = usePage();
const breadcrumbItems = computed(() => {
  const pacId = props.paciente?.id;
  const labelPac = (props.paciente?.nombres ?? '') + ' ' + (props.paciente?.apellidos ?? '');
  const consultaFecha = props.consulta?.fecha_consulta ? new Date(props.consulta.fecha_consulta).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
  return [
    { label: 'Historia clínica', url: '/historia-clinica' },
    { label: labelPac, url: '/ver-historia-clinica?id=' + pacId },
    { label: 'Consulta ' + consultaFecha, url: null },
  ];
});
const iniciales = computed(() => {
  const n = (props.paciente.nombres || '').trim()[0] || '';
  const a = (props.paciente.apellidos || '').trim()[0] || '';
  return (n + a).toUpperCase() || '—';
});

function formatDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
function formatTime(val) {
  if (!val) return '—';
  return new Date(val).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}
function confirmDelete() {
  if (!confirm('¿Eliminar esta consulta?')) return;
  router.post('/eliminar-consulta', { paciente_id: props.paciente.id, consulta_id: props.consulta.id, _token: page.props.csrf_token ?? '' });
}
</script>
