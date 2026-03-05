@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" :items="[['label' => 'Historia clínica', 'url' => route('historia-clinica.index')], ['label' => $paciente->nombre_completo, 'url' => null]]" />
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

        {{-- Cabecera del paciente --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-100 text-2xl font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                        {{ strtoupper(mb_substr($paciente->nombres, 0, 1) . mb_substr($paciente->apellidos, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $paciente->nombre_completo }}
                        </h1>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ $paciente->dni ? 'DNI ' . $paciente->dni : 'Sin DNI' }}
                            @if($paciente->edad_calculada !== null)
                                · {{ $paciente->edad_calculada }} años
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('historia-clinica.pdf', ['id' => $paciente->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Descargar PDF
                    </a>
                    <a href="{{ route('historia-clinica.edit', ['id' => $paciente->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                    <a href="{{ route('historia-clinica.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        Volver al listado
                    </a>
                    <form action="{{ route('historia-clinica.eliminar') }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar toda la historia clínica de este paciente? Se borrarán sus consultas y exámenes. Esta acción no se puede deshacer.');">
                        @csrf
                        <input type="hidden" name="id" value="{{ $paciente->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Eliminar historia clínica
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Datos personales --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Datos personales</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Información demográfica y de contacto</p>
            </div>
            <div class="grid grid-cols-1 gap-x-8 gap-y-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nombres</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->nombres }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Apellidos</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->apellidos }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Fecha de nacimiento</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Edad</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->edad_calculada !== null ? $paciente->edad_calculada . ' años' : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Género</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->genero === 'M' ? 'Masculino' : ($paciente->genero === 'F' ? 'Femenino' : $paciente->genero) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">DNI</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->dni ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dirección</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->direccion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Celular</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->celular ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Email</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Ocupación</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->ocupacion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Grupo sanguíneo</p>
                    <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $paciente->grupo_sanguineo ?? '—' }}</p>
                </div>
            </div>
        </div>

        @if($paciente->historiaClinicaFicha)
            {{-- Antecedentes --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Antecedentes</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Historia médica, personal y familiar</p>
                </div>
                <div class="space-y-5 p-6">
                    @foreach([
                        ['key' => 'antecedentes_medicos', 'label' => 'Antecedentes médicos'],
                        ['key' => 'antecedentes_personales', 'label' => 'Antecedentes personales'],
                        ['key' => 'antecedentes_familiares', 'label' => 'Antecedentes familiares'],
                        ['key' => 'enfermedades_previas', 'label' => 'Enfermedades previas'],
                        ['key' => 'alergias', 'label' => 'Alergias'],
                        ['key' => 'medicamentos_actuales', 'label' => 'Medicamentos actuales'],
                    ] as $item)
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $item['label'] }}</p>
                            <p class="mt-1.5 whitespace-pre-wrap rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-800 dark:bg-gray-800/50 dark:text-gray-200">{{ $paciente->historiaClinicaFicha->{$item['key']} ?: '—' }}</p>
                        </div>
                    @endforeach
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Cirugías</p>
                        <p class="mt-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $paciente->historiaClinicaFicha->cirugias_si_no ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $paciente->historiaClinicaFicha->cirugias_si_no ? 'Sí' : 'No' }}
                            </span>
                            @if($paciente->historiaClinicaFicha->cirugias_detalle)
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $paciente->historiaClinicaFicha->cirugias_detalle }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla de consultas --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Tabla de consultas</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cada fila es una consulta. Un paciente puede tener muchas consultas.</p>
                </div>
                <a href="{{ route('historia-clinica.consultas.create', ['paciente' => $paciente->id]) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva consulta
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                            <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200 w-16">Nº</th>
                            <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Fecha consulta</th>
                            <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Motivo</th>
                            <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Enfermedad actual</th>
                            <th class="px-5 py-3 font-semibold text-gray-700 dark:text-gray-200">Dx</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($paciente->historiaClinicaConsultas as $index => $c)
                            <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $paciente->historiaClinicaConsultas->count() - $index }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $c->fecha_consulta->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ Str::limit($c->motivo_consulta, 35) ?: '—' }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ Str::limit($c->enfermedad_actual, 35) ?: '—' }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ Str::limit($c->dx, 35) ?: '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('historia-clinica.consultas.show', ['paciente' => $paciente->id, 'consulta' => $c->id]) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Ver detalle</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No hay consultas. Agrega la primera con «Nueva consulta».</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Exámenes --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Exámenes</h2>
                <button type="button" onclick="document.getElementById('form-examen').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/25 hover:bg-brand-600 shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Subir examen
                </button>
            </div>
            <div id="form-examen" class="hidden border-b border-gray-200 p-6 dark:border-gray-700"
                x-data="examenMultiForm()">
                <form action="{{ route('historia-clinica.examenes.store') }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-dashed border-gray-300 bg-gray-50/80 p-6 dark:border-gray-600 dark:bg-gray-800/30">
                    @csrf
                    <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha del examen (común para todos)</label>
                        <input type="date" name="fecha_examen" value="{{ old('fecha_examen', now()->timezone('America/Lima')->format('Y-m-d')) }}" class="input-field max-w-xs" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">1. Seleccionar archivos</label>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input type="file"
                                x-ref="fileInput"
                                name="archivo[]"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                                multiple
                                @change="onFilesSelected($event)"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white file:hover:bg-brand-600 dark:text-gray-400 dark:file:bg-brand-600 dark:file:hover:bg-brand-500" />
                            <span class="text-sm text-gray-500 dark:text-gray-400" x-show="rows.length > 0" x-text="rows.length + ' archivo(s) seleccionado(s)'"></span>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">PDF o imágenes. Puede elegir varios a la vez.</p>
                    </div>

                    <div x-show="rows.length > 0" class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-600 dark:bg-gray-800/50">
                        <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">2. Complete Tipo y Descripción / Laboratorio por cada archivo</h4>
                        <ul class="space-y-3" role="list">
                            <template x-for="(row, index) in rows" :key="index">
                                <li class="flex flex-col gap-2 rounded-lg border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-800/80 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4">
                                    <div class="flex min-w-0 flex-1 items-center gap-2 sm:min-w-[180px]">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-xs font-bold text-brand-600 dark:bg-brand-500/20 dark:text-brand-400" x-text="index + 1"></span>
                                        <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200" :title="row.fileName" x-text="row.fileName"></span>
                                    </div>
                                    <div class="flex flex-1 flex-wrap gap-3 sm:flex-nowrap">
                                        <div class="min-w-0 flex-1 sm:max-w-[180px]">
                                            <label class="mb-0.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</label>
                                            <input type="text"
                                                :name="'tipo[' + index + ']'"
                                                x-model="row.tipo"
                                                class="input-field"
                                                placeholder="Ej. Laboratorio, Rayos X" />
                                        </div>
                                        <div class="min-w-0 flex-[2] sm:max-w-[280px]">
                                            <label class="mb-0.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Descripción / Laboratorio</label>
                                            <input type="text"
                                                :name="'descripcion[' + index + ']'"
                                                x-model="row.descripcion"
                                                class="input-field"
                                                placeholder="Ej. Hemograma, Perfil lipídico" />
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Los datos se guardan en el mismo orden que la lista. Puede cambiar la selección de archivos arriba para actualizar la lista.</p>
                    </div>

                    <div class="flex flex-wrap gap-3" x-show="rows.length > 0">
                        <button type="submit" class="rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                            Subir exámenes
                        </button>
                        <button type="button" @click="clearFiles()" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cambiar archivos
                        </button>
                    </div>
                </form>
                <script>
                    function examenMultiForm() {
                        return {
                            rows: [],
                            fileInput: null,
                            onFilesSelected(event) {
                                const files = event.target.files;
                                if (!files || files.length === 0) {
                                    this.rows = [];
                                    return;
                                }
                                this.rows = Array.from(files).map(function(f) {
                                    return { fileName: f.name, tipo: '', descripcion: '' };
                                });
                            },
                            clearFiles() {
                                this.rows = [];
                                var input = this.$refs.fileInput;
                                if (input) {
                                    input.value = '';
                                }
                            }
                        };
                    }
                </script>
            </div>
            <div class="p-6">
                @if(isset($examenesPorFecha) && $examenesPorFecha->isNotEmpty())
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Exámenes agrupados por fecha de realización (o fecha de subida si no se indicó fecha). Así se puede ver la evolución en el tiempo.</p>
                    @foreach($examenesPorFecha as $grupo)
                        <div class="mb-6 last:mb-0">
                            <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Exámenes del {{ $grupo['fecha']->format('d/m/Y') }}
                            </h3>
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                                            <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Archivo</th>
                                            <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Tipo</th>
                                            <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-200">Descripción</th>
                                            <th class="px-4 py-2.5 text-right font-semibold text-gray-700 dark:text-gray-200">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($grupo['items'] as $e)
                                            @php $esPdf = strtolower(pathinfo($e->file_name, PATHINFO_EXTENSION)) === 'pdf'; @endphp
                                            <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $e->file_name }}</td>
                                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $e->tipo ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $e->descripcion ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-right">
                                                    @if($esPdf)
                                                        <a href="{{ route('historia-clinica.examenes.ver', ['id' => $e->id]) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-brand-600 hover:underline dark:text-brand-400 mr-3">Ver</a>
                                                    @endif
                                                    <a href="{{ route('historia-clinica.examenes.download', ['id' => $e->id]) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Descargar</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="py-8 text-center text-gray-500 dark:text-gray-400">No hay exámenes subidos. Use «Subir examen» y, si puede, indique la fecha del examen para organizarlos por día.</p>
                @endif
            </div>
        </div>
    </div>

    <style>
        .input-field {
            height: 2.75rem;
            width: 100%;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            background: #fff;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        .dark .input-field {
            border-color: #4b5563;
            background: #1f2937;
            color: #f9fafb;
        }
    </style>
@endsection
