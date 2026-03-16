<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\HistoriaClinicaFicha;
use App\Models\HistoriaClinicaConsulta;
use App\Models\PacienteExamen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HistoriaClinicaController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')->with('error', 'Inicia sesión para acceder.');
        }

        $buscar = is_string($request->input('buscar')) ? trim($request->input('buscar')) : '';

        try {
            $query = Paciente::where('user_id', Auth::id())->with('historiaClinicaFicha');

            if ($buscar !== '') {
                $term = '%' . mb_strtolower($buscar) . '%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(nombres) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(apellidos) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(dni) LIKE ?', [$term]);
                });
            }

            $pacientes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('dashboard')->with('error', 'Error al cargar historia clínica. Revisa los logs.');
        }

        return Inertia::render('HistoriaClinica/Index', [
            'title' => 'Historia clínica',
            'pacientes' => $pacientes,
            'buscar' => $buscar,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HistoriaClinica/Create', ['title' => 'Nueva historia clínica']);
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

        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', 'Historia clínica registrada correctamente.');
    }

    public function show(Paciente $paciente): Response|RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
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

        return Inertia::render('HistoriaClinica/Show', [
            'title' => 'Historia clínica - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
            'examenesPorFecha' => $examenesPorFecha->values()->all(),
        ]);
    }

    /**
     * Ver ficha del paciente por query string (?id=1). Evita 404 en cPanel con URLs /historia-clinica/1.
     */
    public function showByQuery(Request $request): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')
                ->with('error', 'Inicia sesión para ver la historia clínica.');
        }

        $id = $request->query('id');
        if ($id === null || $id === '' || (int) $id < 1) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'Falta el número de paciente.');
        }

        $paciente = Paciente::find((int) $id);
        if (! $paciente || (int) $paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró esa historia clínica.');
        }

        return $this->show($paciente);
    }

    /**
     * PDF de historia clínica por query string (?id=1). Evita 404 en cPanel.
     */
    public function pdfByQuery(Request $request): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')
                ->with('error', 'Inicia sesión para descargar el PDF.');
        }

        $id = $request->query('id');
        if ($id === null || $id === '' || (int) $id < 1) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'Falta el número de paciente.');
        }

        $paciente = Paciente::find((int) $id);
        if (! $paciente || (int) $paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró esa historia clínica.');
        }

        return $this->pdfHistoriaClinica($paciente);
    }

    public function edit(Paciente $paciente): Response|RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
            abort(404);
        }
        $paciente->load('historiaClinicaFicha');
        return Inertia::render('HistoriaClinica/Edit', [
            'title' => 'Editar historia clínica - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
        ]);
    }

    public function update(Request $request, Paciente $paciente): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
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

        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', 'Historia clínica actualizada correctamente.');
    }

    public function createConsulta(Paciente $paciente): Response|RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
            abort(404);
        }
        return Inertia::render('HistoriaClinica/ConsultaCreate', [
            'title' => 'Nueva consulta - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
        ]);
    }

    public function storeConsulta(Request $request, Paciente $paciente): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
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

        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', 'Consulta registrada correctamente.');
    }

    public function showConsulta(Paciente $paciente, HistoriaClinicaConsulta $consulta): Response|RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id() || (int) $consulta->paciente_id !== (int) $paciente->id) {
            abort(404);
        }
        return Inertia::render('HistoriaClinica/ConsultaShow', [
            'title' => 'Consulta - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
            'consulta' => $consulta,
        ]);
    }

    public function editConsulta(Paciente $paciente, HistoriaClinicaConsulta $consulta): Response|RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id() || (int) $consulta->paciente_id !== (int) $paciente->id) {
            abort(404);
        }
        return Inertia::render('HistoriaClinica/ConsultaEdit', [
            'title' => 'Editar consulta - ' . $paciente->nombre_completo,
            'paciente' => $paciente,
            'consulta' => $consulta,
        ]);
    }

    public function updateConsulta(Request $request, Paciente $paciente, HistoriaClinicaConsulta $consulta): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id() || (int) $consulta->paciente_id !== (int) $paciente->id) {
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

        $consulta->update([
            'fecha_consulta' => $validated['fecha_consulta'],
            'motivo_consulta' => $validated['motivo_consulta'] ?? null,
            'enfermedad_actual' => $validated['enfermedad_actual'] ?? null,
            'dx' => $validated['dx'] ?? null,
            'tx' => $validated['tx'] ?? null,
            'plan_dx' => $validated['plan_dx'] ?? null,
            'recomendaciones' => $validated['recomendaciones'] ?? null,
        ]);

        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', 'Consulta actualizada correctamente.');
    }

    public function destroyConsulta(Paciente $paciente, HistoriaClinicaConsulta $consulta): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id() || (int) $consulta->paciente_id !== (int) $paciente->id) {
            abort(404);
        }
        $consulta->delete();
        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', 'Consulta eliminada correctamente.');
    }

    public function storeExamen(Request $request, Paciente $paciente): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
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

        return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
            ->with('success', $mensaje);
    }

    public function downloadExamen(PacienteExamen $examen): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        if ((int) $examen->paciente->user_id !== (int) Auth::id()) {
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
        if ((int) $examen->paciente->user_id !== (int) Auth::id()) {
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

    public function destroyExamen(PacienteExamen $examen): RedirectResponse
    {
        if ((int) $examen->paciente->user_id !== (int) Auth::id()) {
            abort(404);
        }
        $pacienteId = $examen->paciente_id;
        if (Storage::disk('local')->exists($examen->path)) {
            Storage::disk('local')->delete($examen->path);
        }
        $examen->delete();
        return redirect()->route('historia-clinica.ver', ['id' => $pacienteId])
            ->with('success', 'Examen eliminado correctamente.');
    }

    /**
     * Eliminar historia clínica por POST a /eliminar-historia-clinica con id en el body.
     * Misma lógica que notas de venta: ruta fuera del middleware auth para cPanel.
     */
    public function destroyByQuery(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')
                ->with('error', 'Inicia sesión para continuar.');
        }

        $id = $request->input('id');
        if ($id === null || $id === '' || (int) $id < 1) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'Falta el número de paciente.');
        }

        $paciente = Paciente::find((int) $id);
        if (! $paciente || (int) $paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró esa historia clínica.');
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

    public function destroy(Paciente $paciente): RedirectResponse
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
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

    // ─── Métodos ByQuery (cPanel): misma lógica que notas de venta ────────────────
    // En este servidor cPanel cualquier URL con segmento dinámico devuelve 404.
    // Estos métodos reciben los IDs por query string o body, encuentran el modelo
    // manualmente y delegan en el método original.

    private function resolverPaciente(int $id): Paciente|RedirectResponse
    {
        $paciente = Paciente::find($id);
        if (! $paciente || (int) $paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró esa historia clínica.');
        }
        return $paciente;
    }

    private function authGuard(): ?RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('signin')->with('error', 'Inicia sesión para continuar.');
        }
        return null;
    }

    public function editByQuery(Request $request): Response|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->query('id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        return $this->edit($paciente);
    }

    public function updateByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->input('id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        return $this->update($request, $paciente);
    }

    public function createConsultaByQuery(Request $request): Response|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->query('paciente', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        return $this->createConsulta($paciente);
    }

    public function storeConsultaByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->input('paciente_id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        return $this->storeConsulta($request, $paciente);
    }

    public function showConsultaByQuery(Request $request): Response|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->query('paciente', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        $consultaId = (int) $request->query('consulta', 0);
        $consulta = HistoriaClinicaConsulta::find($consultaId);
        if (! $consulta || (int) $consulta->paciente_id !== (int) $paciente->id) {
            return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
                ->with('error', 'No se encontró esa consulta.');
        }
        return $this->showConsulta($paciente, $consulta);
    }

    public function editConsultaByQuery(Request $request): Response|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->query('paciente', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        $consultaId = (int) $request->query('consulta', 0);
        $consulta = HistoriaClinicaConsulta::find($consultaId);
        if (! $consulta || (int) $consulta->paciente_id !== (int) $paciente->id) {
            return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
                ->with('error', 'No se encontró esa consulta.');
        }
        return $this->editConsulta($paciente, $consulta);
    }

    public function updateConsultaByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->input('paciente_id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        $consultaId = (int) $request->input('consulta_id', 0);
        $consulta = HistoriaClinicaConsulta::find($consultaId);
        if (! $consulta || (int) $consulta->paciente_id !== (int) $paciente->id) {
            return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
                ->with('error', 'No se encontró esa consulta.');
        }
        return $this->updateConsulta($request, $paciente, $consulta);
    }

    public function destroyConsultaByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->input('paciente_id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        $consultaId = (int) $request->input('consulta_id', 0);
        $consulta = HistoriaClinicaConsulta::find($consultaId);
        if (! $consulta || (int) $consulta->paciente_id !== (int) $paciente->id) {
            return redirect()->route('historia-clinica.ver', ['id' => $paciente->id])
                ->with('error', 'No se encontró esa consulta.');
        }
        return $this->destroyConsulta($paciente, $consulta);
    }

    public function storeExamenByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $paciente = $this->resolverPaciente((int) $request->input('paciente_id', 0));
        if ($paciente instanceof RedirectResponse) return $paciente;
        return $this->storeExamen($request, $paciente);
    }

    public function downloadExamenByQuery(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $examen = PacienteExamen::find((int) $request->query('id', 0));
        if (! $examen || (int) $examen->paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró ese examen.');
        }
        return $this->downloadExamen($examen);
    }

    public function verExamenByQuery(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $examen = PacienteExamen::find((int) $request->query('id', 0));
        if (! $examen || (int) $examen->paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró ese examen.');
        }
        return $this->verExamen($examen);
    }

    public function destroyExamenByQuery(Request $request): RedirectResponse
    {
        if ($r = $this->authGuard()) return $r;
        $examen = PacienteExamen::find((int) $request->input('id', 0));
        if (! $examen || (int) $examen->paciente->user_id !== (int) Auth::id()) {
            return redirect()->route('historia-clinica.index')
                ->with('error', 'No se encontró ese examen.');
        }
        return $this->destroyExamen($examen);
    }

    /**
     * Genera PDF de la historia clínica del paciente (datos personales, ficha, consultas). No incluye exámenes.
     */
    public function pdfHistoriaClinica(Paciente $paciente): \Symfony\Component\HttpFoundation\Response
    {
        if ((int) $paciente->user_id !== (int) Auth::id()) {
            abort(404);
        }
        $paciente->load(['historiaClinicaFicha', 'historiaClinicaConsultas']);
        $logoBase64 = $this->getLogoBase64();
        $pdf = Pdf::loadView('pdf.historia-clinica', [
            'paciente' => $paciente,
            'logoBase64' => $logoBase64,
        ]);
        $pdf->setPaper('a4');
        $filename = 'historia_clinica_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', $paciente->nombre_completo) . '_' . now()->format('Y-m-d') . '.pdf';
        $response = $pdf->download($filename);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    private function getLogoBase64(): string
    {
        $paths = [
            ['path' => public_path('logo_cpromed.jpg'), 'mime' => 'image/jpeg'],
            ['path' => public_path('images/logo/logo.png'), 'mime' => 'image/png'],
        ];
        foreach ($paths as $item) {
            try {
                if (file_exists($item['path']) && is_readable($item['path'])) {
                    $data = @file_get_contents($item['path']);
                    if ($data !== false) {
                        return 'data:' . $item['mime'] . ';base64,' . base64_encode($data);
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }
        return '';
    }
}
