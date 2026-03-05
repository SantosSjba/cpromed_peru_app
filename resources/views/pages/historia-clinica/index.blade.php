@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Historia clínica', 'url' => null]]" />
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50/80 p-4 text-sm font-medium text-green-800 shadow-sm dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm font-medium text-amber-800 shadow-sm dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                {{-- <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Historia clínica
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Listado de pacientes con su historia clínica, consultas y exámenes.
                </p> --}}
            </div>
            <a href="{{ route('historia-clinica.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-600 hover:shadow-brand-500/30">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva historia clínica
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.02]"
            x-data="{ debounceTimer: null, debounceMs: 400 }">
            <form action="{{ route('historia-clinica.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center" x-ref="searchForm">
                <label for="buscar" class="sr-only">Buscar por nombre, apellidos o DNI</label>
                <input type="search"
                    id="buscar"
                    name="buscar"
                    value="{{ old('buscar', $buscar ?? '') }}"
                    placeholder="Buscar por nombre, apellidos o DNI..."
                    @input="clearTimeout(debounceTimer); debounceTimer = setTimeout(() => $refs.searchForm.submit(), debounceMs)"
                    class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Buscar
                </button>
                @if($buscar ?? '')
                    <a href="{{ route('historia-clinica.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        Limpiar
                    </a>
                @endif
            </form>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">La búsqueda se ejecuta automáticamente al dejar de escribir (debounce 400 ms).</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.02]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                            <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Paciente</th>
                            <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">DNI</th>
                            <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Contacto</th>
                            <th class="px-5 py-4 font-semibold text-gray-700 dark:text-gray-200">Registro</th>
                            <th class="px-5 py-4 text-right font-semibold text-gray-700 dark:text-gray-200">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pacientes as $p)
                            <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $p->nombres }} {{ $p->apellidos }}</span>
                                </td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $p->dni ?? '—' }}</td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $p->celular ?? $p->email ?? '—' }}</td>
                                <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $p->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('historia-clinica.ver', ['id' => $p->id]) }}" class="rounded-lg px-3 py-1.5 text-brand-600 font-medium hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                            Ver
                                        </a>
                                        <a href="{{ route('historia-clinica.pdf', ['id' => $p->id]) }}" class="rounded-lg px-3 py-1.5 text-gray-600 font-medium hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                                            PDF
                                        </a>
                                        <a href="{{ route('historia-clinica.edit', $p) }}" class="rounded-lg px-3 py-1.5 text-gray-600 font-medium hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                                            Editar
                                        </a>
                                        <form action="{{ route('historia-clinica.eliminar') }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar toda la historia clínica de este paciente? Se borrarán sus consultas y exámenes.');">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $p->id }}">
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-red-600 font-medium hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No hay pacientes registrados.</p>
                                    <a href="{{ route('historia-clinica.create') }}" class="mt-3 inline-block text-brand-500 font-medium hover:underline">Crear primera historia clínica</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pacientes->total() > 0)
                <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                        Mostrando {{ $pacientes->firstItem() }} a {{ $pacientes->lastItem() }} de {{ $pacientes->total() }} paciente(s).
                    </p>
                    {{ $pacientes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
