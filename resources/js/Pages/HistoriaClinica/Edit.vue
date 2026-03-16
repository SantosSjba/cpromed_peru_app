<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="editBreadcrumbItems" />
      <Alert v-if="Object.keys(form.errors).length" variant="error" title="Errores de validación">
        <ul class="list-inside list-disc text-sm">
          <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
        </ul>
      </Alert>

      <form @submit.prevent="form.post('/actualizar-historia-clinica')" class="space-y-6">
        <input type="hidden" name="id" :value="paciente.id" />
        <Card title="Datos personales">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <FormInput v-model="form.nombres" label="Nombres" required />
            <FormInput v-model="form.apellidos" label="Apellidos" required />
            <FormInput v-model="form.fecha_nacimiento" type="date" label="Fecha de nacimiento" required />
            <FormSelect v-model="form.genero" label="Género" required>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="Otro">Otro</option>
            </FormSelect>
            <div class="sm:col-span-2">
              <FormInput v-model="form.direccion" label="Dirección" />
            </div>
            <FormInput v-model="form.celular" label="Celular" />
            <FormInput v-model="form.ocupacion" label="Ocupación" />
            <FormInput v-model="form.dni" label="DNI" />
            <FormInput v-model="form.email" type="email" label="Email" />
            <FormInput v-model="form.grupo_sanguineo" label="Grupo sanguíneo" />
          </div>
        </Card>

        <Card title="Antecedentes">
          <div class="space-y-4">
            <FormTextarea v-model="form.antecedentes_medicos" label="Antecedentes médicos" :rows="3" />
            <FormTextarea v-model="form.antecedentes_personales" label="Antecedentes personales" :rows="4" />
            <FormTextarea v-model="form.antecedentes_familiares" label="Antecedentes familiares" :rows="4" />
            <FormTextarea v-model="form.enfermedades_previas" label="Enfermedades previas" :rows="3" />
            <div>
              <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Cirugías</p>
              <div class="mb-2 flex items-center gap-4">
                <FormRadio v-model="form.cirugias_si_no" name="cirugias_si_no_edit" :value="true">Sí</FormRadio>
                <FormRadio v-model="form.cirugias_si_no" name="cirugias_si_no_edit" :value="false">No</FormRadio>
              </div>
              <FormTextarea v-model="form.cirugias_detalle" label="" :rows="2" />
            </div>
            <FormTextarea v-model="form.alergias" label="Alergias" :rows="2" />
            <FormTextarea v-model="form.medicamentos_actuales" label="Medicamentos actuales" :rows="2" />
          </div>
        </Card>

        <Card no-header>
          <div class="flex flex-wrap items-center gap-3">
            <Button type="submit">Actualizar historia clínica</Button>
            <Link :href="'/ver-historia-clinica?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</Link>
          </div>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button, Alert } from '@/components/ui';
import { FormInput, FormSelect, FormTextarea, FormRadio } from '@/components/form';

const props = defineProps({
  title: { type: String, default: 'Editar historia clínica' },
  paciente: { type: Object, required: true },
});

const ficha = props.paciente.historia_clinica_ficha || props.paciente.historiaClinicaFicha || {};
const editBreadcrumbItems = computed(() => [
  { label: 'Historia clínica', url: '/historia-clinica' },
  { label: (props.paciente?.nombres ?? '') + ' ' + (props.paciente?.apellidos ?? ''), url: '/ver-historia-clinica?id=' + props.paciente?.id },
  { label: 'Editar', url: null },
]);

const form = useForm({
  id: props.paciente.id,
  nombres: props.paciente.nombres ?? '',
  apellidos: props.paciente.apellidos ?? '',
  fecha_nacimiento: props.paciente.fecha_nacimiento ? props.paciente.fecha_nacimiento.slice(0, 10) : '',
  genero: props.paciente.genero ?? 'M',
  direccion: props.paciente.direccion ?? '',
  celular: props.paciente.celular ?? '',
  ocupacion: props.paciente.ocupacion ?? '',
  dni: props.paciente.dni ?? '',
  email: props.paciente.email ?? '',
  grupo_sanguineo: props.paciente.grupo_sanguineo ?? '',
  antecedentes_medicos: ficha.antecedentes_medicos ?? '',
  antecedentes_personales: ficha.antecedentes_personales ?? '',
  antecedentes_familiares: ficha.antecedentes_familiares ?? '',
  enfermedades_previas: ficha.enfermedades_previas ?? '',
  cirugias_si_no: !!ficha.cirugias_si_no,
  cirugias_detalle: ficha.cirugias_detalle ?? '',
  alergias: ficha.alergias ?? '',
  medicamentos_actuales: ficha.medicamentos_actuales ?? '',
});
</script>
