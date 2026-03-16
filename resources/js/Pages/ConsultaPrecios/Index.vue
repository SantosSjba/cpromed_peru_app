<template>
  <AppLayout>
    <div class="space-y-5">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Consulta de Precios', url: null }]" />
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Fuente: <strong>DIGEMID</strong> — SNIPPF
        </p>
        <a href="https://opm-digemid.minsa.gob.pe/#/consulta-producto" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
          Ver en DIGEMID
        </a>
      </div>

      <Card title="Filtros de búsqueda">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Producto autocomplete -->
          <div ref="productoAutocompleteRef" class="relative lg:col-span-2">
            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Producto / Principio activo <span class="text-red-500">*</span></label>
            <div class="relative">
              <input
                v-model="productoQuery"
                type="text"
                placeholder="Ej: PARACETAMOL, IBUPROFENO..."
                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                @input="debounceBuscarProducto()"
                @focus="onFocusProducto()"
                @keydown.escape="cerrarSugerencias()"
                @keydown.arrow-down.prevent="navegarSugerencias(1)"
                @keydown.arrow-up.prevent="navegarSugerencias(-1)"
                @keydown.enter.prevent="seleccionarSugerenciaActiva()"
              />
              <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                <Icon v-show="!loadingProducto" icon="mdi:magnify" class="h-4 w-4 text-gray-400" />
                <Icon v-show="loadingProducto" icon="mdi:loading" class="h-4 w-4 animate-spin text-brand-500" />
              </div>
            </div>
            <div v-show="showSuggestions && sugerencias.length" class="absolute z-50 mt-1 w-full max-h-[280px] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
              <button v-for="(item, idx) in sugerencias" :key="idx" type="button" @click="seleccionarProducto(item)" :class="idx === sugerenciaActiva ? 'bg-brand-50 dark:bg-brand-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'" class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm transition">
                <span class="font-medium text-gray-900 dark:text-white">{{ item?.nombreProducto ?? '' }} {{ item?.concent ?? '' }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ item?.nombreFormaFarmaceutica ?? '' }}</span>
              </button>
            </div>
            <div v-show="sinResultadosProducto" class="absolute z-50 mt-1 w-full rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700/50 dark:bg-gray-800">
              <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">No se encontró "{{ productoQuery }}"</p>
              <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Intente con el nombre genérico.</p>
            </div>
            <div v-show="productoSeleccionado" class="mt-2 flex items-center gap-2">
              <span class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/20 dark:text-brand-300">
                {{ productoSeleccionado?.nombreProducto ?? '' }} {{ productoSeleccionado?.concent ?? '' }}
              </span>
              <button type="button" @click="limpiarProducto()" class="text-xs text-gray-400 hover:text-red-500">Cambiar</button>
            </div>
          </div>

          <FormSelect v-model="filtros.codTipoEstablecimiento" label="Tipo establecimiento">
            <option value="">Todos</option>
            <option value="1">Privado</option>
            <option value="2">Público</option>
          </FormSelect>

          <FormSelect
            v-model="filtros.codigoDepartamento"
            label="Departamento"
            placeholder="— Seleccionar —"
            required
            @update:model-value="onDepartamentoChange"
          >
            <option v-for="(nombre, cod) in (departamentos || {})" :key="cod" :value="cod">{{ nombre }}</option>
          </FormSelect>

          <FormSelect
            v-model="filtros.codigoProvincia"
            label="Provincia"
            placeholder="— Seleccionar —"
            :disabled="!filtros.codigoDepartamento || loadingProvincias"
            @update:model-value="onProvinciaChange"
          >
            <option v-for="p in provincias" :key="p.codigo" :value="p.codigo">{{ p.descripcion }}</option>
          </FormSelect>

          <FormSelect
            v-model="filtros.codigoUbigeo"
            label="Distrito"
            placeholder="— Todos —"
            :disabled="!filtros.codigoProvincia || loadingDistritos"
          >
            <option v-for="d in distritos" :key="d.codigo" :value="d.codigo">{{ d.descripcion }}</option>
          </FormSelect>

          <FormInput v-model="filtros.nombreLaboratorio" label="Laboratorio" placeholder="Nombre laboratorio..." />
          <FormInput v-model="filtros.nombreEstablecimiento" label="Farmacia / Botica" placeholder="Nombre establecimiento..." />
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <Button @click="buscarPrecios()" :disabled="!puedeConsultar || loadingPrecios">
            <template #startIcon>
              <Icon :icon="loadingPrecios ? 'mdi:loading' : 'mdi:magnify'" class="h-4 w-4" :class="{ 'animate-spin': loadingPrecios }" />
            </template>
            {{ loadingPrecios ? 'Consultando...' : 'Consultar precios' }}
          </Button>
          <Button type="button" variant="outline" @click="limpiarFiltros()">Limpiar</Button>
        </div>
        <p v-show="!puedeConsultar" class="mt-2 text-xs text-amber-600 dark:text-amber-400">Seleccione un producto del autocomplete y un departamento.</p>
      </Card>

      <Alert v-show="errorMsg" variant="error" :message="errorMsg" />

      <!-- Resultados -->
      <div v-show="resultados.length > 0 || (buscado && resultados.length === 0)">
        <div v-show="resultados.length > 0" class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
          <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total registros</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">{{ resultadosFiltrados.length }}</p>
          </div>
          <div class="rounded-xl border border-green-200 bg-green-50 p-3 shadow-sm dark:border-green-800 dark:bg-green-900/10">
            <p class="text-xs text-green-600 dark:text-green-400">Precio mínimo</p>
            <p class="mt-0.5 text-xl font-bold text-green-700 dark:text-green-300">S/ {{ precioMinimo.toFixed(2) }}</p>
          </div>
          <div class="rounded-xl border border-red-200 bg-red-50 p-3 shadow-sm dark:border-red-800 dark:bg-red-900/10">
            <p class="text-xs text-red-600 dark:text-red-400">Precio máximo</p>
            <p class="mt-0.5 text-xl font-bold text-red-700 dark:text-red-300">S/ {{ precioMaximo.toFixed(2) }}</p>
          </div>
          <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 shadow-sm dark:border-blue-800 dark:bg-blue-900/10">
            <p class="text-xs text-blue-600 dark:text-blue-400">Precio promedio</p>
            <p class="mt-0.5 text-xl font-bold text-blue-700 dark:text-blue-300">S/ {{ precioPromedio.toFixed(2) }}</p>
          </div>
        </div>

        <div v-show="resultados.length > 0" class="mb-3 flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <FormInput
            v-model="busquedaTabla"
            type="text"
            placeholder="Filtrar por farmacia o laboratorio..."
            size="sm"
            wrapper-class="w-full sm:min-w-[180px] sm:flex-1"
            label-class="sr-only"
            label="Filtrar tabla"
          />
          <FormSelect v-model="filtroTabla" size="sm" wrapper-class="min-w-0" label-class="sr-only" label="Tipo">
            <option value="">Todos los tipos</option>
            <option value="Privado">Privado</option>
            <option value="Público">Público</option>
          </FormSelect>
          <FormSelect v-model="ordenPrecio" size="sm" wrapper-class="min-w-0" label-class="sr-only" label="Ordenar">
            <option value="">Ordenar</option>
            <option value="asc">Precio ↑</option>
            <option value="desc">Precio ↓</option>
          </FormSelect>
          <FormSelect v-model="porPagina" size="sm" wrapper-class="min-w-0" label-class="sr-only" label="Por página">
            <option value="10">10 por página</option>
            <option value="20">20 por página</option>
            <option value="50">50 por página</option>
          </FormSelect>
          <Button type="button" variant="outline" size="sm" className="!border-emerald-300 !bg-emerald-50 !text-emerald-700 hover:!bg-emerald-100 dark:!border-emerald-700 dark:!bg-emerald-900/20 dark:!text-emerald-400" start-icon="mdi:download" @click="exportarXLSX()">
            Exportar Excel
          </Button>
          <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ resultadosFiltrados.length }}</span>
            de <span>{{ resultados.length }}</span> resultados
          </span>
        </div>

        <div v-show="buscado && resultados.length === 0" class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/50 p-8 text-center dark:border-amber-700 dark:bg-amber-900/10">
          <p class="text-sm font-medium text-amber-800 dark:text-amber-300">No se encontraron precios con los filtros seleccionados.</p>
        </div>

        <div v-show="paginados.length > 0" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                  <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo</th>
                  <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Farmacia</th>
                  <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Producto</th>
                  <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Laboratorio</th>
                  <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Ubicación</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">P. Unit.</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">P. Pack</th>
                  <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Detalle</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                <tr v-for="(item, idx) in paginados" :key="idx" class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                  <td class="px-4 py-3">
                    <Badge :color="(item?.setcodigo) === 'Privado' ? 'primary' : 'success'">{{ item?.setcodigo ?? '—' }}</Badge>
                  </td>
                  <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item?.nombreComercial ?? '—' }}</td>
                  <td class="px-4 py-3">
                    <p class="font-medium text-gray-900 dark:text-white">{{ item?.nombreProducto ?? item?.nombreSustancia ?? '—' }}</p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ item?.concent ?? '' }} {{ item?.nombreFormaFarmaceutica ? '· ' + item.nombreFormaFarmaceutica : '' }}</p>
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ item?.nombreLaboratorio ?? '—' }}</td>
                  <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ [item?.distrito, item?.provincia].filter(Boolean).join(', ') || '—' }}</td>
                  <td class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">{{ item?.precio2 != null ? 'S/ ' + Number(item.precio2).toFixed(2) : '—' }}</td>
                  <td class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">{{ item?.precio1 != null ? 'S/ ' + Number(item.precio1).toFixed(2) : '—' }}</td>
                  <td class="px-4 py-3 text-center">
                    <Button type="button" variant="outline" size="sm" className="!px-2.5 !py-1 !text-xs" @click="verDetalle(item)">Ver</Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <PaginationLinks
            :current-page="paginaActual"
            :total-pages="totalPaginas"
            :total-items="resultadosFiltrados.length"
            :per-page="porPagina"
            item-label="resultados"
            @update:page="paginaActual = $event"
          />
        </div>
      </div>

      <div v-show="!buscado && !loadingPrecios" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/50 p-12 text-center dark:border-gray-700 dark:bg-white/[0.01]">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Busca un medicamento para ver precios en farmacias y boticas del Perú</p>
      </div>

      <!-- Modal detalle -->
      <Teleport to="body">
        <div v-show="modalDetalle" class="fixed inset-0 z-[100000] flex items-end justify-center p-4 sm:items-center" @keydown.escape="cerrarModalDetalle()">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrarModalDetalle()"></div>
          <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-t-2xl bg-white shadow-2xl dark:bg-gray-900 sm:rounded-2xl">
            <div class="flex items-start justify-between border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/60">
              <div class="min-w-0 flex-1 pr-4">
                <h3 class="truncate text-base font-semibold text-gray-900 dark:text-white">{{ detalle?.nombreComercial ?? 'Detalle' }}</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ [detalle?.direccion, detalle?.distrito, detalle?.provincia].filter(Boolean).join(' · ') }}</p>
              </div>
              <button type="button" @click="cerrarModalDetalle()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300">✕</button>
            </div>
            <div v-show="loadingDetalle" class="flex flex-col items-center justify-center gap-3 py-16">
              <Icon icon="mdi:loading" class="h-8 w-8 animate-spin text-brand-500" />
              <p class="text-sm text-gray-500 dark:text-gray-400">Cargando detalle...</p>
            </div>
            <div v-show="!loadingDetalle && errorDetalle" class="px-6 py-10 text-center text-sm font-medium text-red-600 dark:text-red-400">{{ errorDetalle }}</div>
            <div v-show="!loadingDetalle && detalle" class="max-h-[60vh] overflow-y-auto p-6">
              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/50">
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Precio por pack</p>
                  <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ detalle?.precio1 ? 'S/ ' + Number(detalle.precio1).toFixed(2) : '—' }}</p>
                </div>
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center dark:border-green-800 dark:bg-green-900/20">
                  <p class="text-xs font-medium text-green-600 dark:text-green-400">Precio unitario</p>
                  <p class="mt-1 text-3xl font-bold text-green-700 dark:text-green-300">{{ detalle?.precio2 ? 'S/ ' + Number(detalle.precio2).toFixed(2) : '—' }}</p>
                </div>
              </div>
              <p v-if="detalle?.telefono && detalle.telefono !== 'NO REGISTRADO'" class="mt-4 text-xs text-gray-600 dark:text-gray-400">Tel: {{ detalle.telefono }}</p>
              <p v-if="detalle?.horarioAtencion" class="mt-2 text-xs text-gray-600 dark:text-gray-400">Horario: {{ detalle.horarioAtencion }}</p>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Card, Button, Alert, Badge } from '@/components/ui';
import { FormInput, FormSelect } from '@/components/form';
import { Icon } from '@iconify/vue';

const productoAutocompleteRef = ref(null);

const props = defineProps({
  title: { type: String, default: 'Consulta de Precios de Medicamentos' },
  departamentos: { type: Object, default: () => ({}) },
});

const page = usePage();
const csrf = computed(() => page.props.csrf_token ?? '');

const productoQuery = ref('');
const productoSeleccionado = ref(null);
const sugerencias = ref([]);
const showSuggestions = ref(false);
const sinResultadosProducto = ref(false);
const sugerenciaActiva = ref(-1);
const loadingProducto = ref(false);
let debounceTimer = null;

const filtros = ref({
  codigoDepartamento: '',
  codigoProvincia: '',
  codigoUbigeo: '',
  codTipoEstablecimiento: '',
  nombreEstablecimiento: '',
  nombreLaboratorio: '',
});
const provincias = ref([]);
const distritos = ref([]);
const loadingProvincias = ref(false);
const loadingDistritos = ref(false);

const resultados = ref([]);
const buscado = ref(false);
const loadingPrecios = ref(false);
const errorMsg = ref('');
const busquedaTabla = ref('');
const filtroTabla = ref('');
const ordenPrecio = ref('');
const paginaActual = ref(1);
const porPagina = ref(20);

const modalDetalle = ref(false);
const detalle = ref(null);
const loadingDetalle = ref(false);
const errorDetalle = ref('');

const puedeConsultar = computed(() => productoSeleccionado.value && filtros.value.codigoDepartamento);

function onFocusProducto() {
  if (sugerencias.value.length) showSuggestions.value = true;
}
function cerrarSugerencias() {
  showSuggestions.value = false;
  sinResultadosProducto.value = false;
}
function navegarSugerencias(dir) {
  if (!sugerencias.value.length) return;
  sugerenciaActiva.value = Math.max(-1, Math.min(sugerencias.value.length - 1, sugerenciaActiva.value + dir));
}
function seleccionarSugerenciaActiva() {
  if (sugerenciaActiva.value >= 0 && sugerencias.value[sugerenciaActiva.value]) seleccionarProducto(sugerencias.value[sugerenciaActiva.value]);
}
function cerrarModalDetalle() {
  modalDetalle.value = false;
}

function debounceBuscarProducto() {
  clearTimeout(debounceTimer);
  const q = productoQuery.value.trim();
  if (q.length < 2) {
    sugerencias.value = [];
    showSuggestions.value = false;
    sinResultadosProducto.value = false;
    return;
  }
  debounceTimer = setTimeout(() => buscarProducto(), 400);
}

async function buscarProducto() {
  const q = productoQuery.value.trim();
  if (q.length < 2) return;
  loadingProducto.value = true;
  sinResultadosProducto.value = false;
  try {
    const r = await fetch(`/consulta-precios/autocomplete?query=${encodeURIComponent(q)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await r.json();
    sugerencias.value = Array.isArray(data) ? data.slice(0, 12) : [];
    showSuggestions.value = sugerencias.value.length > 0;
    sinResultadosProducto.value = sugerencias.value.length === 0;
    sugerenciaActiva.value = -1;
  } catch {
    sugerencias.value = [];
    sinResultadosProducto.value = true;
  } finally {
    loadingProducto.value = false;
  }
}

function seleccionarProducto(item) {
  if (!item) return;
  productoSeleccionado.value = item;
  productoQuery.value = (item.nombreProducto ?? '') + ' ' + (item.concent ?? '');
  sugerencias.value = [];
  showSuggestions.value = false;
  sinResultadosProducto.value = false;
}

function limpiarProducto() {
  productoSeleccionado.value = null;
  productoQuery.value = '';
  sugerencias.value = [];
  sinResultadosProducto.value = false;
}

function onClickOutside(e) {
  if (productoAutocompleteRef.value && !productoAutocompleteRef.value.contains(e.target)) {
    cerrarSugerencias();
  }
}

onMounted(() => {
  document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
});

async function onDepartamentoChange() {
  filtros.value.codigoProvincia = '';
  filtros.value.codigoUbigeo = '';
  provincias.value = [];
  distritos.value = [];
  if (!filtros.value.codigoDepartamento) return;
  loadingProvincias.value = true;
  try {
    const r = await fetch(`/consulta-precios/provincias?codigoDepartamento=${filtros.value.codigoDepartamento}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    provincias.value = await r.json();
  } finally {
    loadingProvincias.value = false;
  }
}

async function onProvinciaChange() {
  filtros.value.codigoUbigeo = '';
  distritos.value = [];
  if (!filtros.value.codigoProvincia) return;
  loadingDistritos.value = true;
  try {
    const r = await fetch(`/consulta-precios/distritos?codigoDepartamento=${filtros.value.codigoDepartamento}&codigoProvincia=${filtros.value.codigoProvincia}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    distritos.value = await r.json();
  } finally {
    loadingDistritos.value = false;
  }
}

async function buscarPrecios() {
  if (!puedeConsultar.value) return;
  loadingPrecios.value = true;
  errorMsg.value = '';
  buscado.value = false;
  resultados.value = [];
  paginaActual.value = 1;
  busquedaTabla.value = '';
  ordenPrecio.value = '';
  try {
    const r = await fetch('/consulta-precios/buscar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf.value,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        codigoProducto: productoSeleccionado.value.grupo,
        codigoDepartamento: filtros.value.codigoDepartamento,
        codigoProvincia: filtros.value.codigoProvincia || null,
        codigoUbigeo: filtros.value.codigoUbigeo || null,
        codTipoEstablecimiento: filtros.value.codTipoEstablecimiento || null,
        nombreEstablecimiento: filtros.value.nombreEstablecimiento || null,
        nombreLaboratorio: filtros.value.nombreLaboratorio || null,
        codGrupoFF: productoSeleccionado.value.codGrupoFF,
        concent: productoSeleccionado.value.concent,
        pagina: 1,
        tokenGoogle: '',
      }),
    });
    const data = await r.json();
    if (data.success) resultados.value = (data.data ?? []).filter(Boolean);
    else errorMsg.value = data.message ?? 'Error al consultar DIGEMID.';
  } catch {
    errorMsg.value = 'Error de conexión. Intente nuevamente.';
  } finally {
    loadingPrecios.value = false;
    buscado.value = true;
  }
}

function limpiarFiltros() {
  limpiarProducto();
  filtros.value = { codigoDepartamento: '', codigoProvincia: '', codigoUbigeo: '', codTipoEstablecimiento: '', nombreEstablecimiento: '', nombreLaboratorio: '' };
  provincias.value = [];
  distritos.value = [];
  resultados.value = [];
  buscado.value = false;
  errorMsg.value = '';
  busquedaTabla.value = '';
  ordenPrecio.value = '';
  paginaActual.value = 1;
}

const resultadosFiltrados = computed(() => {
  let list = [...resultados.value].filter(Boolean);
  const q = busquedaTabla.value.trim().toLowerCase();
  if (q) list = list.filter((r) => ((r.nombreComercial ?? '') + ' ' + (r.nombreLaboratorio ?? '')).toLowerCase().includes(q));
  if (filtroTabla.value) list = list.filter((r) => (r.setcodigo ?? '') === filtroTabla.value);
  if (ordenPrecio.value === 'asc') list.sort((a, b) => (Number(a.precio2) || 0) - (Number(b.precio2) || 0));
  if (ordenPrecio.value === 'desc') list.sort((a, b) => (Number(b.precio2) || 0) - (Number(a.precio2) || 0));
  return list;
});

const precioMinimo = computed(() => {
  const nums = resultadosFiltrados.value.map((r) => Number(r.precio2)).filter((n) => !isNaN(n) && n > 0);
  return nums.length ? Math.min(...nums) : 0;
});
const precioMaximo = computed(() => {
  const nums = resultadosFiltrados.value.map((r) => Number(r.precio2)).filter((n) => !isNaN(n) && n > 0);
  return nums.length ? Math.max(...nums) : 0;
});
const precioPromedio = computed(() => {
  const nums = resultadosFiltrados.value.map((r) => Number(r.precio2)).filter((n) => !isNaN(n) && n > 0);
  return nums.length ? nums.reduce((a, b) => a + b, 0) / nums.length : 0;
});

const totalPaginas = computed(() => Math.max(1, Math.ceil(resultadosFiltrados.value.length / porPagina.value)));
const paginados = computed(() => {
  const start = (paginaActual.value - 1) * porPagina.value;
  return resultadosFiltrados.value.slice(start, start + porPagina.value);
});

watch(ordenPrecio, () => { paginaActual.value = 1; });
watch(busquedaTabla, () => { paginaActual.value = 1; });
watch(filtroTabla, () => { paginaActual.value = 1; });
watch(porPagina, () => { paginaActual.value = 1; });

async function verDetalle(item) {
  modalDetalle.value = true;
  loadingDetalle.value = true;
  detalle.value = null;
  errorDetalle.value = '';
  try {
    const r = await fetch('/consulta-precios/detalle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      body: JSON.stringify({ codigoProducto: item.codProdE, codEstablecimiento: item.codEstab, tokenGoogle: '' }),
    });
    const data = await r.json();
    if (data.success && data.data) detalle.value = data.data;
    else errorDetalle.value = data.message ?? 'No se pudo obtener el detalle.';
  } catch {
    errorDetalle.value = 'Error de conexión al obtener el detalle.';
  } finally {
    loadingDetalle.value = false;
  }
}

async function exportarXLSX() {
  const XLSX = window.XLSX ?? (await import('xlsx').catch(() => null));
  if (!XLSX) {
    alert('No se pudo cargar la librería de exportación. Recargue la página.');
    return;
  }
  const cols = ['Tipo', 'Farmacia', 'Producto', 'Concentración', 'Laboratorio', 'Departamento', 'Provincia', 'Distrito', 'P. Pack (S/)', 'P. Unitario (S/)'];
  const rows = resultadosFiltrados.value.map((r) => [
    r.setcodigo ?? '',
    r.nombreComercial ?? '',
    r.nombreProducto ?? r.nombreSustancia ?? '',
    r.concent ?? '',
    r.nombreLaboratorio ?? '',
    r.departamento ?? '',
    r.provincia ?? '',
    r.distrito ?? '',
    r.precio1 != null ? Number(r.precio1) : '',
    r.precio2 != null ? Number(r.precio2) : '',
  ]);
  const ws = XLSX.utils.aoa_to_sheet([cols, ...rows]);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Precios');
  const nombre = (productoSeleccionado.value ? productoSeleccionado.value.nombreProducto + '_' + productoSeleccionado.value.concent : 'precios').replace(/\s+/g, '_').slice(0, 40);
  XLSX.writeFile(wb, `precios_${nombre}_${new Date().toISOString().slice(0, 10)}.xlsx`);
}
</script>
