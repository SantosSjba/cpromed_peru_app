<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'VitaTrack', url: null }]" />

      <Alert v-if="flash.success" variant="success" :message="flash.success" />
      <Alert v-else-if="flash.error" variant="error" :message="flash.error" />

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-brand-500 shadow-sm dark:bg-brand-600">
            <Icon icon="mdi:scale-bathroom" class="h-6 w-6 text-white" />
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Módulo</p>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Control de Peso</h2>
          </div>
        </div>
        <Button type="button" start-icon="mdi:account-plus" @click="abrirModalNuevoPaciente">Nuevo paciente</Button>
      </div>

      <Card title="Buscar" desc="La búsqueda se actualiza al escribir.">
        <form @submit.prevent="submitSearch" class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <FormInput
            id="buscar-vitatrack"
            v-model="buscar"
            type="search"
            label="Buscar paciente por nombre o DNI"
            label-class="sr-only"
            placeholder="Buscar paciente por nombre o DNI..."
            wrapper-class="flex-1 min-w-0"
            @update:model-value="onBuscarInput"
          />
          <Button type="submit" variant="outline" start-icon="mdi:magnify">Buscar</Button>
          <Link v-if="buscar.trim()" href="/control-peso" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Limpiar</Link>
        </form>
      </Card>

      <!-- Seguimientos activos -->
      <div v-if="conSeguimientoFiltrado.length > 0" class="space-y-3">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
          Pacientes en seguimiento ({{ conSeguimientoFiltrado.length }})
        </h3>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="item in conSeguimientoFiltrado"
            :key="item.id"
            class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-white/[0.02]"
          >
            <!-- Accent bar -->
            <div
              class="absolute left-0 top-0 h-full w-1 rounded-l-2xl"
              :class="imcBarColor(item.imc_color)"
            />
            <div class="pl-2">
              <!-- Nombre y DNI -->
              <div class="mb-3 flex items-start justify-between">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ item.paciente_nombre }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ item.paciente_dni || '—' }}</p>
                </div>
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                  :class="imcBadgeClass(item.imc_color)"
                >
                  {{ item.imc_categoria }}
                </span>
              </div>

              <!-- Stats en grid -->
              <div class="mb-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800/80">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Peso actual</p>
                  <p class="text-base font-bold text-gray-900 dark:text-white">{{ item.peso_actual }}<span class="text-xs font-normal"> kg</span></p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800/80">
                  <p class="text-xs text-gray-500 dark:text-gray-400">IMC</p>
                  <p class="text-base font-bold" :class="imcTextColor(item.imc_color)">{{ item.imc }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-2 dark:bg-gray-800/80">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Perdido</p>
                  <p class="text-base font-bold dark:text-white" :class="item.kg_perdidos > 0 ? 'text-emerald-600 dark:text-emerald-400' : item.kg_perdidos < 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'">
                    {{ item.kg_perdidos > 0 ? '-' : item.kg_perdidos < 0 ? '+' : '' }}{{ Math.abs(item.kg_perdidos) }}<span class="text-xs font-normal"> kg</span>
                  </p>
                </div>
              </div>

              <!-- Barra de progreso -->
              <div v-if="item.progreso_pct !== null" class="mb-3">
                <div class="mb-1 flex items-center justify-between text-xs">
                  <span class="text-gray-500 dark:text-gray-400">Hacia la meta</span>
                  <span class="font-semibold text-brand-600 dark:text-brand-400">{{ item.progreso_pct }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                  <div
                    class="h-full rounded-full bg-brand-500 transition-all dark:bg-brand-600"
                    :style="{ width: Math.min(item.progreso_pct, 100) + '%' }"
                  />
                </div>
              </div>

              <!-- Inicio seguimiento + registros -->
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ item.registros_count }} registro(s)</span>
                <span v-if="item.fecha_inicio">Desde {{ formatFecha(item.fecha_inicio) }}</span>
              </div>

              <!-- Acción -->
              <a
                :href="'/ver-control-peso?id=' + item.id"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20"
              >
                <Icon icon="mdi:chart-line" class="h-4 w-4" />
                Ver tracker
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Pacientes sin seguimiento -->
      <div class="space-y-3">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
          Pacientes sin seguimiento
          <span v-if="sinSeguimientoFiltrado.length">({{ sinSeguimientoFiltrado.length }})</span>
        </h3>

        <div v-if="sinSeguimientoFiltrado.length === 0 && sinSeguimiento.length === 0" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 py-12 text-center dark:border-gray-600 dark:bg-gray-800/50">
          <Icon icon="mdi:account-group-outline" class="mx-auto mb-3 h-12 w-12 text-gray-400 dark:text-gray-500" />
          <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No hay pacientes registrados aún</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Crea un paciente para comenzar su seguimiento</p>
          <Button type="button" size="sm" start-icon="mdi:plus" className="mt-4" @click="abrirModalNuevoPaciente">Crear paciente</Button>
        </div>

        <div v-else-if="sinSeguimientoFiltrado.length === 0" class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
          Todos los pacientes tienen seguimiento activo.
        </div>

        <div v-else class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                  <th class="px-5 py-3.5 font-semibold text-gray-700 dark:text-gray-200">Paciente</th>
                  <th class="px-5 py-3.5 font-semibold text-gray-700 dark:text-gray-200">DNI</th>
                  <th class="px-5 py-3.5 text-right font-semibold text-gray-700 dark:text-gray-200">Acción</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr v-for="p in sinSeguimientoFiltrado" :key="p.id" class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                  <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">{{ p.nombre }}</td>
                  <td class="px-5 py-3.5 text-gray-500 dark:text-gray-400">{{ p.dni || '—' }}</td>
                  <td class="px-5 py-3.5 text-right">
          <Button type="button" size="sm" className="!px-3 !py-1.5 !text-xs" start-icon="mdi:plus" @click="abrirModalConfigurar(p)">Iniciar seguimiento</Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Estado vacío global -->
      <div v-if="conSeguimientoFiltrado.length === 0 && sinSeguimientoFiltrado.length === 0 && buscar" class="py-8 text-center">
        <Icon icon="mdi:magnify-remove-outline" class="mx-auto mb-3 h-10 w-10 text-gray-400" />
        <p class="text-sm text-gray-600 dark:text-gray-400">No se encontraron resultados para "<strong>{{ buscar }}</strong>"</p>
      </div>
    </div>

    <!-- ── Modal: Nuevo Paciente ──────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modalNuevoPaciente" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Nuevo paciente</h3>
            <button type="button" @click="modalNuevoPaciente = false" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
              <Icon icon="mdi:close" class="h-5 w-5" />
            </button>
          </div>
          <form @submit.prevent="submitNuevoPaciente" class="space-y-4 p-6">
            <div class="grid grid-cols-2 gap-3">
              <FormInput v-model="formPaciente.nombres" label="Nombres" placeholder="Juan" required />
              <FormInput v-model="formPaciente.apellidos" label="Apellidos" placeholder="García" required />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <FormInput v-model="formPaciente.fecha_nacimiento" type="date" label="Fecha de nacimiento" required />
              <FormSelect v-model="formPaciente.genero" label="Género" required>
                <option value="">— Seleccionar —</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
              </FormSelect>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <FormInput v-model="formPaciente.dni" label="DNI" placeholder="12345678" />
              <FormInput v-model="formPaciente.celular" label="Celular" placeholder="999 000 000" />
            </div>
            <div class="flex gap-3 pt-2">
              <Button type="button" variant="outline" className="flex-1" @click="modalNuevoPaciente = false">Cancelar</Button>
              <Button type="submit" :disabled="submitting" className="flex-1">{{ submitting ? 'Guardando…' : 'Crear paciente' }}</Button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal: Configurar Seguimiento ──────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modalConfigurar" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
          <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <div>
              <h3 class="font-bold text-gray-900 dark:text-white">Configurar seguimiento</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ pacienteSeleccionado?.nombre }}</p>
            </div>
            <button type="button" @click="modalConfigurar = false" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
              <Icon icon="mdi:close" class="h-5 w-5" />
            </button>
          </div>
          <form @submit.prevent="submitConfigurar" class="space-y-4 p-6">
            <div class="grid grid-cols-2 gap-3">
              <FormInput v-model.number="formConfig.peso_inicial" type="number" step="0.1" label="Peso inicial (kg)" placeholder="Ej: 92.5" required />
              <FormInput v-model.number="formConfig.talla" type="number" step="0.1" label="Talla (cm)" placeholder="Ej: 180" required />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <FormInput v-model.number="formConfig.peso_meta" type="number" step="0.1" label="Peso meta (kg)" placeholder="Ej: 79" />
              <FormInput v-model="formConfig.fecha_inicio" type="date" label="Fecha de inicio" required />
            </div>
            <FormInput v-model="formConfig.fecha_meta" type="date" label="Fecha meta" />

            <div v-if="formConfig.peso_inicial && formConfig.talla" class="rounded-xl border border-brand-200 bg-brand-50 p-3 dark:border-brand-800 dark:bg-brand-500/10">
              <p class="text-xs text-brand-700 dark:text-brand-400">
                IMC inicial estimado:
                <strong>{{ calcularImcPreview(formConfig.peso_inicial, formConfig.talla) }}</strong>
                — {{ categoriaImcPreview(calcularImcPreview(formConfig.peso_inicial, formConfig.talla)) }}
              </p>
            </div>

            <div class="flex gap-3 pt-2">
              <Button type="button" variant="outline" className="flex-1" @click="modalConfigurar = false">Cancelar</Button>
              <Button type="submit" :disabled="submitting" className="flex-1">{{ submitting ? 'Guardando…' : 'Iniciar seguimiento' }}</Button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Alert, Card, Button } from '@/components/ui';
import { FormInput, FormSelect } from '@/components/form';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title:          { type: String, default: 'VitaTrack' },
  conSeguimiento: { type: Array, default: () => [] },
  sinSeguimiento: { type: Array, default: () => [] },
  buscar:         { type: String, default: '' },
});

const page  = usePage();
const flash = computed(() => page.props.flash ?? {});

const buscar      = ref(props.buscar);
let   debounceT   = null;

function onBuscarInput() {
  clearTimeout(debounceT);
  debounceT = setTimeout(() => {
    router.get('/control-peso', { buscar: buscar.value || undefined }, { preserveState: true, replace: true });
  }, 380);
}

function submitSearch() {
  router.get('/control-peso', { buscar: buscar.value || undefined }, { preserveState: true, replace: true });
}

// Filtrado local (el servidor ya filtra, pero añadimos local también si hay datos)
const conSeguimientoFiltrado = computed(() => props.conSeguimiento);
const sinSeguimientoFiltrado = computed(() => props.sinSeguimiento);

// ── Modal: Nuevo Paciente ─────────────────────────────────────────────────────
const modalNuevoPaciente = ref(false);
const submitting         = ref(false);
const formPaciente       = ref({ nombres: '', apellidos: '', fecha_nacimiento: '', genero: '', dni: '', celular: '' });

function abrirModalNuevoPaciente() {
  formPaciente.value = { nombres: '', apellidos: '', fecha_nacimiento: '', genero: '', dni: '', celular: '' };
  modalNuevoPaciente.value = true;
}

function submitNuevoPaciente() {
  submitting.value = true;
  router.post('/crear-paciente-rapido', formPaciente.value, {
    onFinish: () => { submitting.value = false; modalNuevoPaciente.value = false; },
  });
}

// ── Modal: Configurar Seguimiento ─────────────────────────────────────────────
const modalConfigurar      = ref(false);
const pacienteSeleccionado = ref(null);
const formConfig           = ref({
  paciente_id:   null,
  peso_inicial:  null,
  talla:         null,
  peso_meta:     null,
  fecha_inicio:  new Date().toISOString().split('T')[0],
  fecha_meta:    '',
  recompensas:   [],
});

function abrirModalConfigurar(paciente) {
  pacienteSeleccionado.value = paciente;
  formConfig.value = {
    paciente_id:  paciente.id,
    peso_inicial: null,
    talla:        null,
    peso_meta:    null,
    fecha_inicio: new Date().toISOString().split('T')[0],
    fecha_meta:   '',
    recompensas:  [],
  };
  modalConfigurar.value = true;
}

function submitConfigurar() {
  submitting.value = true;
  router.post('/configurar-control-peso', formConfig.value, {
    onFinish: () => { submitting.value = false; modalConfigurar.value = false; },
  });
}

// ── IMC helpers (cliente) ─────────────────────────────────────────────────────
function calcularImcPreview(peso, talla) {
  if (!talla || talla <= 0) return 0;
  const m = talla / 100;
  return Math.round((peso / (m * m)) * 10) / 10;
}

function categoriaImcPreview(imc) {
  if (imc < 18.5) return 'Bajo peso';
  if (imc < 25)   return 'Normal';
  if (imc < 30)   return 'Sobrepeso';
  if (imc < 35)   return 'Obesidad I';
  if (imc < 40)   return 'Obesidad II';
  return 'Obesidad III';
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function imcBarColor(color) {
  const m = { green: 'bg-emerald-500', yellow: 'bg-yellow-400', orange: 'bg-orange-500', red: 'bg-red-500', blue: 'bg-blue-400' };
  return m[color] || 'bg-gray-400';
}
function imcBadgeClass(color) {
  const m = {
    green:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    red:    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    blue:   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  };
  return m[color] || 'bg-gray-100 text-gray-700';
}
function imcTextColor(color) {
  const m = { green: 'text-emerald-600', yellow: 'text-yellow-600', orange: 'text-orange-600', red: 'text-red-600', blue: 'text-blue-600' };
  return m[color] || 'text-gray-600';
}
function formatFecha(fecha) {
  if (!fecha) return '—';
  const [y, m, d] = fecha.split('-');
  return `${d}/${m}/${y}`;
}
</script>

