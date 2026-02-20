@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Notas de venta
            </h3>
            <a href="{{ route('notas-venta.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                + Nueva nota de venta
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nº Documento</th>
                        <th class="px-4 py-3 font-medium">Cliente</th>
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3 font-medium text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($notas as $nota)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3">{{ $nota->numero_documento }}</td>
                            <td class="px-4 py-3">{{ $nota->cliente['nombre'] ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $nota->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right font-medium">S/ {{ number_format($nota->total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('notas-venta.show', $nota) }}" class="text-brand-500 hover:underline mr-2">Ver</a>
                                <a href="{{ route('notas-venta.pdf', ['id' => $nota->id]) }}" class="text-brand-500 hover:underline mr-2">PDF</a>
                                <form action="{{ route('notas-venta.destroy', $nota) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta nota de venta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No hay notas de venta. Crea una desde «Nueva nota de venta».
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notas->hasPages())
            <div class="mt-4">
                {{ $notas->links() }}
            </div>
        @endif
    </div>
@endsection
