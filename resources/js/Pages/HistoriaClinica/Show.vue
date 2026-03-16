<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="breadcrumbItems" />
      <Alert v-if="flash.success" variant="success" :message="flash.success" />
      <Alert v-else-if="flash.error" variant="amber" :message="flash.error" />

      <Card no-header>
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-2xl font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
              {{ iniciales }}
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ paciente.nombres }} {{ paciente.apellidos }}</h1>
              <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                {{ paciente.dni ? 'DNI ' + paciente.dni : 'Sin DNI' }}
                <template v-if="paciente.edad_calculada != null"> · {{ paciente.edad_calculada }} años</template>
              </p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <a :href="'/descargar-historia-clinica-pdf?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
              <Icon icon="mdi:file-pdf-box" class="h-4 w-4" />
              Descargar PDF
            </a>
            <Link :href="'/editar-historia-clinica?id=' + paciente.id" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
              <Icon icon="mdi:pencil" class="h-4 w-4" />
              Editar
            </Link>
            <Link href="/historia-clinica" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
              Volver al listado
            </Link>
            <Button type="button" variant="outlineDanger" @click="confirmDelete">Eliminar historia clínica</Button>
          </div>
        </div>
      </Card>

      <Card title="Datos personales">
        <div class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nombres</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.nombres }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Apellidos</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.apellidos }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Fecha de nacimiento</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ formatDate(paciente.fecha_nacimiento) }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Edad</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.edad_calculada != null ? paciente.edad_calculada + ' años' : '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Género</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ generoLabel(paciente.genero) }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">DNI</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.dni || '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dirección</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.direccion || '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Celular</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.celular || '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Email</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.email || '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Ocupación</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.ocupacion || '—' }}</p></div>
          <div><p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Grupo sanguíneo</p><p class="mt-1 font-medium text-gray-900 dark:text-white">{{ paciente.grupo_sanguineo || '—' }}</p></div>
        </div>
      </Card>

      <Card v-if="ficha" title="Antecedentes">
        <div class="space-y-5">
          <div v-for="item in antecedentesLabels" :key="item.key">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ item.label }}</p>
            <p class="mt-1.5 whitespace-pre-wrap rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 dark:bg-gray-800/50 dark:text-gray-200">{{ ficha[item.key] || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Cirugías</p>
            <p class="mt-1.5">
              <Badge :color="ficha.cirugias_si_no ? 'warning' : 'light'">{{ ficha.cirugias_si_no ? 'Sí' : 'No' }}</Badge>
              <span v-if="ficha.cirugias_detalle" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ficha.cirugias_detalle }}</span>
            </p>
          </div>
        </div>
      </Card>

      <Card no-padding>
        <template #header>
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tabla de consultas</h3>
              <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cada fila es una consulta.</p>
            </div>
            <Link :href="'/nueva-consulta?paciente=' + paciente.id" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
              <Icon icon="mdi:plus" class="h-4 w-4" />
              Nueva consulta
            </Link>
          </div>
        </template>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                <th class="w-16 px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Nº</th>
                <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Fecha consulta</th>
                <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Motivo</th>
                <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Enfermedad actual</th>
                <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Dx</th>
                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
              <tr v-for="(c, index) in consultas" :key="c.id" class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ consultas.length - index }}</td>
                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ formatDateTime(c.fecha_consulta) }}</td>
                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ limitStr(c.motivo_consulta, 35) }}</td>
                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ limitStr(c.enfermedad_actual, 35) }}</td>
                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ limitStr(c.dx, 35) }}</td>
                <td class="px-5 py-3 text-right">
                  <Link :href="'/ver-consulta?paciente=' + paciente.id + '&consulta=' + c.id" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Ver detalle</Link>
                </td>
              </tr>
              <tr v-if="!consultas.length">
                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No hay consultas. Agrega la primera con «Nueva consulta».</td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>

      <Card>
        <template #header>
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Exámenes</h3>
            <Button type="button" start-icon="mdi:upload" @click="showExamenForm = !showExamenForm">Subir examen</Button>
          </div>
        </template>
        <div v-show="showExamenForm" class="mb-6 rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-6 dark:border-gray-600 dark:bg-gray-800/30">
          <form @submit.prevent="submitExamen">
            <input type="hidden" name="paciente_id" :value="paciente.id" />
            <div class="mb-4">
              <FormInput v-model="fechaExamen" type="date" label="Fecha del examen (común para todos)" wrapper-class="max-w-xs" />
            </div>
            <div class="mb-4">
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Seleccionar archivos (PDF o imágenes)</label>
              <input ref="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" multiple @change="onFilesSelected" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white file:hover:bg-brand-600" />
              <p v-if="fileRows.length" class="mt-1.5 text-sm text-gray-500">{{ fileRows.length }} archivo(s) seleccionado(s)</p>
            </div>
            <div v-if="fileRows.length" class="mb-4 space-y-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-600 dark:bg-gray-800/50">
              <h4 class="text-sm font-semibold text-gray-800 dark:text-white">Complete Tipo y Descripción por cada archivo</h4>
              <div v-for="(row, idx) in fileRows" :key="idx" class="flex flex-col gap-2 rounded-lg border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 sm:flex-row sm:items-center sm:gap-4">
                <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ row.fileName }}</span>
                <FormInput v-model="row.tipo" placeholder="Ej. Laboratorio" wrapper-class="flex-1 !mb-0" />
                <FormInput v-model="row.descripcion" placeholder="Descripción" wrapper-class="flex-[2] !mb-0" />
              </div>
            </div>
            <div v-if="fileRows.length" class="flex flex-wrap gap-3">
              <Button type="submit">Subir exámenes</Button>
              <Button type="button" variant="outline" @click="clearFiles">Cambiar archivos</Button>
            </div>
          </form>
        </div>
        <div>
          <template v-if="examenesPorFecha && examenesPorFecha.length">
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Exámenes agrupados por fecha.</p>
            <div v-for="grupo in examenesPorFecha" :key="grupo.fecha" class="mb-6 last:mb-0">
              <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                <Icon icon="mdi:calendar" class="h-4 w-4 text-gray-400" />
                Exámenes del {{ formatDate(grupo.fecha) }}
              </h3>
              <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                      <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Archivo</th>
                      <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Tipo</th>
                      <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                      <th class="px-4 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-200">Acción</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="e in grupo.items" :key="e.id" class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                      <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ e.file_name }}</td>
                      <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ e.tipo || '—' }}</td>
                      <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ e.descripcion || '—' }}</td>
                      <td class="px-4 py-2.5 text-right">
                        <a v-if="isPdf(e.file_name)" :href="'/ver-examen?id=' + e.id" target="_blank" rel="noopener noreferrer" class="font-medium text-brand-600 hover:underline dark:text-brand-400 mr-3">Ver</a>
                        <a :href="'/descargar-examen?id=' + e.id" class="font-medium text-brand-600 hover:underline dark:text-brand-400 mr-3">Descargar</a>
                        <Button type="button" variant="outlineDanger" size="sm" className="!px-0 !py-0 !text-sm" @click="confirmDeleteExamen(e)">Eliminar</Button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
          <p v-else class="py-8 text-center text-gray-500 dark:text-gray-400">No hay exámenes subidos. Use «Subir examen».</p>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Alert, Card, Button, Badge } from '@/components/ui';
import { FormInput } from '@/components/form';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title: { type: String, default: 'Historia clínica' },
  paciente: { type: Object, required: true },
  examenesPorFecha: { type: Array, default: () => [] },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const iniciales = computed(() => {
  const n = (props.paciente.nombres || '').trim();
  const a = (props.paciente.apellidos || '').trim();
  const n1 = n ? n[0] : '';
  const a1 = a ? a[0] : '';
  return (n1 + a1).toUpperCase() || '—';
});

const ficha = computed(() => props.paciente.historia_clinica_ficha || props.paciente.historiaClinicaFicha || null);
const consultas = computed(() => props.paciente.historia_clinica_consultas || props.paciente.historiaClinicaConsultas || []);

const breadcrumbItems = computed(() => [
  { label: 'Historia clínica', url: '/historia-clinica' },
  { label: (props.paciente?.nombres ?? '') + ' ' + (props.paciente?.apellidos ?? ''), url: null },
]);

const antecedentesLabels = [
  { key: 'antecedentes_medicos', label: 'Antecedentes médicos' },
  { key: 'antecedentes_personales', label: 'Antecedentes personales' },
  { key: 'antecedentes_familiares', label: 'Antecedentes familiares' },
  { key: 'enfermedades_previas', label: 'Enfermedades previas' },
  { key: 'alergias', label: 'Alergias' },
  { key: 'medicamentos_actuales', label: 'Medicamentos actuales' },
];

const showExamenForm = ref(false);
const fechaExamen = ref(new Date().toISOString().slice(0, 10));
const fileInput = ref(null);
const fileRows = ref([]);
let selectedFiles = [];

function formatDate(val) {
  if (!val) return '—';
  return new Date(val).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
function formatDateTime(val) {
  if (!val) return '—';
  const d = new Date(val);
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function generoLabel(g) {
  if (g === 'M') return 'Masculino';
  if (g === 'F') return 'Femenino';
  return g || '—';
}
function limitStr(s, max) {
  if (!s) return '—';
  return s.length > max ? s.slice(0, max) + '…' : s;
}
function isPdf(fileName) {
  return (fileName || '').toLowerCase().endsWith('.pdf');
}

function confirmDelete() {
  if (!confirm('¿Eliminar toda la historia clínica de este paciente? Se borrarán sus consultas y exámenes.')) return;
  router.post('/eliminar-historia-clinica', { id: props.paciente.id, _token: page.props.csrf_token ?? '' });
}

function onFilesSelected(e) {
  const files = e.target.files;
  if (!files || !files.length) {
    fileRows.value = [];
    selectedFiles = [];
    return;
  }
  selectedFiles = Array.from(files);
  fileRows.value = selectedFiles.map((f) => ({ fileName: f.name, tipo: '', descripcion: '' }));
}
function clearFiles() {
  fileRows.value = [];
  selectedFiles = [];
  if (fileInput.value) fileInput.value.value = '';
}
function submitExamen() {
  if (!selectedFiles.length) return;
  const formData = new FormData();
  formData.append('paciente_id', props.paciente.id);
  formData.append('fecha_examen', fechaExamen.value);
  formData.append('_token', page.props.csrf_token ?? '');
  selectedFiles.forEach((file, i) => {
    formData.append('archivo[]', file);
    formData.append('tipo[' + i + ']', fileRows.value[i]?.tipo ?? '');
    formData.append('descripcion[' + i + ']', fileRows.value[i]?.descripcion ?? '');
  });
  router.post('/guardar-examen', formData, { forceFormData: true });
  clearFiles();
  showExamenForm.value = false;
}

function confirmDeleteExamen(e) {
  if (!confirm('¿Eliminar este examen? Se borrará el archivo.')) return;
  router.post('/eliminar-examen', { id: e.id, _token: page.props.csrf_token ?? '' });
}
</script>

<style scoped>
.input-hc {
  height: 2.75rem;
  width: 100%;
  border-radius: 0.5rem;
  border: 2px solid #e5e7eb;
  background: #fff;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}
</style>
