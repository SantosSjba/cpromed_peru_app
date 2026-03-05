@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Historia clínica', 'url' => route('historia-clinica.index')], ['label' => $paciente->nombre_completo, 'url' => route('historia-clinica.show', $paciente)], ['label' => 'Consulta ' . $consulta->fecha_consulta->format('d/m/Y'), 'url' => null]]" />
    <div class="space-y-6">
        {{-- Cabecera: paciente + fecha de consulta --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-2xl font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                        {{ strtoupper(mb_substr($paciente->nombres, 0, 1) . mb_substr($paciente->apellidos, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $paciente->nombre_completo }}
                        </h1>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            Consulta del {{ $consulta->fecha_consulta->format('d/m/Y') }} a las {{ $consulta->fecha_consulta->format('H:i') }}
                        </p>
                        @if($consulta->created_at)
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                Registrada el {{ $consulta->created_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('historia-clinica.show', $paciente) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver a historia clínica
                    </a>
                </div>
            </div>
        </div>

        {{-- Detalle de la consulta --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalle de la consulta</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Motivo, enfermedad actual, diagnóstico y tratamiento</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Motivo de consulta</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->motivo_consulta ?: '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Enfermedad actual</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->enfermedad_actual ?: '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dx (Diagnóstico)</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->dx ?: '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Tx (Tratamiento)</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->tx ?: '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Plan Dx.</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->plan_dx ?: '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Recomendaciones</p>
                    <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900 dark:text-white">{{ $consulta->recomendaciones ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('historia-clinica.show', $paciente) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a historia clínica
            </a>
        </div>
    </div>
@endsection
