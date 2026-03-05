@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Nota {{ $nota->numero_documento }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Cliente: {{ $nota->cliente['nombre'] ?? '—' }} · Total S/ {{ number_format($nota->total, 2) }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('notas-venta.pdf', ['id' => $nota->id]) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar PDF
                </a>
                <a href="{{ route('notas-venta.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    Volver al listado
                </a>
                <form action="{{ route('notas-venta.destroy', $nota) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta nota de venta?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-300 px-5 py-3 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Datos generales</h2>
            </div>
            <dl class="grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nº Documento</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $nota->numero_documento }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $nota->cliente['nombre'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DNI/RUC</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $nota->cliente['dni_ruc'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección cliente</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $nota->cliente['direccion'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Razón social</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $nota->razon_social ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha emisión</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ isset($nota->boleta['fecha_emision']) ? \Carbon\Carbon::parse($nota->boleta['fecha_emision'])->format('d/m/Y') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Moneda / Forma de pago</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $nota->boleta['moneda'] ?? 'Soles' }} · {{ $nota->boleta['forma_pago'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">S/ {{ number_format($nota->total, 2) }}</dd>
                </div>
            </dl>
            @if($nota->notas)
                <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas</dt>
                    <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $nota->notas }}</dd>
                </div>
            @endif
        </div>

        @if($nota->detalles && count($nota->detalles) > 0)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
                <div class="border-b border-gray-200 bg-gray-50/80 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/80">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalles</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                                <th class="px-5 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Cant.</th>
                                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">P. unit.</th>
                                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Dscto.</th>
                                <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($nota->detalles as $d)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-3 text-gray-900 dark:text-white">{{ $d['descripcion'] ?? '' }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($d['cantidad'] ?? 0, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($d['precio_unitario'] ?? 0, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format($d['descuento_unitario'] ?? 0, 2) }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($d['importe_total_unitario'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap justify-end gap-6 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">S/ {{ number_format($nota->subtotal, 2) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">IGV</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">S/ {{ number_format($nota->igv, 2) }}</span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Total</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">S/ {{ number_format($nota->total, 2) }}</span>
                </div>
            </div>
        @endif
    </div>
@endsection
