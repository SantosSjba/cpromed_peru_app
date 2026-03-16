<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Historia clínica', url: '/historia-clinica' }, { label: 'Nueva historia clínica', url: null }]" />
      <ul v-if="Object.keys(form.errors).length" class="list-inside list-disc rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
      </ul>

      <form @submit.prevent="form.post('/historia-clinica')" class="space-y-6">
        <!-- Datos personales -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
          <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Datos personales</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">La edad se calculará automáticamente desde la fecha de nacimiento.</p>
          </div>
          <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Nombres <span class="text-red-500">*</span></label>
              <input v-model="form.nombres" type="text" class="input-hc" required />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Apellidos <span class="text-red-500">*</span></label>
              <input v-model="form.apellidos" type="text" class="input-hc" required />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de nacimiento <span class="text-red-500">*</span></label>
              <input v-model="form.fecha_nacimiento" type="date" class="input-hc" required />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Género <span class="text-red-500">*</span></label>
              <select v-model="form.genero" class="input-hc" required>
                <option value="">Seleccione</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dirección</label>
              <input v-model="form.direccion" type="text" class="input-hc" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">N.º Celular</label>
              <input v-model="form.celular" type="text" class="input-hc" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Ocupación</label>
              <input v-model="form.ocupacion" type="text" class="input-hc" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">DNI</label>
              <input v-model="form.dni" type="text" class="input-hc" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Email</label>
              <input v-model="form.email" type="email" class="input-hc" />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Grupo sanguíneo</label>
              <input v-model="form.grupo_sanguineo" type="text" class="input-hc" placeholder="Ej. O+" />
            </div>
          </div>
        </div>

        <!-- Antecedentes -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
          <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Antecedentes</h2>
          </div>
          <div class="space-y-4 p-6">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes médicos</label>
              <textarea v-model="form.antecedentes_medicos" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes personales</label>
              <textarea v-model="form.antecedentes_personales" rows="4" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes familiares</label>
              <textarea v-model="form.antecedentes_familiares" rows="4" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
          </div>
        </div>

        <!-- Historia -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
          <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Historia</h2>
          </div>
          <div class="space-y-4 p-6">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Enfermedades previas</label>
              <textarea v-model="form.enfermedades_previas" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Cirugías</label>
              <div class="mb-2 flex items-center gap-4">
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.cirugias_si_no" type="radio" :value="true" />
                  <span>Sí</span>
                </label>
                <label class="inline-flex items-center gap-2">
                  <input v-model="form.cirugias_si_no" type="radio" :value="false" />
                  <span>No</span>
                </label>
              </div>
              <textarea v-model="form.cirugias_detalle" rows="2" class="input-hc w-full min-h-[60px]" placeholder="Detalle de cirugías si aplica"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Alergias</label>
              <textarea v-model="form.alergias" rows="2" class="input-hc w-full min-h-[60px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Medicamentos actuales</label>
              <textarea v-model="form.medicamentos_actuales" rows="2" class="input-hc w-full min-h-[60px]"></textarea>
            </div>
          </div>
        </div>

        <!-- Primera consulta -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
          <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Primera consulta</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Se guardará como la primera fila en la tabla de consultas.</p>
          </div>
          <div class="space-y-4 p-6">
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de consulta <span class="text-red-500">*</span></label>
              <input v-model="form.fecha_consulta" type="datetime-local" class="input-hc" required />
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Motivo de consulta</label>
              <textarea v-model="form.motivo_consulta" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Enfermedad actual</label>
              <textarea v-model="form.enfermedad_actual" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dx (Diagnóstico)</label>
              <textarea v-model="form.dx" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Tx (Tratamiento)</label>
              <textarea v-model="form.tx" rows="3" class="input-hc w-full min-h-[80px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Plan Dx.</label>
              <textarea v-model="form.plan_dx" rows="2" class="input-hc w-full min-h-[60px]"></textarea>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Recomendaciones</label>
              <textarea v-model="form.recomendaciones" rows="2" class="input-hc w-full min-h-[60px]"></textarea>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700">
          <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
            <Icon icon="mdi:check" class="h-4 w-4" />
            Guardar historia clínica
          </button>
          <Link href="/historia-clinica" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
            Cancelar
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '../../components/PageBreadcrumb.vue';
import { Icon } from '@iconify/vue';

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

<style scoped>
.input-hc {
  height: 2.75rem;
  width: 100%;
  border-radius: 0.5rem;
  border: 2px solid #9ca3af;
  background: #fff;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
}
.input-hc:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}
textarea.input-hc {
  min-height: 5rem;
  height: auto;
  resize: vertical;
}
select.input-hc {
  appearance: none;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>
