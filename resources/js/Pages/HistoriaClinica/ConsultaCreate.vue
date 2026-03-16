<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.03] lg:p-6">
      <ul v-if="Object.keys(form.errors).length" class="mb-4 list-inside list-disc rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
      </ul>
      <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Paciente: <strong>{{ paciente.nombres }} {{ paciente.apellidos }}</strong>. Esta consulta se guardará como una nueva fila en la tabla de consultas.</p>
      <form @submit.prevent="form.post('/guardar-consulta')" class="space-y-4">
        <input type="hidden" name="paciente_id" :value="paciente.id" />
        <div>
          <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de consulta <span class="text-red-500">*</span></label>
          <input v-model="form.fecha_consulta" type="datetime-local" class="input-hc" required />
        </div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Motivo de consulta</label><textarea v-model="form.motivo_consulta" rows="3" class="input-hc w-full min-h-[80px]"></textarea></div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Enfermedad actual</label><textarea v-model="form.enfermedad_actual" rows="3" class="input-hc w-full min-h-[80px]"></textarea></div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dx (Diagnóstico)</label><textarea v-model="form.dx" rows="3" class="input-hc w-full min-h-[80px]"></textarea></div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Tx (Tratamiento)</label><textarea v-model="form.tx" rows="3" class="input-hc w-full min-h-[80px]"></textarea></div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Plan Dx.</label><textarea v-model="form.plan_dx" rows="2" class="input-hc w-full min-h-[60px]"></textarea></div>
        <div><label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Recomendaciones</label><textarea v-model="form.recomendaciones" rows="2" class="input-hc w-full min-h-[60px]"></textarea></div>
        <div class="flex flex-wrap gap-3">
          <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Guardar consulta</button>
          <Link :href="'/ver-historia-clinica?id=' + paciente.id" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">Volver</Link>
        </div>
      </form>
    </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '../../components/PageBreadcrumb.vue';

const props = defineProps({
  title: { type: String, default: 'Nueva consulta' },
  paciente: { type: Object, required: true },
});

const breadcrumbItems = computed(() => [
  { label: 'Historia clínica', url: '/historia-clinica' },
  { label: (props.paciente?.nombres ?? '') + ' ' + (props.paciente?.apellidos ?? ''), url: '/ver-historia-clinica?id=' + props.paciente?.id },
  { label: 'Nueva consulta', url: null },
]);

const form = useForm({
  paciente_id: props.paciente.id,
  fecha_consulta: '',
  motivo_consulta: '',
  enfermedad_actual: '',
  dx: '',
  tx: '',
  plan_dx: '',
  recomendaciones: '',
});

onMounted(() => {
  if (!form.fecha_consulta) form.fecha_consulta = new Date().toISOString().slice(0, 16);
});
</script>

<style scoped>
.input-hc { height: 2.75rem; width: 100%; border-radius: 0.5rem; border: 2px solid #9ca3af; background: #fff; padding: 0.5rem 1rem; font-size: 0.875rem; }
textarea.input-hc { min-height: 5rem; resize: vertical; }
</style>
