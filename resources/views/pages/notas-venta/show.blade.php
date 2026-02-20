@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <div class="mb-4 flex flex-wrap gap-3">
            <a href="{{ route('notas-venta.pdf', ['id' => $nota->id]) }}" target="_blank"
                class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Descargar PDF
            </a>
            <a href="{{ route('notas-venta.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                Volver al listado
            </a>
        </div>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nº Documento</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $nota->numero_documento }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $nota->cliente['nombre'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DNI/RUC</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $nota->cliente['dni_ruc'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Razón social</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $nota->razon_social ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha emisión</dt>
                <dd class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $nota->boleta['fecha_emision'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">S/ {{ number_format($nota->total, 2) }}</dd>
            </div>
        </dl>

        @if($nota->detalles && count($nota->detalles) > 0)
            <div class="mt-6">
                <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Detalles</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Descripción</th>
                                <th class="px-4 py-2 text-right font-medium">Cant.</th>
                                <th class="px-4 py-2 text-right font-medium">P. unit.</th>
                                <th class="px-4 py-2 text-right font-medium">Dscto.</th>
                                <th class="px-4 py-2 text-right font-medium">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($nota->detalles as $d)
                                <tr>
                                    <td class="px-4 py-2">{{ $d['descripcion'] ?? '' }}</td>
                                    <td class="px-4 py-2 text-right">{{ $d['cantidad'] ?? 0 }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($d['precio_unitario'] ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($d['descuento_unitario'] ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($d['importe_total_unitario'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
