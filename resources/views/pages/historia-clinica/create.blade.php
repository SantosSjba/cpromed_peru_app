@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Historia clínica', 'url' => route('historia-clinica.index')], ['label' => 'Nueva historia clínica', 'url' => null]]" />
    <div class="space-y-6">
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50/80 p-4 text-sm font-medium text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('historia-clinica.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Datos personales</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">La edad se calculará automáticamente desde la fecha de nacimiento.</p>
                </div>
                <div class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Nombres <span class="text-red-500">*</span></label>
                        <input type="text" name="nombres" value="{{ old('nombres') }}" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Apellidos <span class="text-red-500">*</span></label>
                        <input type="text" name="apellidos" value="{{ old('apellidos') }}" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de nacimiento <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="input-field" required />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Género <span class="text-red-500">*</span></label>
                        <select name="genero" class="input-field" required>
                            <option value="">Seleccione</option>
                            <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('genero') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">N.º Celular</label>
                        <input type="text" name="celular" value="{{ old('celular') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Ocupación</label>
                        <input type="text" name="ocupacion" value="{{ old('ocupacion') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">DNI</label>
                        <input type="text" name="dni" value="{{ old('dni') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-field" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Grupo sanguíneo</label>
                        <input type="text" name="grupo_sanguineo" value="{{ old('grupo_sanguineo') }}" class="input-field" placeholder="Ej. O+" />
                    </div>
                </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Antecedentes</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Historia médica, personal y familiar</p>
                </div>
                <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes médicos</label>
                        <textarea name="antecedentes_medicos" rows="3" class="input-field w-full">{{ old('antecedentes_medicos') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes personales</label>
                        <textarea name="antecedentes_personales" rows="4" class="input-field w-full">{{ old('antecedentes_personales') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Antecedentes familiares</label>
                        <textarea name="antecedentes_familiares" rows="4" class="input-field w-full">{{ old('antecedentes_familiares') }}</textarea>
                    </div>
                </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Historia</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Enfermedades previas, cirugías, alergias y medicamentos</p>
                </div>
                <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Enfermedades previas</label>
                        <textarea name="enfermedades_previas" rows="3" class="input-field w-full">{{ old('enfermedades_previas') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Cirugías</label>
                        <div class="flex gap-4 items-center mb-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="cirugias_si_no" value="1" {{ old('cirugias_si_no', '0') == '1' ? 'checked' : '' }} />
                                <span>Sí</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="cirugias_si_no" value="0" {{ old('cirugias_si_no', '0') == '0' ? 'checked' : '' }} />
                                <span>No</span>
                            </label>
                        </div>
                        <textarea name="cirugias_detalle" rows="2" class="input-field w-full" placeholder="Detalle de cirugías si aplica">{{ old('cirugias_detalle') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Alergias</label>
                        <textarea name="alergias" rows="2" class="input-field w-full">{{ old('alergias') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Medicamentos actuales</label>
                        <textarea name="medicamentos_actuales" rows="2" class="input-field w-full">{{ old('medicamentos_actuales') }}</textarea>
                    </div>
                </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Primera consulta</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Se guardará como la primera fila en la tabla de consultas. Luego podrás agregar más desde la ficha del paciente.</p>
                </div>
                <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-gray-200">Fecha de consulta <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="fecha_consulta" value="{{ old('fecha_consulta', now()->format('Y-m-d\TH:i')) }}" class="input-field" required />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 mt-4">
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
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar historia clínica
                </button>
                <a href="{{ route('historia-clinica.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
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
            font-weight: 500;
            color: #111827;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        textarea.input-field {
            min-height: 80px;
            resize: vertical;
            height: auto;
        }
        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
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
        .dark .input-field {
            border-color: #4b5563;
            background: #1f2937;
            color: #f9fafb;
        }
        .dark .input-field:focus {
            border-color: #60a5fa;
        }
    </style>
@endsection
