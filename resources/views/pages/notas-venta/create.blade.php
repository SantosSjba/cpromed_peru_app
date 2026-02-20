@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6"
        x-data="notaVentaForm()">
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('notas-venta.store') }}" id="form-nota-venta">
            @csrf
            <input type="hidden" name="generar_pdf" value="0" id="generar_pdf" />

            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del negocio</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">RUC</label>
                        <input type="text" name="ruc" value="{{ old('ruc', '10477674327') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Razón social</label>
                        <input type="text" name="razon_social" value="{{ old('razon_social', 'TOPICO LOS ANGELES') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Sucursal</label>
                        <input type="text" name="sucursal" value="{{ old('sucursal') }}" class="input-field" />
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos de la boleta</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Nº Correlativo</label>
                        <input type="text" name="boleta_numero" x-model="boletaNumero" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha emisión</label>
                        <input type="date" name="boleta_fecha_emision" x-model="fechaEmision" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha vencimiento</label>
                        <input type="date" name="boleta_fecha_vencimiento" value="{{ old('boleta_fecha_vencimiento') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Moneda</label>
                        <select name="boleta_moneda" class="input-field">
                            <option value="Soles" {{ old('boleta_moneda', 'Soles') == 'Soles' ? 'selected' : '' }}>Soles</option>
                            <option value="Dólares" {{ old('boleta_moneda') == 'Dólares' ? 'selected' : '' }}>Dólares</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Forma de pago</label>
                        <input type="text" name="boleta_forma_pago" value="{{ old('boleta_forma_pago', 'Contado') }}" class="input-field" />
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del cliente</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre') }}" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">DNI/RUC <span class="text-red-500">*</span></label>
                        <input type="text" name="cliente_dni_ruc" value="{{ old('cliente_dni_ruc') }}" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dirección</label>
                        <input type="text" name="cliente_direccion" value="{{ old('cliente_direccion') }}" class="input-field" />
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Detalles</h4>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Descripción</th>
                                <th class="px-3 py-2 text-left font-medium w-24">Cantidad</th>
                                <th class="px-3 py-2 text-left font-medium w-28">P. unitario</th>
                                <th class="px-3 py-2 text-left font-medium w-24">Dscto.</th>
                                <th class="px-3 py-2 text-left font-medium w-28">Importe</th>
                                <th class="px-3 py-2 w-20"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="detalles-tbody">
                            <template x-for="(detalle, index) in detalles" :key="index">
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'detalles[' + index + '][descripcion]'" x-model="detalle.descripcion" class="input-field w-full" required />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" :name="'detalles[' + index + '][cantidad]'" x-model.number="detalle.cantidad" @input="calcularImporte(index)" class="input-field w-full" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" :name="'detalles[' + index + '][precio_unitario]'" x-model.number="detalle.precio_unitario" @input="calcularImporte(index)" class="input-field w-full" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" :name="'detalles[' + index + '][descuento_unitario]'" x-model.number="detalle.descuento_unitario" @input="calcularImporte(index)" class="input-field w-full" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="hidden" :name="'detalles[' + index + '][importe_total_unitario]'" :value="detalle.importe_total_unitario" />
                                        <span x-text="detalle.importe_total_unitario.toFixed(2)"></span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" @click="eliminarDetalle(index)" class="text-red-600 hover:underline text-sm">Quitar</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <button type="button" @click="agregarDetalle()" class="mt-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                    + Agregar detalle
                </button>
            </div>

            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Totales</h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Descuento total</label>
                        <input type="number" step="0.01" name="descuento_total" x-model.number="descuentoTotal" @input="calcularTotales()" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Subtotal</label>
                        <input type="number" step="0.01" name="subtotal" x-model="subtotal" readonly class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">IGV</label>
                        <input type="number" step="0.01" name="igv" x-model="igv" readonly class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Total</label>
                        <input type="number" step="0.01" name="total" x-model="total" readonly class="input-field" required />
                    </div>
                </div>
                <div class="mt-2">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Notas</label>
                    <textarea name="notas" rows="2" class="input-field w-full">{{ old('notas') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Guardar
                </button>
                <button type="button" @click="submitConPdf()" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Guardar y descargar PDF
                </button>
                <a href="{{ route('notas-venta.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        function notaVentaForm() {
            const ahora = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const numero = ahora.getFullYear().toString().slice(-2) + pad(ahora.getMonth()+1) + pad(ahora.getDate()) + '-' + pad(ahora.getHours()) + pad(ahora.getMinutes()) + pad(ahora.getSeconds());

            return {
                boletaNumero: numero,
                fechaEmision: ahora.toISOString().split('T')[0],
                detalles: [{ descripcion: '', cantidad: 0, precio_unitario: 0, descuento_unitario: 0, importe_total_unitario: 0 }],
                descuentoTotal: 0,
                subtotal: 0,
                igv: 0,
                total: 0,
                agregarDetalle() {
                    this.detalles.push({ descripcion: '', cantidad: 0, precio_unitario: 0, descuento_unitario: 0, importe_total_unitario: 0 });
                },
                eliminarDetalle(i) {
                    if (this.detalles.length <= 1) return;
                    this.detalles.splice(i, 1);
                    this.calcularTotales();
                },
                calcularImporte(i) {
                    const d = this.detalles[i];
                    d.importe_total_unitario = (d.cantidad || 0) * (d.precio_unitario || 0) - (d.descuento_unitario || 0);
                    this.calcularTotales();
                },
                calcularTotales() {
                    let totalBruto = this.detalles.reduce((s, d) => s + (d.importe_total_unitario || 0), 0);
                    this.subtotal = (totalBruto / 1.18).toFixed(2);
                    this.igv = (totalBruto - parseFloat(this.subtotal)).toFixed(2);
                    this.total = (totalBruto - (this.descuentoTotal || 0)).toFixed(2);
                },
                submitConPdf() {
                    document.getElementById('generar_pdf').value = '1';
                    document.getElementById('form-nota-venta').submit();
                }
            };
        }
    </script>

    <style>
        .input-field {
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
        .input-field::placeholder {
            color: #6b7280;
        }
        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .input-field[readonly] {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #1f2937;
        }
        select.input-field {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        textarea.input-field {
            min-height: 80px;
            resize: vertical;
        }
        /* Dark mode */
        .dark .input-field {
            border-color: #4b5563;
            background: #1f2937;
            color: #f9fafb;
        }
        .dark .input-field::placeholder {
            color: #9ca3af;
        }
        .dark .input-field:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.3);
        }
        .dark .input-field[readonly] {
            background: rgba(55, 65, 81, 0.9);
            border-color: #4b5563;
            color: #e5e7eb;
        }
    </style>
@endsection
