<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <Card title="Nueva consulta" :desc="'Paciente: ' + paciente.nombres + ' ' + paciente.apellidos + '. Esta consulta se guardará como una nueva fila en la tabla de consultas.'">
        <Alert v-if="Object.keys(form.errors).length" variant="error" title="Errores de validación">
          <ul class="list-inside list-disc text-sm">
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </Alert>
        <form @submit.prevent="form.post('/guardar-consulta')" class="space-y-4">
          <input type="hidden" name="paciente_id" :value="paciente.id" />
          <FormInput v-model="form.fecha_consulta" type="datetime-local" label="Fecha de consulta" required />
          <FormTextarea v-model="form.motivo_consulta" label="Motivo de consulta" :rows="3" />
          <FormTextarea v-model="form.enfermedad_actual" label="Enfermedad actual" :rows="3" />
          <FormTextarea v-model="form.dx" label="Dx (Diagnóstico)" :rows="3" />
          <FormTextarea v-model="form.tx" label="Tx (Tratamiento)" :rows="3" />
          <FormTextarea v-model="form.plan_dx" label="Plan Dx." :rows="2" />
          <FormTextarea v-model="form.recomendaciones" label="Recomendaciones" :rows="2" />
          <div class="flex flex-wrap gap-3">
            <Button type="submit">Guardar consulta</Button>
            <Link :href="'/ver-historia-clinica?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">Volver</Link>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button, Alert } from '@/components/ui';
import { FormInput, FormTextarea } from '@/components/form';

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
