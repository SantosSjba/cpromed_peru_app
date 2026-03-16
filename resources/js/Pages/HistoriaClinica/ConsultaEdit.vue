<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <Card title="Editar consulta" :desc="'Paciente: ' + paciente.nombres + ' ' + paciente.apellidos + '. Edite los datos de la consulta.'">
        <Alert v-if="Object.keys(form.errors).length" variant="error" title="Errores de validación">
          <ul class="list-inside list-disc text-sm">
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </Alert>
        <form @submit.prevent="form.post('/actualizar-consulta')" class="space-y-4">
          <input type="hidden" name="paciente_id" :value="paciente.id" />
          <input type="hidden" name="consulta_id" :value="consulta.id" />
          <FormInput v-model="form.fecha_consulta" type="datetime-local" label="Fecha de consulta" required />
          <FormTextarea v-model="form.motivo_consulta" label="Motivo de consulta" :rows="3" />
          <FormTextarea v-model="form.enfermedad_actual" label="Enfermedad actual" :rows="3" />
          <FormTextarea v-model="form.dx" label="Dx (Diagnóstico)" :rows="3" />
          <FormTextarea v-model="form.tx" label="Tx (Tratamiento)" :rows="3" />
          <FormTextarea v-model="form.plan_dx" label="Plan Dx." :rows="2" />
          <FormTextarea v-model="form.recomendaciones" label="Recomendaciones" :rows="2" />
          <div class="flex flex-wrap gap-3">
            <Button type="submit">Guardar cambios</Button>
            <Link :href="'/ver-historia-clinica?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">Volver</Link>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button, Alert } from '@/components/ui';
import { FormInput, FormTextarea } from '@/components/form';

const props = defineProps({
  title: { type: String, default: 'Editar consulta' },
  paciente: { type: Object, required: true },
  consulta: { type: Object, required: true },
});

const breadcrumbItems = computed(() => {
  const consultaFecha = props.consulta?.fecha_consulta ? new Date(props.consulta.fecha_consulta).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
  return [
    { label: 'Historia clínica', url: '/historia-clinica' },
    { label: (props.paciente?.nombres ?? '') + ' ' + (props.paciente?.apellidos ?? ''), url: '/ver-historia-clinica?id=' + props.paciente?.id },
    { label: 'Consulta ' + consultaFecha, url: '/ver-consulta?paciente=' + props.paciente?.id + '&consulta=' + props.consulta?.id },
    { label: 'Editar', url: null },
  ];
});

const form = useForm({
  paciente_id: props.paciente.id,
  consulta_id: props.consulta.id,
  fecha_consulta: props.consulta.fecha_consulta ? props.consulta.fecha_consulta.slice(0, 16) : '',
  motivo_consulta: props.consulta.motivo_consulta ?? '',
  enfermedad_actual: props.consulta.enfermedad_actual ?? '',
  dx: props.consulta.dx ?? '',
  tx: props.consulta.tx ?? '',
  plan_dx: props.consulta.plan_dx ?? '',
  recomendaciones: props.consulta.recomendaciones ?? '',
});
</script>
