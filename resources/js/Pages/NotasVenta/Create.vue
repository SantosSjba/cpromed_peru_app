<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Notas de venta', url: '/notas-venta' }, { label: 'Nueva nota de venta', url: null }]" />
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02] lg:p-6">
        <ul v-if="Object.keys(form.errors).length" class="mb-4 list-inside list-disc rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
          <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
        </ul>

        <form @submit.prevent="submit(false)" id="form-nota-venta">

          <div class="mb-6">
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del negocio</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">RUC</label>
                <input v-model="form.ruc" type="text" class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Razón social</label>
                <input v-model="form.razon_social" type="text" class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                <input v-model="form.direccion" type="text" class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal</label>
                <input v-model="form.sucursal" type="text" class="input-field-custom" />
              </div>
            </div>
          </div>

          <div class="mb-6">
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos de la boleta</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nº Correlativo</label>
                <input v-model="form.boleta_numero" type="text" class="input-field-custom" required />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha emisión</label>
                <input v-model="form.boleta_fecha_emision" type="date" class="input-field-custom" required />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha vencimiento</label>
                <input v-model="form.boleta_fecha_vencimiento" type="date" class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
                <select v-model="form.boleta_moneda" class="input-field-custom">
                  <option value="Soles">Soles</option>
                  <option value="Dólares">Dólares</option>
                </select>
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Forma de pago</label>
                <input v-model="form.boleta_forma_pago" type="text" class="input-field-custom" />
              </div>
            </div>
          </div>

          <div class="mb-6">
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del cliente</h4>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Ingrese el DNI o RUC; si el cliente está en el sistema se completarán nombre y dirección.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">DNI / RUC <span class="text-red-500">*</span></label>
                <input
                  v-model="form.cliente_dni_ruc"
                  type="text"
                  placeholder="Ej. 12345678"
                  class="input-field-custom"
                  @input="debouncedBuscarCliente()"
                  @blur="buscarClientePorDni()"
                  required
                />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo <span class="text-red-500">*</span></label>
                <input v-model="form.cliente_nombre" type="text" placeholder="Nombres y apellidos" class="input-field-custom" required />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                <input v-model="form.cliente_direccion" type="text" placeholder="Dirección del cliente" class="input-field-custom" />
              </div>
            </div>
            <p v-if="clienteEstado === 'loading'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Buscando cliente…</p>
            <p v-else-if="clienteEstado === 'found'" class="mt-2 text-sm text-green-600 dark:text-green-400">Cliente encontrado. Puede editar los datos si lo desea.</p>
            <p v-else-if="clienteEstado === 'new'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">No registrado. Se registrará como nuevo cliente al guardar la nota.</p>
          </div>

          <div class="mb-6">
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Detalles</h4>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
              <table class="w-full min-w-[700px] text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                    <th class="w-28 px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Cantidad</th>
                    <th class="w-28 px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">P. unitario</th>
                    <th class="w-24 px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Dscto.</th>
                    <th class="w-28 px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Importe</th>
                    <th class="w-20"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                  <tr v-for="(detalle, index) in detalles" :key="index">
                    <td class="px-4 py-2">
                      <input v-model="detalle.descripcion" type="text" class="input-field-custom w-full" required />
                    </td>
                    <td class="px-4 py-2">
                      <input v-model.number="detalle.cantidad" type="number" step="0.01" class="input-field-custom w-full" @input="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <input v-model.number="detalle.precio_unitario" type="number" step="0.01" class="input-field-custom w-full" @input="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <input v-model.number="detalle.descuento_unitario" type="number" step="0.01" class="input-field-custom w-full" @input="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <span class="font-medium">{{ (detalle.importe_total_unitario || 0).toFixed(2) }}</span>
                    </td>
                    <td class="px-4 py-2">
                      <button type="button" @click="eliminarDetalle(index)" class="rounded-lg px-2 py-1 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Quitar</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <button type="button" @click="agregarDetalle" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
              <Icon icon="mdi:plus" class="h-4 w-4" />
              Agregar detalle
            </button>
          </div>

          <div class="mb-6">
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Totales</h4>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento total</label>
                <input v-model.number="form.descuento_total" type="number" step="0.01" class="input-field-custom" @input="calcularTotales" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</label>
                <input :value="form.subtotal" type="number" step="0.01" readonly class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">IGV</label>
                <input :value="form.igv" type="number" step="0.01" readonly class="input-field-custom" />
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                <input v-model="form.total" type="number" step="0.01" readonly class="input-field-custom font-semibold" required />
              </div>
            </div>
            <div class="mt-4">
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
              <textarea v-model="form.notas" rows="2" class="input-field-custom w-full min-h-[5rem] resize-y" placeholder="Observaciones opcionales"></textarea>
            </div>
          </div>

          <div class="flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600">
              Guardar
            </button>
            <button type="button" @click="submit(true)" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              Guardar y descargar PDF
            </button>
            <Link href="/notas-venta" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';

let dniDebounceTimer = null;
function debouncedBuscarCliente() {
  clearTimeout(dniDebounceTimer);
  dniDebounceTimer = setTimeout(() => buscarClientePorDni(), 400);
}
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '../../components/PageBreadcrumb.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  title: { type: String, default: 'Nueva nota de venta' },
  clienteInit: { type: Object, default: () => ({}) },
});

const form = useForm({
  ruc: '',
  razon_social: '',
  direccion: '',
  sucursal: '',
  cliente_nombre: props.clienteInit.clienteNombre || '',
  cliente_dni_ruc: props.clienteInit.clienteDniRuc || '',
  cliente_direccion: props.clienteInit.clienteDireccion || '',
  boleta_numero: '',
  boleta_fecha_emision: '',
  boleta_fecha_vencimiento: '',
  boleta_moneda: 'Soles',
  boleta_forma_pago: 'Contado',
  descuento_total: 0,
  subtotal: 0,
  igv: 0,
  total: 0,
  notas: '',
  generar_pdf: '0',
  detalles: [],
});

const clienteEstado = ref('');
const detalles = ref([{ descripcion: '', cantidad: 0, precio_unitario: 0, descuento_unitario: 0, importe_total_unitario: 0 }]);

function defaultBoletaNumber() {
  const ahora = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  return ahora.getFullYear().toString().slice(-2) + pad(ahora.getMonth() + 1) + pad(ahora.getDate()) + '-' + pad(ahora.getHours()) + pad(ahora.getMinutes()) + pad(ahora.getSeconds());
}

onMounted(() => {
  form.boleta_numero = form.boleta_numero || defaultBoletaNumber();
  form.boleta_fecha_emision = form.boleta_fecha_emision || new Date().toISOString().split('T')[0];
  if (!form.ruc) form.ruc = '10477674327';
  if (!form.razon_social) form.razon_social = 'CPROMED PERU';
  if (!form.boleta_forma_pago) form.boleta_forma_pago = 'Contado';
  calcularTotales();
});

async function buscarClientePorDni() {
  const dni = (form.cliente_dni_ruc || '').trim();
  if (!dni) {
    clienteEstado.value = '';
    return;
  }
  clienteEstado.value = 'loading';
  try {
    const res = await fetch('/notas-venta/cliente-por-dni?dni=' + encodeURIComponent(dni), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json().catch(() => ({}));
    if (res.ok && data.found) {
      form.cliente_nombre = data.nombre || form.cliente_nombre;
      form.cliente_direccion = data.direccion || '';
      clienteEstado.value = 'found';
    } else {
      clienteEstado.value = 'new';
    }
  } catch {
    clienteEstado.value = 'new';
  }
}

function agregarDetalle() {
  detalles.value.push({ descripcion: '', cantidad: 0, precio_unitario: 0, descuento_unitario: 0, importe_total_unitario: 0 });
}

function eliminarDetalle(i) {
  if (detalles.value.length <= 1) return;
  detalles.value.splice(i, 1);
  calcularTotales();
}

function calcularImporte(i) {
  const d = detalles.value[i];
  d.importe_total_unitario = (d.cantidad || 0) * (d.precio_unitario || 0) - (d.descuento_unitario || 0);
  calcularTotales();
}

function calcularTotales() {
  const totalBruto = detalles.value.reduce((s, d) => s + (d.importe_total_unitario || 0), 0);
  form.subtotal = parseFloat((totalBruto / 1.18).toFixed(2));
  form.igv = parseFloat((totalBruto - form.subtotal).toFixed(2));
  form.total = parseFloat((totalBruto - (form.descuento_total || 0)).toFixed(2));
}

function submit(conPdf) {
  form.generar_pdf = conPdf ? '1' : '0';
  form.detalles = detalles.value.map((d) => ({
    descripcion: d.descripcion,
    cantidad: d.cantidad,
    precio_unitario: d.precio_unitario,
    descuento_unitario: d.descuento_unitario || 0,
    importe_total_unitario: d.importe_total_unitario || 0,
  }));
  form.post('/notas-venta');
}
</script>

<style scoped>
.input-field-custom {
  height: 2.75rem;
  width: 100%;
  border-radius: 0.5rem;
  border: 2px solid #9ca3af;
  background: #fff;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.input-field-custom:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}
.input-field-custom[readonly] {
  background: #f3f4f6;
  border-color: #9ca3af;
}
textarea.input-field-custom {
  min-height: 5rem;
}
select.input-field-custom {
  appearance: none;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>
