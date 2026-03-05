@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Paciente: <strong>{{ $paciente->nombre_completo }}</strong> —
            Consulta registrada en la tabla de consultas: <strong>{{ $consulta->fecha_consulta->format('d/m/Y H:i') }}</strong>
        </p>

        <div class="space-y-4 text-sm">
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Motivo de consulta</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->motivo_consulta ?: '—' }}</p>
            </div>
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Enfermedad actual</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->enfermedad_actual ?: '—' }}</p>
            </div>
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Dx (Diagnóstico)</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->dx ?: '—' }}</p>
            </div>
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Tx (Tratamiento)</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->tx ?: '—' }}</p>
            </div>
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Plan Dx.</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->plan_dx ?: '—' }}</p>
            </div>
            <div>
                <h4 class="text-gray-500 dark:text-gray-400 font-medium mb-1">Recomendaciones</h4>
                <p class="whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $consulta->recomendaciones ?: '—' }}</p>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('historia-clinica.show', $paciente) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                Volver a historia clínica
            </a>
        </div>
    </div>
@endsection
