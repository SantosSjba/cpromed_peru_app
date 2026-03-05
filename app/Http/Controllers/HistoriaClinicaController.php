<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\HistoriaClinicaFicha;
use App\Models\HistoriaClinicaConsulta;
use App\Models\PacienteExamen;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HistoriaClinicaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Paciente::where('user_id', Auth::id())->with('historiaClinicaFicha');

        if ($request->filled('buscar')) {
            $term = $request->input('buscar');
            $query->where(function ($q) use ($term) {
                $q->where('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")
                    ->orWhere('dni', 'like', "%{$term}%");
            });
        }

        $pacientes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pages.historia-clinica.index', [
            'title' => 'Historia clínica',
            'pacientes' => $pacientes,
            'buscar' => $request->input('buscar', ''),
        ]);
    }

    public function create(): View
    {
        return view('pages.historia-clinica.create', ['title' => 'Nueva historia clínica']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'string', 'in:M,F,Otro'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'celular' => ['nullable', 'string', 'max:32'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'grupo_sanguineo' => ['nullable', 'string', 'max:16'],
            // Ficha
            'antecedentes_medicos' => ['nullable', 'string'],
            'antecedentes_personales' => ['nullable', 'string'],
            'antecedentes_familiares' => ['nullable', 'string'],
            'enfermedades_previas' => ['nullable', 'string'],
            'cirugias_si_no' => ['required', 'boolean'],
            'cirugias_detalle' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'medicamentos_actuales' => ['nullable', 'string'],
            // Primera consulta
            'fecha_consulta' => ['required', 'date'],
            'motivo_consulta' => ['nullable', 'string'],
            'enfermedad_actual' => ['nullable', 'string'],
            'dx' => ['nullable', 'string'],
            'tx' => ['nullable', 'string'],
            'plan_dx' => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
        ], [], [
            'nombres' => 'nombres del paciente',
            'apellidos' => 'apellidos del paciente',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'genero' => 'género',
        ]);

        $paciente = Paciente::create([
            'user_id' => Auth::id(),
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'genero' => $validated['genero'],
            'direccion' => $validated['direccion'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'ocupacion' => $validated['ocupacion'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'email' => $validated['email'] ?? null,
            'grupo_sanguineo' => $validated['grupo_sanguineo'] ?? null,
        ]);

        HistoriaClinicaFicha::create([
            'paciente_id' => $paciente->id,
            'antecedentes_medicos' => $validated['antecedentes_medicos'] ?? null,
            'antecedentes_personales' => $validated['antecedentes_personales'] ?? null,
            'antecedentes_familiares' => $validated['antecedentes_familiares'] ?? null,
            'enfermedades_previas' => $validated['enfermedades_previas'] ?? null,
            'cirugias_si_no' => (bool) $validated['cirugias_si_no'],
            'cirugias_detalle' => $validated['cirugias_detalle'] ?? null,
            'alergias' => $validated['alergias'] ?? null,
            'medicamentos_actuales' => $validated['medicamentos_actuales'] ?? null,
        ]);

        HistoriaClinicaConsulta::create([
            'paciente_id' => $paciente->id,
            'fecha_consulta' => $validated['fecha_consulta'],
            'motivo_consulta' => $validated['motivo_consulta'] ?? null,
            'enfermedad_actual' => $validated['enfermedad_actual'] ?? null,
            'dx' => $validated['dx'] ?? null,
            'tx' => $validated['tx'] ?? null,
            'plan_dx' => $validated['plan_dx'] ?? null,
            'recomendaciones' => $validated['recomendaciones'] ?? null,
        ]);

        return redirect()->route('historia-clinica.show', $paciente)
            ->with('success', 'Historia clínica registrada correctamente.');
    }

    public function show(Paciente $paciente): View|RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $paciente->load(['historiaClinicaFicha', 'historiaClinicaConsultas' => fn ($q) => $q->orderByDesc('fecha_consulta')]);
        $paciente->load(['pacienteExamenes' => fn ($q) => $q->orderByRaw('COALESCE(fecha_examen, created_at) DESC')]);

        $examenesPorFecha = $paciente->pacienteExamenes
            ->groupBy(function ($e) {
                return $e->fecha_examen
                    ? $e->fecha_examen->format('Y-m-d')
                    : $e->created_at->format('Y-m-d');
            })
            ->map(function ($examenes, $fecha) {
                $primero = $examenes->first();
                $fechaObj = $primero->fecha_examen ?? $primero->created_at;
                return [
                    'fecha' => $fechaObj,
                    'items' => $examenes,
                ];
            })
            ->sortByDesc(fn ($g) => $g['fecha']);

        return view('pages.historia-clinica.show', [
            'title' => 'Historia clínica - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
            'examenesPorFecha' => $examenesPorFecha,
        ]);
    }

    public function edit(Paciente $paciente): View|RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $paciente->load('historiaClinicaFicha');
        return view('pages.historia-clinica.edit', [
            'title' => 'Editar historia clínica - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
        ]);
    }

    public function update(Request $request, Paciente $paciente): RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'string', 'in:M,F,Otro'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'celular' => ['nullable', 'string', 'max:32'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'grupo_sanguineo' => ['nullable', 'string', 'max:16'],
            'antecedentes_medicos' => ['nullable', 'string'],
            'antecedentes_personales' => ['nullable', 'string'],
            'antecedentes_familiares' => ['nullable', 'string'],
            'enfermedades_previas' => ['nullable', 'string'],
            'cirugias_si_no' => ['required', 'boolean'],
            'cirugias_detalle' => ['nullable', 'string'],
            'alergias' => ['nullable', 'string'],
            'medicamentos_actuales' => ['nullable', 'string'],
        ], [], [
            'nombres' => 'nombres del paciente',
            'apellidos' => 'apellidos del paciente',
        ]);

        $paciente->update([
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'genero' => $validated['genero'],
            'direccion' => $validated['direccion'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'ocupacion' => $validated['ocupacion'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'email' => $validated['email'] ?? null,
            'grupo_sanguineo' => $validated['grupo_sanguineo'] ?? null,
        ]);

        $ficha = $paciente->historiaClinicaFicha;
        if ($ficha) {
            $ficha->update([
                'antecedentes_medicos' => $validated['antecedentes_medicos'] ?? null,
                'antecedentes_personales' => $validated['antecedentes_personales'] ?? null,
                'antecedentes_familiares' => $validated['antecedentes_familiares'] ?? null,
                'enfermedades_previas' => $validated['enfermedades_previas'] ?? null,
                'cirugias_si_no' => (bool) $validated['cirugias_si_no'],
                'cirugias_detalle' => $validated['cirugias_detalle'] ?? null,
                'alergias' => $validated['alergias'] ?? null,
                'medicamentos_actuales' => $validated['medicamentos_actuales'] ?? null,
            ]);
        } else {
            HistoriaClinicaFicha::create([
                'paciente_id' => $paciente->id,
                'antecedentes_medicos' => $validated['antecedentes_medicos'] ?? null,
                'antecedentes_personales' => $validated['antecedentes_personales'] ?? null,
                'antecedentes_familiares' => $validated['antecedentes_familiares'] ?? null,
                'enfermedades_previas' => $validated['enfermedades_previas'] ?? null,
                'cirugias_si_no' => (bool) $validated['cirugias_si_no'],
                'cirugias_detalle' => $validated['cirugias_detalle'] ?? null,
                'alergias' => $validated['alergias'] ?? null,
                'medicamentos_actuales' => $validated['medicamentos_actuales'] ?? null,
            ]);
        }

        return redirect()->route('historia-clinica.show', $paciente)
            ->with('success', 'Historia clínica actualizada correctamente.');
    }

    public function createConsulta(Paciente $paciente): View|RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        return view('pages.historia-clinica.consulta-create', [
            'title' => 'Nueva consulta - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
        ]);
    }

    public function storeConsulta(Request $request, Paciente $paciente): RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $validated = $request->validate([
            'fecha_consulta' => ['required', 'date'],
            'motivo_consulta' => ['nullable', 'string'],
            'enfermedad_actual' => ['nullable', 'string'],
            'dx' => ['nullable', 'string'],
            'tx' => ['nullable', 'string'],
            'plan_dx' => ['nullable', 'string'],
            'recomendaciones' => ['nullable', 'string'],
        ], [], [
            'fecha_consulta' => 'fecha de consulta',
        ]);

        HistoriaClinicaConsulta::create([
            'paciente_id' => $paciente->id,
            'fecha_consulta' => $validated['fecha_consulta'],
            'motivo_consulta' => $validated['motivo_consulta'] ?? null,
            'enfermedad_actual' => $validated['enfermedad_actual'] ?? null,
            'dx' => $validated['dx'] ?? null,
            'tx' => $validated['tx'] ?? null,
            'plan_dx' => $validated['plan_dx'] ?? null,
            'recomendaciones' => $validated['recomendaciones'] ?? null,
        ]);

        return redirect()->route('historia-clinica.show', $paciente)
            ->with('success', 'Consulta registrada correctamente.');
    }

    public function showConsulta(Paciente $paciente, HistoriaClinicaConsulta $consulta): View|RedirectResponse
    {
        if ($paciente->user_id !== Auth::id() || $consulta->paciente_id !== $paciente->id) {
            abort(404);
        }
        return view('pages.historia-clinica.consulta-show', [
            'title' => 'Consulta - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
            'consulta' => $consulta,
        ]);
    }

    public function storeExamen(Request $request, Paciente $paciente): RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $request->validate([
            'archivo' => ['required', 'array', 'min:1'],
            'archivo.*' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,gif,webp'],
            'tipo' => ['nullable', 'array'],
            'tipo.*' => ['nullable', 'string', 'max:64'],
            'descripcion' => ['nullable', 'array'],
            'descripcion.*' => ['nullable', 'string', 'max:500'],
            'fecha_examen' => ['nullable', 'date'],
        ], [], [
            'archivo' => 'archivos',
            'archivo.*' => 'archivo',
        ]);

        $archivos = $request->file('archivo', []);
        $tipos = $request->input('tipo', []);
        $descripciones = $request->input('descripcion', []);
        $fechaExamen = $request->input('fecha_examen');
        $dir = 'paciente_examenes/' . $paciente->id;
        $subidos = 0;

        foreach ($archivos as $i => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->store($dir, 'local');
            PacienteExamen::create([
                'paciente_id' => $paciente->id,
                'user_id' => Auth::id(),
                'path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'tipo' => $tipos[$i] ?? null,
                'fecha_examen' => $fechaExamen,
                'descripcion' => $descripciones[$i] ?? null,
            ]);
            $subidos++;
        }

        if ($subidos === 0) {
            return redirect()->back()
                ->with('error', 'Debe subir al menos un archivo válido (PDF o imagen).')
                ->withInput();
        }

        $mensaje = $subidos === 1
            ? 'Examen subido correctamente.'
            : "{$subidos} exámenes subidos correctamente.";

        return redirect()->route('historia-clinica.show', $paciente)
            ->with('success', $mensaje);
    }

    public function downloadExamen(PacienteExamen $examen): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        if ($examen->paciente->user_id !== Auth::id()) {
            abort(404);
        }
        if (! Storage::disk('local')->exists($examen->path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }
        return Storage::disk('local')->download(
            $examen->path,
            $examen->file_name,
            ['Content-Type' => Storage::disk('local')->mimeType($examen->path)]
        );
    }

    public function verExamen(PacienteExamen $examen): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        if ($examen->paciente->user_id !== Auth::id()) {
            abort(404);
        }
        if (! Storage::disk('local')->exists($examen->path)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }
        $mime = Storage::disk('local')->mimeType($examen->path);
        return Storage::disk('local')->response(
            $examen->path,
            $examen->file_name,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline',
            ]
        );
    }

    public function destroy(Paciente $paciente): RedirectResponse
    {
        if ($paciente->user_id !== Auth::id()) {
            abort(404);
        }
        $paciente->load('pacienteExamenes');
        foreach ($paciente->pacienteExamenes as $examen) {
            if (Storage::disk('local')->exists($examen->path)) {
                Storage::disk('local')->delete($examen->path);
            }
        }
        $paciente->delete();
        return redirect()->route('historia-clinica.index')
            ->with('success', 'Historia clínica eliminada correctamente.');
    }
}
