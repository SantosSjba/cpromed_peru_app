<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Historia clínica', url: '/historia-clinica' }, { label: 'Nueva historia clínica', url: null }]" />
      <Alert v-if="Object.keys(form.errors).length" variant="error" title="Errores de validación">
        <ul class="list-inside list-disc text-sm">
          <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
        </ul>
      </Alert>

      <form @submit.prevent="form.post('/historia-clinica')" class="space-y-6">
        <Card title="Datos personales" desc="La edad se calculará automáticamente desde la fecha de nacimiento.">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <FormInput v-model="form.nombres" label="Nombres" required />
            <FormInput v-model="form.apellidos" label="Apellidos" required />
            <FormInput v-model="form.fecha_nacimiento" type="date" label="Fecha de nacimiento" required />
            <FormSelect v-model="form.genero" label="Género" required>
              <option value="">Seleccione</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="Otro">Otro</option>
            </FormSelect>
            <div class="sm:col-span-2">
              <FormInput v-model="form.direccion" label="Dirección" />
            </div>
            <FormInput v-model="form.celular" label="N.º Celular" />
            <FormInput v-model="form.ocupacion" label="Ocupación" />
            <FormInput v-model="form.dni" label="DNI" />
            <FormInput v-model="form.email" type="email" label="Email" />
            <FormInput v-model="form.grupo_sanguineo" label="Grupo sanguíneo" placeholder="Ej. O+" />
          </div>
        </Card>

        <Card title="Antecedentes">
          <div class="space-y-4">
            <FormTextarea v-model="form.antecedentes_medicos" label="Antecedentes médicos" :rows="3" />
            <FormTextarea v-model="form.antecedentes_personales" label="Antecedentes personales" :rows="4" />
            <FormTextarea v-model="form.antecedentes_familiares" label="Antecedentes familiares" :rows="4" />
          </div>
        </Card>

        <Card title="Historia">
          <div class="space-y-4">
            <FormTextarea v-model="form.enfermedades_previas" label="Enfermedades previas" :rows="3" />
            <div>
              <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Cirugías</p>
              <div class="mb-2 flex items-center gap-4">
                <FormRadio v-model="form.cirugias_si_no" name="cirugias_si_no" :value="true">Sí</FormRadio>
                <FormRadio v-model="form.cirugias_si_no" name="cirugias_si_no" :value="false">No</FormRadio>
              </div>
              <FormTextarea v-model="form.cirugias_detalle" label="" :rows="2" placeholder="Detalle de cirugías si aplica" />
            </div>
            <FormTextarea v-model="form.alergias" label="Alergias" :rows="2" />
            <FormTextarea v-model="form.medicamentos_actuales" label="Medicamentos actuales" :rows="2" />
          </div>
        </Card>

        <Card title="Primera consulta" desc="Se guardará como la primera fila en la tabla de consultas.">
          <div class="space-y-4">
            <FormInput v-model="form.fecha_consulta" type="datetime-local" label="Fecha de consulta" required />
            <FormTextarea v-model="form.motivo_consulta" label="Motivo de consulta" :rows="3" />
            <FormTextarea v-model="form.enfermedad_actual" label="Enfermedad actual" :rows="3" />
            <FormTextarea v-model="form.dx" label="Dx (Diagnóstico)" :rows="3" />
            <FormTextarea v-model="form.tx" label="Tx (Tratamiento)" :rows="3" />
            <FormTextarea v-model="form.plan_dx" label="Plan Dx." :rows="2" />
            <FormTextarea v-model="form.recomendaciones" label="Recomendaciones" :rows="2" />
          </div>
        </Card>

        <Card no-header>
          <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" start-icon="mdi:check">Guardar historia clínica</Button>
            <Link href="/historia-clinica" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</Link>
          </div>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button, Alert } from '@/components/ui';
import { FormInput, FormSelect, FormTextarea, FormRadio } from '@/components/form';

defineProps({ title: { type: String, default: 'Nueva historia clínica' } });

const form = useForm({
  nombres: '',
  apellidos: '',
  fecha_nacimiento: '',
  genero: '',
  direccion: '',
  celular: '',
  ocupacion: '',
  dni: '',
  email: '',
  grupo_sanguineo: '',
  antecedentes_medicos: '',
  antecedentes_personales: '',
  antecedentes_familiares: '',
  enfermedades_previas: '',
  cirugias_si_no: false,
  cirugias_detalle: '',
  alergias: '',
  medicamentos_actuales: '',
  fecha_consulta: '',
  motivo_consulta: '',
  enfermedad_actual: '',
  dx: '',
  tx: '',
  plan_dx: '',
  recomendaciones: '',
});

onMounted(() => {
  if (!form.fecha_consulta) {
    const now = new Date();
    form.fecha_consulta = now.toISOString().slice(0, 16);
  }
});
</script>
