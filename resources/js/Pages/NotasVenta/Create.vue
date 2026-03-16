<template>
  <AppLayout>
    <div class="space-y-6">
      <PageBreadcrumb :page-title="title" :items="[{ label: 'Notas de venta', url: '/notas-venta' }, { label: 'Nueva nota de venta', url: null }]" />
      <Card title="Nueva nota de venta">
        <Alert v-if="Object.keys(form.errors).length" variant="error" title="Errores de validación">
          <ul class="list-inside list-disc text-sm">
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </Alert>

        <form @submit.prevent="submit(false)" id="form-nota-venta" class="space-y-6">
          <div>
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del negocio</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <FormInput v-model="form.ruc" label="RUC" />
              <FormInput v-model="form.razon_social" label="Razón social" />
              <FormInput v-model="form.direccion" label="Dirección" />
              <FormInput v-model="form.sucursal" label="Sucursal" />
            </div>
          </div>

          <div>
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos de la boleta</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <FormInput v-model="form.boleta_numero" label="Nº Correlativo" required />
              <FormInput v-model="form.boleta_fecha_emision" type="date" label="Fecha emisión" required />
              <FormInput v-model="form.boleta_fecha_vencimiento" type="date" label="Fecha vencimiento" />
              <FormSelect v-model="form.boleta_moneda" label="Moneda">
                <option value="Soles">Soles</option>
                <option value="Dólares">Dólares</option>
              </FormSelect>
              <FormInput v-model="form.boleta_forma_pago" label="Forma de pago" />
            </div>
          </div>

          <div>
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del cliente</h4>
            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Ingrese el DNI o RUC; si el cliente está en el sistema se completarán nombre y dirección.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <FormInput
                v-model="form.cliente_dni_ruc"
                label="DNI / RUC"
                placeholder="Ej. 12345678"
                required
                @update:model-value="debouncedBuscarCliente()"
                @blur="buscarClientePorDni()"
              />
              <FormInput v-model="form.cliente_nombre" label="Nombre completo" placeholder="Nombres y apellidos" required />
              <FormInput v-model="form.cliente_direccion" label="Dirección" placeholder="Dirección del cliente" />
            </div>
            <p v-if="clienteEstado === 'loading'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Buscando cliente…</p>
            <Alert v-else-if="clienteEstado === 'found'" variant="success" message="Cliente encontrado. Puede editar los datos si lo desea." />
            <p v-else-if="clienteEstado === 'new'" class="mt-2 text-sm text-gray-500 dark:text-gray-400">No registrado. Se registrará como nuevo cliente al guardar la nota.</p>
          </div>

          <div>
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
                      <FormInput v-model="detalle.descripcion" label="" wrapper-class="!mb-0" required />
                    </td>
                    <td class="px-4 py-2">
                      <FormInput v-model.number="detalle.cantidad" type="number" step="0.01" label="" wrapper-class="!mb-0" @update:model-value="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <FormInput v-model.number="detalle.precio_unitario" type="number" step="0.01" label="" wrapper-class="!mb-0" @update:model-value="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <FormInput v-model.number="detalle.descuento_unitario" type="number" step="0.01" label="" wrapper-class="!mb-0" @update:model-value="calcularImporte(index)" />
                    </td>
                    <td class="px-4 py-2">
                      <span class="font-medium">{{ (detalle.importe_total_unitario || 0).toFixed(2) }}</span>
                    </td>
                    <td class="px-4 py-2">
                      <Button type="button" variant="outlineDanger" size="sm" @click="eliminarDetalle(index)">Quitar</Button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <Button type="button" variant="outline" start-icon="mdi:plus" className="mt-3" @click="agregarDetalle">Agregar detalle</Button>
          </div>

          <div>
            <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Totales</h4>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              <FormInput v-model.number="form.descuento_total" type="number" step="0.01" label="Descuento total" @update:model-value="calcularTotales" />
              <FormInput :model-value="form.subtotal" type="number" step="0.01" label="Subtotal" readonly />
              <FormInput :model-value="form.igv" type="number" step="0.01" label="IGV" readonly />
              <FormInput :model-value="form.total" type="number" step="0.01" label="Total" input-class="font-semibold" readonly required />
            </div>
            <div class="mt-4">
              <FormTextarea v-model="form.notas" label="Notas" :rows="2" placeholder="Observaciones opcionales" />
            </div>
          </div>

          <div class="flex flex-wrap gap-3">
            <Button type="submit">Guardar</Button>
            <Button type="button" variant="outline" @click="submit(true)">Guardar y descargar PDF</Button>
            <Link href="/notas-venta" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</Link>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PageBreadcrumb from '@/components/PageBreadcrumb.vue';
import { Card, Button, Alert } from '@/components/ui';
import { FormInput, FormSelect, FormTextarea } from '@/components/form';

const props = defineProps({
  title: { type: String, default: 'Nueva nota de venta' },
  clienteInit: { type: Object, default: () => ({}) },
});

let dniDebounceTimer = null;
function debouncedBuscarCliente() {
  clearTimeout(dniDebounceTimer);
  dniDebounceTimer = setTimeout(() => buscarClientePorDni(), 400);
}

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
