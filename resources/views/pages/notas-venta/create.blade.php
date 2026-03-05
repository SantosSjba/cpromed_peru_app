@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Notas de venta', 'url' => route('notas-venta.index')], ['label' => 'Nueva nota de venta', 'url' => null]]" />
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-white/[0.02] lg:p-6"
            x-data="notaVentaForm({{ json_encode($clienteInit, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }})">
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
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
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">RUC</label>
                            <input type="text" name="ruc" value="{{ old('ruc', '10477674327') }}" class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Razón social</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social', 'CPROMED PERU') }}" class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion') }}" class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Sucursal</label>
                            <input type="text" name="sucursal" value="{{ old('sucursal') }}" class="input-field" />
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos de la boleta</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nº Correlativo</label>
                            <input type="text" name="boleta_numero" x-model="boletaNumero" class="input-field" required />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha emisión</label>
                            <input type="date" name="boleta_fecha_emision" x-model="fechaEmision" class="input-field" required />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha vencimiento</label>
                            <input type="date" name="boleta_fecha_vencimiento" value="{{ old('boleta_fecha_vencimiento') }}" class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Moneda</label>
                            <select name="boleta_moneda" class="input-field">
                                <option value="Soles" {{ old('boleta_moneda', 'Soles') == 'Soles' ? 'selected' : '' }}>Soles</option>
                                <option value="Dólares" {{ old('boleta_moneda') == 'Dólares' ? 'selected' : '' }}>Dólares</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Forma de pago</label>
                            <input type="text" name="boleta_forma_pago" value="{{ old('boleta_forma_pago', 'Contado') }}" class="input-field" />
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos del cliente</h4>
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Ingrese el DNI o RUC; si el cliente está en el sistema se completarán nombre y dirección. Si no existe, se registrará como nuevo al guardar la nota.</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">DNI / RUC <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="cliente_dni_ruc"
                                x-model="clienteDniRuc"
                                @blur="buscarClientePorDni()"
                                placeholder="Ej. 12345678"
                                class="input-field"
                                required />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="cliente_nombre"
                                x-model="clienteNombre"
                                placeholder="Nombres y apellidos"
                                class="input-field"
                                required />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                            <input type="text"
                                name="cliente_direccion"
                                x-model="clienteDireccion"
                                placeholder="Dirección del cliente"
                                class="input-field" />
                        </div>
                    </div>
                    <div class="mt-2 text-sm" x-show="clienteEstado === 'loading'" x-cloak>
                        <span class="text-gray-500 dark:text-gray-400">Buscando cliente…</span>
                    </div>
                    <div class="mt-2 text-sm" x-show="clienteEstado === 'found'" x-cloak>
                        <span class="text-green-600 dark:text-green-400">Cliente encontrado. Puede editar los datos si lo desea.</span>
                    </div>
                    <div class="mt-2 text-sm" x-show="clienteEstado === 'new'" x-cloak>
                        <span class="text-gray-500 dark:text-gray-400">No registrado. Se registrará como nuevo cliente al guardar la nota.</span>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Detalles</h4>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full min-w-[700px] text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-28">Cantidad</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-28">P. unitario</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-24">Dscto.</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-28">Importe</th>
                                    <th class="px-4 py-3 w-20"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(detalle, index) in detalles" :key="index">
                                    <tr>
                                        <td class="px-4 py-2">
                                            <input type="text" :name="'detalles[' + index + '][descripcion]'" x-model="detalle.descripcion" class="input-field w-full" required />
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" step="0.01" :name="'detalles[' + index + '][cantidad]'" x-model.number="detalle.cantidad" @input="calcularImporte(index)" class="input-field w-full" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" step="0.01" :name="'detalles[' + index + '][precio_unitario]'" x-model.number="detalle.precio_unitario" @input="calcularImporte(index)" class="input-field w-full" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="number" step="0.01" :name="'detalles[' + index + '][descuento_unitario]'" x-model.number="detalle.descuento_unitario" @input="calcularImporte(index)" class="input-field w-full" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="hidden" :name="'detalles[' + index + '][importe_total_unitario]'" :value="detalle.importe_total_unitario" />
                                            <span class="font-medium" x-text="detalle.importe_total_unitario.toFixed(2)"></span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <button type="button" @click="eliminarDetalle(index)" class="rounded-lg px-2 py-1 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Quitar</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" @click="agregarDetalle()" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Agregar detalle
                    </button>
                </div>

                <div class="mb-6">
                    <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Totales</h4>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento total</label>
                            <input type="number" step="0.01" name="descuento_total" x-model.number="descuentoTotal" @input="calcularTotales()" class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</label>
                            <input type="number" step="0.01" name="subtotal" x-model="subtotal" readonly class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">IGV</label>
                            <input type="number" step="0.01" name="igv" x-model="igv" readonly class="input-field" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Total</label>
                            <input type="number" step="0.01" name="total" x-model="total" readonly class="input-field font-semibold" required />
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notas</label>
                        <textarea name="notas" rows="2" class="input-field w-full" placeholder="Observaciones opcionales">{{ old('notas') }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600">
                        Guardar
                    </button>
                    <button type="button" @click="submitConPdf()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Guardar y descargar PDF
                    </button>
                    <a href="{{ route('notas-venta.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function notaVentaForm(init) {
            init = init || {};
            const ahora = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const numero = ahora.getFullYear().toString().slice(-2) + pad(ahora.getMonth()+1) + pad(ahora.getDate()) + '-' + pad(ahora.getHours()) + pad(ahora.getMinutes()) + pad(ahora.getSeconds());

            return {
                boletaNumero: numero,
                fechaEmision: ahora.toISOString().split('T')[0],
                clienteNombre: init.clienteNombre || '',
                clienteDniRuc: init.clienteDniRuc || '',
                clienteDireccion: init.clienteDireccion || '',
                clienteEstado: '', // '', 'loading', 'found', 'new'
                detalles: [{ descripcion: '', cantidad: 0, precio_unitario: 0, descuento_unitario: 0, importe_total_unitario: 0 }],
                descuentoTotal: 0,
                subtotal: 0,
                igv: 0,
                total: 0,
                async buscarClientePorDni() {
                    const dni = (this.clienteDniRuc || '').trim();
                    if (!dni) {
                        this.clienteEstado = '';
                        return;
                    }
                    this.clienteEstado = 'loading';
                    try {
                        const url = '{{ route("notas-venta.cliente-por-dni") }}?dni=' + encodeURIComponent(dni);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.found) {
                            this.clienteNombre = data.nombre || this.clienteNombre;
                            this.clienteDireccion = data.direccion || '';
                            this.clienteEstado = 'found';
                        } else {
                            this.clienteEstado = 'new';
                        }
                    } catch (e) {
                        this.clienteEstado = 'new';
                    }
                },
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
