@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Historia clínica', 'url' => route('historia-clinica.index')], ['label' => $paciente->nombre_completo, 'url' => route('historia-clinica.ver', ['id' => $paciente->id])], ['label' => 'Nueva consulta', 'url' => null]]" />
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Paciente: <strong>{{ $paciente->nombre_completo }}</strong>. Esta consulta se guardará como una nueva fila en la tabla de consultas del paciente (fecha, motivo, enfermedad actual, Dx, Tx, Plan Dx, recomendaciones).</p>

        <form method="POST" action="{{ route('historia-clinica.consultas.store') }}">
            @csrf
            <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
            <div class="mb-6">
                <h4 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Datos de la consulta</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de consulta <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="fecha_consulta" value="{{ old('fecha_consulta', now()->format('Y-m-d\TH:i')) }}" class="input-field" required />
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Motivo de consulta</label>
                        <textarea name="motivo_consulta" rows="3" class="input-field w-full">{{ old('motivo_consulta') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Enfermedad actual</label>
                        <textarea name="enfermedad_actual" rows="3" class="input-field w-full">{{ old('enfermedad_actual') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dx (Diagnóstico)</label>
                        <textarea name="dx" rows="3" class="input-field w-full">{{ old('dx') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Tx (Tratamiento)</label>
                        <textarea name="tx" rows="3" class="input-field w-full">{{ old('tx') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Plan Dx.</label>
                        <textarea name="plan_dx" rows="2" class="input-field w-full">{{ old('plan_dx') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Recomendaciones</label>
                        <textarea name="recomendaciones" rows="2" class="input-field w-full">{{ old('recomendaciones') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Guardar consulta
                </button>
                <a href="{{ route('historia-clinica.ver', ['id' => $paciente->id]) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
    <style>
        .input-field {
            height: 2.75rem;
            width: 100%;
            border-radius: 0.5rem;
            border: 2px solid #9ca3af;
            background: #fff;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        textarea.input-field { min-height: 80px; resize: vertical; height: auto; }
        .input-field:focus { outline: none; border-color: #3b82f6; }
        .dark .input-field { border-color: #4b5563; background: #1f2937; color: #f9fafb; }
    </style>
@endsection
